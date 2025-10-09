<?php
declare(strict_types=1);

namespace App\Service\Quiz;

use Cake\ORM\TableRegistry;
use Cake\Log\LogTrait;
use Cake\Utility\Text;
use Exception;

/**
 * Question Strategy Service
 * 
 * Generates intelligent batches of 4 questions that adapt based on user responses
 * to efficiently narrow down product recommendations
 */
class QuestionStrategyService
{
    use LogTrait;
    
    private const BATCH_SIZE = 4;
    private const MAX_BATCHES = 3; // Max 12 questions total
    
    private $productsTable;
    
    /**
     * Question priorities by stage
     */
    private const QUESTION_STAGES = [
        'initial' => [
            'device_category' => 0.9,
            'manufacturer_preference' => 0.8,
            'port_type' => 0.7,
            'usage_scenario' => 0.6,
        ],
        'refinement' => [
            'power_requirements' => 0.9,
            'budget_range' => 0.8,
            'certification_needs' => 0.7,
            'form_factor' => 0.6,
        ],
        'finalization' => [
            'specific_features' => 0.9,
            'compatibility_details' => 0.8,
            'quality_priorities' => 0.7,
            'purchase_timeline' => 0.6,
        ]
    ];
    
    /**
     * Pre-defined question templates
     */
    private const QUESTION_TEMPLATES = [
        'device_category' => [
            'id' => 'device_category',
            'text' => 'What type of device do you need to power or charge?',
            'type' => 'choice',
            'category' => 'device_classification',
            'options' => [
                ['id' => 'laptop', 'label' => 'Laptop/MacBook', 'follow_up' => 'laptop_specifics'],
                ['id' => 'phone', 'label' => 'Smartphone', 'follow_up' => 'phone_specifics'],
                ['id' => 'tablet', 'label' => 'Tablet/iPad', 'follow_up' => 'tablet_specifics'],
                ['id' => 'gaming', 'label' => 'Gaming Device', 'follow_up' => 'gaming_specifics'],
                ['id' => 'multiple', 'label' => 'Multiple devices', 'follow_up' => 'multiport_needs'],
            ]
        ],
        'manufacturer_preference' => [
            'id' => 'manufacturer_preference',
            'text' => 'Do you have a preferred brand or manufacturer?',
            'type' => 'choice',
            'category' => 'brand_compatibility',
            'options' => [
                ['id' => 'apple', 'label' => 'Apple (official or compatible)', 'filter' => ['manufacturer' => ['Apple', 'Universal']]],
                ['id' => 'samsung', 'label' => 'Samsung', 'filter' => ['manufacturer' => ['Samsung', 'Universal']]],
                ['id' => 'dell_hp_lenovo', 'label' => 'Dell, HP, or Lenovo', 'filter' => ['manufacturer' => ['Dell', 'HP', 'Lenovo', 'Universal']]],
                ['id' => 'premium_third_party', 'label' => 'Premium third-party brands', 'filter' => ['manufacturer' => ['Anker', 'Belkin', 'CalDigit', 'Universal']]],
                ['id' => 'no_preference', 'label' => 'No preference', 'filter' => []],
            ]
        ],
        'port_type' => [
            'id' => 'port_type',
            'text' => 'What type of charging port does your device have?',
            'type' => 'choice',
            'category' => 'connectivity',
            'options' => [
                ['id' => 'usbc', 'label' => 'USB-C', 'filter' => ['port_type_name' => 'USB-C']],
                ['id' => 'lightning', 'label' => 'Lightning (iPhone/iPad)', 'filter' => ['port_type_name' => 'Lightning']],
                ['id' => 'magsafe', 'label' => 'MagSafe (MacBook)', 'filter' => ['port_type_name' => ['MagSafe 3', 'MagSafe 2']]],
                ['id' => 'surface_connect', 'label' => 'Surface Connect', 'filter' => ['port_type_name' => 'Surface Connect']],
                ['id' => 'not_sure', 'label' => 'Not sure', 'filter' => []],
            ]
        ],
        'power_requirements' => [
            'id' => 'power_requirements',
            'text' => 'What are your power requirements?',
            'type' => 'choice',
            'category' => 'technical_specs',
            'options' => [
                ['id' => 'low_power', 'label' => 'Low power (5W-25W) - Phones, earbuds', 'filter' => ['max_wattage' => ['min' => 5, 'max' => 25]]],
                ['id' => 'medium_power', 'label' => 'Medium power (30W-65W) - Tablets, ultrabooks', 'filter' => ['max_wattage' => ['min' => 30, 'max' => 65]]],
                ['id' => 'high_power', 'label' => 'High power (70W+) - Gaming laptops, workstations', 'filter' => ['max_wattage' => ['min' => 70, 'max' => 200]]],
                ['id' => 'unknown', 'label' => 'I don\'t know', 'filter' => []],
            ]
        ],
        'budget_range' => [
            'id' => 'budget_range',
            'text' => 'What\'s your budget range?',
            'type' => 'choice',
            'category' => 'financial_constraints',
            'options' => [
                ['id' => 'budget', 'label' => 'Budget-friendly ($10-$30)', 'filter' => ['price' => ['min' => 10, 'max' => 30]]],
                ['id' => 'mid_range', 'label' => 'Mid-range ($30-$70)', 'filter' => ['price' => ['min' => 30, 'max' => 70]]],
                ['id' => 'premium', 'label' => 'Premium ($70-$150)', 'filter' => ['price' => ['min' => 70, 'max' => 150]]],
                ['id' => 'high_end', 'label' => 'High-end ($150+)', 'filter' => ['price' => ['min' => 150, 'max' => 500]]],
            ]
        ],
        'usage_scenario' => [
            'id' => 'usage_scenario',
            'text' => 'How do you primarily plan to use this charger/adapter?',
            'type' => 'choice',
            'category' => 'use_case',
            'options' => [
                ['id' => 'home_office', 'label' => 'Home/office (stationary)', 'tags' => ['desktop', 'reliable', 'high_power']],
                ['id' => 'travel', 'label' => 'Travel/portable', 'tags' => ['portable', 'compact', 'lightweight']],
                ['id' => 'car', 'label' => 'In the car', 'tags' => ['car_adapter', 'mobile']],
                ['id' => 'backup', 'label' => 'Backup/replacement', 'tags' => ['affordable', 'reliable']],
            ]
        ],
        'certification_needs' => [
            'id' => 'certification_needs',
            'text' => 'How important is official certification to you?',
            'type' => 'choice',
            'category' => 'quality_assurance',
            'options' => [
                ['id' => 'required', 'label' => 'Must be officially certified', 'filter' => ['is_certified' => true]],
                ['id' => 'preferred', 'label' => 'Prefer certified but not required', 'filter' => []],
                ['id' => 'not_important', 'label' => 'Not important to me', 'filter' => []],
            ]
        ],
        'specific_features' => [
            'id' => 'specific_features',
            'text' => 'Which additional features are most important?',
            'type' => 'multiple_choice',
            'category' => 'feature_preferences',
            'options' => [
                ['id' => 'fast_charging', 'label' => 'Fast/rapid charging', 'tags' => ['fast_charging', 'gan_technology']],
                ['id' => 'multiple_ports', 'label' => 'Multiple charging ports', 'tags' => ['multiport', 'hub']],
                ['id' => 'compact_size', 'label' => 'Compact/portable design', 'tags' => ['compact', 'travel']],
                ['id' => 'long_cable', 'label' => 'Extra-long cable', 'tags' => ['extended_cable']],
                ['id' => 'wireless', 'label' => 'Wireless charging capability', 'tags' => ['wireless', 'qi_compatible']],
            ]
        ]
    ];
    
