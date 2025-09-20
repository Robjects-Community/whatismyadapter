<?php
declare(strict_types=1);

namespace App\Service\Ai;

use Cake\Log\Log;
use Exception;

/**
 * AI Provider for Product Form Field Suggestions
 *
 * Provides intelligent suggestions for product form fields based on
 * existing data and context. Uses pattern matching and heuristic rules
 * to generate helpful auto-fill suggestions.
 */
class ProductFormAiProvider implements AiProviderInterface
{
    private array $manufacturerPatterns = [
        'anker' => ['anker', 'powerline', 'powercore', 'eufy'],
        'belkin' => ['belkin', 'boost', 'charge'],
        'apple' => ['apple', 'lightning', 'magsafe', 'airpods', 'iphone', 'ipad', 'macbook'],
        'samsung' => ['samsung', 'galaxy', 'dex'],
        'dell' => ['dell', 'alienware', 'latitude', 'precision'],
        'hp' => ['hp', 'elitebook', 'pavilion', 'omen'],
        'lenovo' => ['lenovo', 'thinkpad', 'yoga', 'legion'],
        'logitech' => ['logitech', 'mx', 'k380', 'g pro'],
        'ugreen' => ['ugreen', 'nexode'],
        'cable matters' => ['cable matters', 'cm'],
    ];

    private array $productTypePatterns = [
        'usb-c' => ['usb-c', 'usbc', 'type-c', 'thunderbolt', 'pd'],
        'hdmi' => ['hdmi', '4k', 'display', 'monitor', 'tv'],
        'ethernet' => ['ethernet', 'network', 'rj45', 'lan'],
        'audio' => ['audio', '3.5mm', 'headphone', 'microphone', 'aux'],
        'charging' => ['charging', 'charger', 'power', 'adapter', 'wall'],
        'hub' => ['hub', 'dock', 'station', 'multiport'],
        'cable' => ['cable', 'cord', 'wire'],
    ];

    private array $technicalSpecs = [
        'usb-c' => [
            'data_transfer' => ['USB 3.0 (5Gbps)', 'USB 3.1 (10Gbps)', 'USB 3.2 (20Gbps)', 'Thunderbolt 3 (40Gbps)'],
            'power_delivery' => ['60W', '87W', '100W', '140W'],
            'video_output' => ['4K@60Hz', '4K@30Hz', '1080p@60Hz'],
        ],
        'hdmi' => [
            'version' => ['HDMI 2.1', 'HDMI 2.0', 'HDMI 1.4'],
            'resolution' => ['8K@60Hz', '4K@120Hz', '4K@60Hz', '1080p@60Hz'],
            'features' => ['HDR', 'eARC', 'CEC', 'HDCP 2.2'],
        ],
        'ethernet' => [
            'speed' => ['Gigabit (1000Mbps)', 'Fast Ethernet (100Mbps)', '2.5 Gigabit'],
            'category' => ['Cat 6', 'Cat 6a', 'Cat 5e'],
        ],
    ];

    /**
     * Get AI-powered suggestions for improving product reliability
     *
     * @param array $productData Current product data payload
     * @param array $context Additional context (field_weights, current_scores, etc.)
     * @return array Array with keys: suggestions[], reasoning, confidence_level
     */
    public function getSuggestions(array $productData, array $context = []): array
    {
        $fieldName = $productData['field_name'] ?? '';
        $existingData = $productData['existing_data'] ?? [];

        try {
            switch ($fieldName) {
                case 'title':
                    return $this->suggestTitle($existingData, $context);
                case 'manufacturer':
                    return $this->suggestManufacturer($existingData, $context);
                case 'description':
                    return $this->suggestDescription($existingData, $context);
                case 'technical_specifications':
                    return $this->suggestTechnicalSpecs($existingData, $context);
                case 'model_number':
                    return $this->suggestModelNumber($existingData, $context);
                case 'price':
                    return $this->suggestPrice($existingData, $context);
                case 'alt_text':
                    return $this->suggestAltText($existingData, $context);
                case 'testing_standard':
                    return $this->suggestTestingStandard($existingData, $context);
                case 'certifying_organization':
                    return $this->suggestCertifyingOrganization($existingData, $context);
                default:
                    return $this->getDefaultSuggestions($productData, $context);
            }
        } catch (Exception $e) {
            Log::error('ProductFormAiProvider error: ' . $e->getMessage());

            return [
                'suggestions' => [],
                'reasoning' => 'AI processing error',
                'confidence_level' => 0,
            ];
        }
    }