    public function __construct()
    {
        $this->productsTable = TableRegistry::getTableLocator()->get('Products');
    }
    
    /**
     * Generate a batch of 4 strategic questions based on current user profile
     *
     * @param array $userProfile Current answers and derived insights
     * @param int $batchNumber Current batch number (0-based)
     * @return array Batch of 4 questions
     */
    public function generateQuestionBatch(array $userProfile, int $batchNumber = 0): array
    {
        $stage = $this->determineStage($batchNumber, $userProfile);
        $availableProducts = $this->getAvailableProducts($userProfile);
        
        // Determine question priorities based on current product set diversity
        $questionPriorities = $this->calculateQuestionPriorities($availableProducts, $userProfile, $stage);
        
        // Select top 4 most valuable questions
        $selectedQuestions = $this->selectOptimalQuestions($questionPriorities, $userProfile);
        
        // Customize questions based on user context
        $customizedQuestions = $this->customizeQuestions($selectedQuestions, $userProfile);
        
        $this->log(sprintf(
            'Generated batch %d with %d questions for stage "%s". Available products: %d',
            $batchNumber,
            count($customizedQuestions),
            $stage,
            count($availableProducts)
        ), 'info');
        
        return [
            'questions' => $customizedQuestions,
            'batch_number' => $batchNumber,
            'stage' => $stage,
            'available_products_count' => count($availableProducts),
            'progress' => [
                'current_batch' => $batchNumber + 1,
                'total_batches' => self::MAX_BATCHES,
                'questions_answered' => count($userProfile['answers'] ?? []),
                'max_questions' => self::MAX_BATCHES * self::BATCH_SIZE,
            ]
        ];
    }
    
    /**
     * Determine current questioning stage based on progress and answers
     */
    private function determineStage(int $batchNumber, array $userProfile): string
    {
        if ($batchNumber === 0) {
            return 'initial';
        } elseif ($batchNumber === 1) {
            return 'refinement';
        } else {
            return 'finalization';
        }
    }
    
    /**
     * Get currently available products based on user answers
     */
    private function getAvailableProducts(array $userProfile): array
    {
        $query = $this->productsTable->find()
            ->where(['is_published' => true]);
            
        // Apply filters based on user answers
        foreach ($userProfile['answers'] ?? [] as $questionId => $answer) {
            $question = self::QUESTION_TEMPLATES[$questionId] ?? null;
            if (!$question) continue;
            
            $selectedOption = null;
            foreach ($question['options'] as $option) {
                if ($option['id'] === $answer) {
                    $selectedOption = $option;
                    break;
                }
            }
            
            if ($selectedOption && isset($selectedOption['filter'])) {
                $query = $this->applyFilters($query, $selectedOption['filter']);
            }
        }
        
        return $query->limit(100)->toArray();
    }
    
    /**
     * Apply filters to product query
     */
    private function applyFilters($query, array $filters)
    {
        foreach ($filters as $field => $criteria) {
            if (is_array($criteria)) {
                if (isset($criteria['min']) || isset($criteria['max'])) {
                    // Range filter
                    if (isset($criteria['min'])) {
                        $query = $query->where([$field . ' >=' => $criteria['min']]);
                    }
                    if (isset($criteria['max'])) {
                        $query = $query->where([$field . ' <=' => $criteria['max']]);
                    }
                } else {
                    // Array of values
                    $query = $query->where([$field . ' IN' => $criteria]);
                }
            } else {
                // Single value
                $query = $query->where([$field => $criteria]);
            }
        }
        
        return $query;
    }
    
    /**
     * Calculate question priorities based on current product diversity
     */
    private function calculateQuestionPriorities(array $products, array $userProfile, string $stage): array
    {
        $stageQuestions = self::QUESTION_STAGES[$stage] ?? [];
        $priorities = [];
        
        foreach ($stageQuestions as $questionId => $baseWeight) {
            // Skip if already answered
            if (isset($userProfile['answers'][$questionId])) {
                continue;
            }
            
            // Calculate information gain potential
            $informationGain = $this->calculateInformationGain($questionId, $products);
            
            // Adjust weight based on current context
            $contextWeight = $this->calculateContextualWeight($questionId, $userProfile);
            
            $finalPriority = $baseWeight * $informationGain * $contextWeight;
            $priorities[$questionId] = $finalPriority;
        }
        
        // Sort by priority
        arsort($priorities);
        
        return $priorities;
    }
    