    /**
     * Suggest product title based on manufacturer and description
     *
     * @param array $data Product data containing manufacturer, description, model_number
     * @param array $context Additional context for suggestion
     * @return array Suggestion results with confidence level
     */
    private function suggestTitle(array $data, array $context): array
    {
        $manufacturer = $data['manufacturer'] ?? '';
        $description = $data['description'] ?? '';
        $modelNumber = $data['model_number'] ?? '';

        $suggestions = [];
        $confidence = 0;

        if ($manufacturer && $description) {
            // Extract product type from description
            $productType = $this->extractProductType($description);
            if ($productType) {
                $title = $manufacturer . ' ' . $productType;
                if ($modelNumber) {
                    $title .= ' (' . $modelNumber . ')';
                }
                $suggestions[] = $title;
                $confidence = 85;
            }
        }

        if (empty($suggestions) && $description) {
            // Fallback: extract key terms from description
            $keyTerms = $this->extractKeyTerms($description);
            if (!empty($keyTerms)) {
                $suggestions[] = implode(' ', array_slice($keyTerms, 0, 4));
                $confidence = 60;
            }
        }

        return [
            'suggestions' => $suggestions,
            'reasoning' => 'Generated title based on manufacturer, product type, and key features',
            'confidence_level' => $confidence,
        ];
    }