    /**
     * Calculate information gain for a question based on current product set
     */
    private function calculateInformationGain(string $questionId, array $products): float
    {
        if (empty($products)) {
            return 0.0;
        }
        
        $question = self::QUESTION_TEMPLATES[$questionId] ?? null;
        if (!$question) {
            return 0.0;
        }
        
        // Calculate how well this question would split the product set
        $splits = [];
        foreach ($question['options'] as $option) {
            if (!isset($option['filter'])) {
                continue;
            }
            
            $matchingProducts = 0;
            foreach ($products as $product) {
                if ($this->productMatchesFilter($product, $option['filter'])) {
                    $matchingProducts++;
                }
            }
            
            $splits[] = $matchingProducts;
        }
        
        // Calculate entropy reduction (information gain)
        $totalProducts = count($products);
        if ($totalProducts <= 1) {
            return 0.0;
        }
        
        $entropy = 0.0;
        foreach ($splits as $split) {
            if ($split > 0) {
                $proportion = $split / $totalProducts;
                $entropy -= $proportion * log($proportion, 2);
            }
        }
        
        // Normalize entropy (0-1 scale)
        $maxEntropy = log(count($question['options']), 2);
        return $maxEntropy > 0 ? $entropy / $maxEntropy : 0.0;
    }
    
    /**
     * Check if product matches filter criteria
     */
    private function productMatchesFilter($product, array $filter): bool
    {
        foreach ($filter as $field => $criteria) {
            $productValue = $product->$field ?? null;
            
            if (is_array($criteria)) {
                if (isset($criteria['min']) || isset($criteria['max'])) {
                    // Range check
                    if (isset($criteria['min']) && $productValue < $criteria['min']) {
                        return false;
                    }
                    if (isset($criteria['max']) && $productValue > $criteria['max']) {
                        return false;
                    }
                } else {
                    // Array membership
                    if (!in_array($productValue, $criteria)) {
                        return false;
                    }
                }
            } else {
                // Exact match
                if ($productValue !== $criteria) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Calculate contextual weight based on user profile
     */
    private function calculateContextualWeight(string $questionId, array $userProfile): float
    {
        $baseWeight = 1.0;
        
        // Boost weight for follow-up questions
        if (isset($userProfile['follow_ups']) && in_array($questionId, $userProfile['follow_ups'])) {
            $baseWeight *= 1.5;
        }
        
        // Adjust based on user preferences
        if (isset($userProfile['preferences'])) {
            // Add contextual logic here
            // e.g., if user prefers premium, boost certification questions
            if (isset($userProfile['preferences']['quality_focused']) && 
                in_array($questionId, ['certification_needs', 'specific_features'])) {
                $baseWeight *= 1.3;
            }
        }
        
        return $baseWeight;
    }
    
    /**
     * Select optimal 4 questions from prioritized list
     */
    private function selectOptimalQuestions(array $priorities, array $userProfile): array
    {
        $selected = [];
        $count = 0;
        
        foreach ($priorities as $questionId => $priority) {
            if ($count >= self::BATCH_SIZE) {
                break;
            }
            
            $selected[] = $questionId;
            $count++;
        }
        
        // If we don't have enough questions, fill with fallbacks
        while (count($selected) < self::BATCH_SIZE) {
            $fallback = $this->getFallbackQuestion($userProfile, $selected);
            if ($fallback) {
                $selected[] = $fallback;
            } else {
                break;
            }
        }
        
        return $selected;
    }
    
    /**
     * Get fallback question when primary selection is insufficient
     */
    private function getFallbackQuestion(array $userProfile, array $alreadySelected): ?string
    {
        $allQuestions = array_keys(self::QUESTION_TEMPLATES);
        $answered = array_keys($userProfile['answers'] ?? []);
        
        foreach ($allQuestions as $questionId) {
            if (!in_array($questionId, $answered) && !in_array($questionId, $alreadySelected)) {
                return $questionId;
            }
        }
        
        return null;
    }
    
    /**
     * Customize questions based on user context
     */
    private function customizeQuestions(array $questionIds, array $userProfile): array
    {
        $customized = [];
        
        foreach ($questionIds as $questionId) {
            $template = self::QUESTION_TEMPLATES[$questionId] ?? null;
            if (!$template) {
                continue;
            }
            
            $question = $template;
            $question['id'] = Text::uuid(); // Unique ID for this instance
            $question['template_id'] = $questionId;
            
            // Customize question text based on context
            $question['text'] = $this->customizeQuestionText($question['text'], $userProfile);
            
            // Filter options based on context
            $question['options'] = $this->customizeOptions($question['options'], $userProfile);
            
            $customized[] = $question;
        }
        
        return $customized;
    }
    
    /**
     * Customize question text based on user context
     */
    private function customizeQuestionText(string $text, array $userProfile): string
    {
        // Add contextual modifications to question text
        // For example, if user already selected laptop, modify device-related questions
        
        return $text;
    }
    
    /**
     * Customize options based on user context
     */
    private function customizeOptions(array $options, array $userProfile): array
    {
        // Filter or modify options based on user context
        // For example, if user already selected Apple, prioritize Apple-compatible options
        
        return $options;
    }
    
    /**
     * Determine if quiz should terminate based on user profile
     */
    public function shouldTerminate(array $userProfile, int $batchNumber): bool
    {
        // Check if we've reached max batches
        if ($batchNumber >= self::MAX_BATCHES) {
            return true;
        }
        
        // Check if we have high confidence
        $confidence = $this->calculateConfidence($userProfile);
        if ($confidence >= 0.85) {
            return true;
        }
        
        // Check if product set is sufficiently narrow
        $availableProducts = $this->getAvailableProducts($userProfile);
        if (count($availableProducts) <= 3) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Calculate current confidence based on answers
     */
    public function calculateConfidence(array $userProfile): float
    {
        $answers = $userProfile['answers'] ?? [];
        $answerCount = count($answers);
        
        if ($answerCount === 0) {
            return 0.0;
        }
        
        // Base confidence increases with each answer
        $baseConfidence = min(0.3 + ($answerCount * 0.05), 0.7);
        
        // Bonus for specific high-value answers
        $bonusConfidence = 0.0;
        
        if (isset($answers['device_category'])) {
            $bonusConfidence += 0.1;
        }
        
        if (isset($answers['port_type'])) {
            $bonusConfidence += 0.1;
        }
        
        if (isset($answers['manufacturer_preference']) && $answers['manufacturer_preference'] !== 'no_preference') {
            $bonusConfidence += 0.05;
        }
        
        return min($baseConfidence + $bonusConfidence, 0.95);
    }
    
    /**
     * Update user profile with new answer and derive insights
     */
    public function updateUserProfile(array $userProfile, string $questionTemplateId, string $answer): array
    {
        // Add the answer
        $userProfile['answers'] = $userProfile['answers'] ?? [];
        $userProfile['answers'][$questionTemplateId] = $answer;
        
        // Update follow-ups based on the answer
        $question = self::QUESTION_TEMPLATES[$questionTemplateId] ?? null;
        if ($question) {
            foreach ($question['options'] as $option) {
                if ($option['id'] === $answer && isset($option['follow_up'])) {
                    $userProfile['follow_ups'] = $userProfile['follow_ups'] ?? [];
                    $userProfile['follow_ups'][] = $option['follow_up'];
                }
            }
        }
        
        // Update derived insights
        $userProfile = $this->deriveInsights($userProfile);
        
        return $userProfile;
    }
    
    /**
     * Derive insights from current answers
     */
    private function deriveInsights(array $userProfile): array
    {
        $answers = $userProfile['answers'] ?? [];
        $preferences = [];
        
        // Derive quality focus
        if (isset($answers['certification_needs']) && $answers['certification_needs'] === 'required') {
            $preferences['quality_focused'] = true;
        }
        
        // Derive budget category
        if (isset($answers['budget_range'])) {
            $preferences['budget_category'] = $answers['budget_range'];
        }
        
        // Derive use case
        if (isset($answers['usage_scenario'])) {
            $preferences['use_case'] = $answers['usage_scenario'];
        }
        
        $userProfile['preferences'] = $preferences;
        
        return $userProfile;
    }
}