    /**
     * Suggest manufacturer based on product title and description patterns
     *
     * @param array $data Product data containing title and description
     * @param array $context Additional context for suggestion
     * @return array Suggestion results with confidence level
     */
    private function suggestManufacturer(array $data, array $context): array
    {
        $title = strtolower($data['title'] ?? '');
        $description = strtolower($data['description'] ?? '');

        $suggestions = [];
        $confidence = 0;

        $text = $title . ' ' . $description;

        foreach ($this->manufacturerPatterns as $manufacturer => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($text, $pattern) !== false) {
                    $suggestions[] = ucfirst($manufacturer);
                    $confidence = max($confidence, 80);
                    break;
                }
            }
        }

        return [
            'suggestions' => array_unique($suggestions),
            'reasoning' => 'Identified manufacturer based on product name and description patterns',
            'confidence_level' => $confidence,
        ];
    }

    /**
     * Suggest product description based on title and specifications
     *
     * @param array $data Product data containing title, manufacturer, specs
     * @param array $context Additional context for suggestion
     * @return array Suggestion results with confidence level
     */
    private function suggestDescription(array $data, array $context): array
    {
        $title = $data['title'] ?? '';
        $technicalSpecs = $data['technical_specifications'] ?? '';

        if (!$title) {
            return [
                'suggestions' => [],
                'reasoning' => 'Need product title to generate description',
                'confidence_level' => 0,
            ];
        }

        $productType = $this->extractProductType($title);
        $description = '';

        // Build description based on product type
        switch ($productType) {
            case 'usb-c':
                $description = 'High-quality USB-C adapter that provides reliable connectivity and data transfer. '
                    . 'Features durable construction with premium materials for long-lasting performance.';
                if (strpos(strtolower($title), 'hdmi') !== false) {
                    $description .= ' Supports 4K video output for crystal-clear display connectivity.';
                }
                break;
            case 'hdmi':
                $description = 'Premium HDMI cable/adapter designed for high-definition video '
                    . 'and audio transmission. Supports the latest HDMI standards for optimal compatibility.';
                break;
            case 'ethernet':
                $description = 'Reliable network adapter providing fast and stable internet connectivity. '
                    . 'Built with high-quality components for consistent performance.';
                break;
            default:
                $description = 'Quality ' . strtolower($title) . ' designed for reliable performance and durability. '
                    . 'Built to meet high standards of connectivity and compatibility.';
        }

        if ($technicalSpecs) {
            $description .= ' ' . $technicalSpecs;
        }

        return [
            'suggestions' => [$description],
            'reasoning' => 'Generated description based on product type and technical specifications',
            'confidence_level' => 75,
        ];
    }

    /**
     * Suggest technical specifications based on product type
     *
     * @param array $data Product data containing title and description
     * @param array $context Additional context for suggestion
     * @return array Suggestion results with confidence level
     */
    private function suggestTechnicalSpecs(array $data, array $context): array
    {
        $title = strtolower($data['title'] ?? '');
        $description = strtolower($data['description'] ?? '');

        $suggestions = [];
        $confidence = 0;

        $text = $title . ' ' . $description;
        $productType = $this->extractProductType($text);

        if (isset($this->technicalSpecs[$productType])) {
            $specs = [];
            foreach ($this->technicalSpecs[$productType] as $category => $options) {
                $specs[] = ucwords(str_replace('_', ' ', $category)) . ': ' . $options[0];
            }

            $suggestions[] = implode('; ', $specs);
            $confidence = 70;
        }

        return [
            'suggestions' => $suggestions,
            'reasoning' => 'Generated technical specifications based on product type',
            'confidence_level' => $confidence,
        ];
    }

    /**
     * Suggest model number based on manufacturer patterns
     *
     * @param array $data Product data containing manufacturer
     * @param array $context Additional context for suggestion
     * @return array Suggestion results with confidence level
     */
    private function suggestModelNumber(array $data, array $context): array
    {
        $manufacturer = strtolower($data['manufacturer'] ?? '');

        $suggestions = [];
        $confidence = 0;

        // Pattern-based model number suggestions
        switch ($manufacturer) {
            case 'anker':
                $suggestions[] = 'A' . rand(8000, 9999);
                $confidence = 40;
                break;
            case 'belkin':
                $suggestions[] = 'F2CU' . rand(100, 999);
                $confidence = 40;
                break;
            case 'ugreen':
                $suggestions[] = 'CM' . rand(100, 999);
                $confidence = 40;
                break;
        }

        return [
            'suggestions' => $suggestions,
            'reasoning' => 'Generated model number based on manufacturer naming patterns',
            'confidence_level' => $confidence,
        ];
    }

    /**
     * Suggest price based on product type and manufacturer
     *
     * @param array $data Product data containing title and manufacturer
     * @param array $context Additional context for suggestion
     * @return array Suggestion results with confidence level
     */
    private function suggestPrice(array $data, array $context): array
    {
        $title = strtolower($data['title'] ?? '');
        $manufacturer = strtolower($data['manufacturer'] ?? '');

        $suggestions = [];
        $confidence = 0;

        // Price estimation based on product type and manufacturer
        $productType = $this->extractProductType($title);
        $basePrice = 0;

        switch ($productType) {
            case 'usb-c':
                $basePrice = strpos($title, 'hub') !== false ? 45 : 25;
                break;
            case 'hdmi':
                $basePrice = 20;
                break;
            case 'ethernet':
                $basePrice = 15;
                break;
            default:
                $basePrice = 30;
        }

        // Manufacturer premium
        $multiplier = 1.0;
        if (in_array($manufacturer, ['apple', 'belkin', 'anker'])) {
            $multiplier = 1.3;
        }

        $estimatedPrice = round($basePrice * $multiplier, 2);
        $suggestions[] = (string)$estimatedPrice;
        $confidence = 50;

        return [
            'suggestions' => $suggestions,
            'reasoning' => 'Estimated price based on product type and manufacturer positioning',
            'confidence_level' => $confidence,
        ];
    }

    /**
     * Suggest alt text for product images
     *
     * @param array $data Product data containing title and manufacturer
     * @param array $context Additional context for suggestion
     * @return array Suggestion results with confidence level
     */
    private function suggestAltText(array $data, array $context): array
    {
        $title = $data['title'] ?? '';
        $manufacturer = $data['manufacturer'] ?? '';

        if (!$title) {
            return [
                'suggestions' => [],
                'reasoning' => 'Need product title to generate alt text',
                'confidence_level' => 0,
            ];
        }

        $altText = $title;
        if ($manufacturer) {
            $altText = $manufacturer . ' ' . str_replace($manufacturer, '', $title);
        }
        $altText = trim($altText) . ' product image';

        return [
            'suggestions' => [$altText],
            'reasoning' => 'Generated accessible alt text describing the product',
            'confidence_level' => 85,
        ];
    }

    /**
     * Suggest testing standards based on product type
     *
     * @param array $data Product data containing title and specifications
     * @param array $context Additional context for suggestion
     * @return array Suggestion results with confidence level
     */
    private function suggestTestingStandard(array $data, array $context): array
    {
        $title = strtolower($data['title'] ?? '');
        $technicalSpecs = strtolower($data['technical_specifications'] ?? '');

        $suggestions = [];
        $confidence = 0;

        $text = $title . ' ' . $technicalSpecs;

        if (strpos($text, 'usb') !== false) {
            $suggestions[] = 'USB-IF Certified';
            $confidence = 70;
        }
        if (strpos($text, 'hdmi') !== false) {
            $suggestions[] = 'HDMI 2.1 Compliance';
            $confidence = 70;
        }
        if (strpos($text, 'ethernet') !== false || strpos($text, 'network') !== false) {
            $suggestions[] = 'IEEE 802.3';
            $confidence = 75;
        }

        return [
            'suggestions' => array_unique($suggestions),
            'reasoning' => 'Suggested testing standards based on product connectivity type',
            'confidence_level' => $confidence,
        ];
    }

    /**
     * Suggest certifying organizations based on product and standards
     *
     * @param array $data Product data containing testing standards and title
     * @param array $context Additional context for suggestion
     * @return array Suggestion results with confidence level
     */
    private function suggestCertifyingOrganization(array $data, array $context): array
    {
        $testingStandard = strtolower($data['testing_standard'] ?? '');
        $title = strtolower($data['title'] ?? '');

        $suggestions = [];
        $confidence = 0;

        // Common certifications for electronic products
        $suggestions[] = 'FCC';
        $suggestions[] = 'CE';
        $confidence = 60;

        if (strpos($testingStandard, 'usb') !== false || strpos($title, 'usb') !== false) {
            $suggestions[] = 'USB-IF';
            $confidence = 75;
        }

        if (strpos($title, 'rohs') !== false || strpos($testingStandard, 'rohs') !== false) {
            $suggestions[] = 'RoHS';
            $confidence = 80;
        }

        return [
            'suggestions' => array_unique($suggestions),
            'reasoning' => 'Common certifying organizations for electronic products',
            'confidence_level' => $confidence,
        ];
    }

    /**
     * Get default suggestions when no specific handler exists
     *
     * @param array $productData Product data
     * @param array $context Additional context
     * @return array Empty suggestion results
     */
    private function getDefaultSuggestions(array $productData, array $context): array
    {
        return [
            'suggestions' => [],
            'reasoning' => 'No AI suggestions available for this field type',
            'confidence_level' => 0,
        ];
    }

    /**
     * Extract product type from text using pattern matching
     *
     * @param string $text Input text to analyze
     * @return string Detected product type or 'generic'
     */
    private function extractProductType(string $text): string
    {
        $text = strtolower($text);

        foreach ($this->productTypePatterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($text, $pattern) !== false) {
                    return $type;
                }
            }
        }

        return 'generic';
    }

    /**
     * Extract key terms from text by removing stop words
     *
     * @param string $text Input text to analyze
     * @return array Array of key terms
     */
    private function extractKeyTerms(string $text): array
    {
        // Simple keyword extraction
        $words = str_word_count(strtolower($text), 1);
        $stopWords = [
            'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of',
            'with', 'by', 'this', 'that', 'is', 'are', 'a', 'an',
        ];

        $keywords = array_filter($words, function ($word) use ($stopWords) {
            return !in_array($word, $stopWords) && strlen($word) > 3;
        });

        return array_values(array_unique($keywords));
    }
}
