<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Entity\Product;
use App\Model\Table\ProductsTable;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;
use RuntimeException;

/**
 * GenerateProducts command.
 *
 * Generates sample products with precise control over quantities and status distribution.
 * Example usage:
 * - Generate 50 products with default pending status: `bin/cake generate_products 50`
 * - Generate 30 products with custom status distribution: `bin/cake generate_products 30 pending:20,approved:8,rejected:2`
 * - Delete existing and generate 100 new products: `bin/cake generate_products 100 --delete`
 * - Generate for admin user only: `bin/cake generate_products 25 --admin-only`
 */
class GenerateProductsCommand extends Command
{
    /**
     * Valid verification statuses for products
     */
    private const VALID_STATUSES = ['pending', 'approved', 'rejected'];

    /**
     * Default verification status for non-admin users
     */
    private const DEFAULT_STATUS = 'pending';

    /**
     * @var string UUID of the admin user
     */
    private string $adminUserId;

    /**
     * @var array<string> List of regular user IDs
     */
    private array $regularUserIds = [];

    /**
     * @var \App\Model\Table\ProductsTable
     */
    private ProductsTable $Products;

    /**
     * Sample product data for realistic generation
     *
     * @var array<string, array<int, string>>
     */
    private array $sampleData = [
        'manufacturers' => [
            'Apple', 'Samsung', 'Microsoft', 'Sony', 'Nintendo', 'Tesla',
            'Ford', 'Toyota', 'Nike', 'Adidas', 'Canon', 'Nikon',
            'Dell', 'HP', 'Lenovo', 'ASUS', 'Logitech', 'Razer',
            'Intel', 'AMD', 'NVIDIA', 'LG', 'Panasonic', 'Philips',
            'Bosch', 'Siemens', 'KitchenAid', 'Dyson', 'Roomba', 'GoPro',
        ],
        'categories' => [
            'Electronics', 'Computing', 'Gaming', 'Mobile Devices', 'Cameras',
            'Audio', 'Home & Garden', 'Sports & Fitness', 'Automotive',
            'Kitchen Appliances', 'Wearables', 'Smart Home', 'Tools',
            'Books', 'Clothing', 'Toys', 'Health & Beauty', 'Office Supplies',
        ],
        'adjectives' => [
            'Professional', 'Premium', 'Advanced', 'Compact', 'Wireless',
            'Smart', 'Portable', 'High-Performance', 'Ultra', 'Pro',
            'Max', 'Mini', 'Lite', 'Plus', 'Elite', 'Classic',
            'Digital', 'Automatic', 'Manual', 'Deluxe', 'Standard',
        ],
        'product_types' => [
            'Smartphone', 'Laptop', 'Tablet', 'Headphones', 'Speaker',
            'Camera', 'Watch', 'Monitor', 'Keyboard', 'Mouse',
            'Printer', 'Scanner', 'Router', 'Drive', 'Charger',
            'Cable', 'Case', 'Stand', 'Mount', 'Adapter',
            'Microphone', 'Webcam', 'Controller', 'Console', 'Software',
        ],
    ];

    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Products = TableRegistry::getTableLocator()->get('Products');
        $this->loadUsers();
        $this->ensureTags();
    }

    /**
     * Build option parser method.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription([
                'Generate sample products with precise control over quantity and status distribution.',
                '',
                'By default, products are generated with "pending" verification status for regular users',
                'to simulate real-world scenarios where user submissions require admin review.',
                '',
                'Status distribution examples:',
                '  pending:20,approved:8,rejected:2  - Generate 20 pending, 8 approved, 2 rejected',
                '  approved:50                       - Generate 50 approved products only',
                '  pending:30,approved:20            - Generate 30 pending, 20 approved',
                '',
                'All generated products will appear in the admin interface for management.',
            ])
            ->addArgument('quantity', [
                'help' => __('Total number of products to generate'),
                'required' => true,
            ])
            ->addArgument('status_distribution', [
                'help' => __(
                    'Status distribution in format "status:count,status:count" '
                    . '(e.g., "pending:20,approved:8,rejected:2")',
                ),
                'required' => false,
                'default' => null,
            ])
            ->addOption('delete', [
                'help' => __('Delete all existing products before generating new ones'),
                'boolean' => true,
                'short' => 'd',
            ])
            ->addOption('admin-only', [
                'help' => __('Assign all products to admin user only (useful for testing admin workflows)'),
                'boolean' => true,
                'short' => 'a',
            ])
            ->addOption('with-images', [
                'help' => __('Generate products with sample image references'),
                'boolean' => true,
                'short' => 'i',
            ])
            ->addOption('featured-percent', [
                'help' => __('Percentage of products to mark as featured (0-100)'),
                'short' => 'f',
                'default' => 10,
            ]);

        return $parser;
    }

    /**
     * Execute the command
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $quantity = (int)$args->getArgument('quantity');
        $statusDistribution = $args->getArgument('status_distribution');

        if ($quantity <= 0) {
            $io->error(__('Quantity must be a positive integer.'));

            return static::CODE_ERROR;
        }

        // Delete existing products if requested
        if ($args->getOption('delete')) {
            $this->Products->deleteAll([]);
            $io->out(__('All existing products have been deleted.'));
        }

        // Parse status distribution
        $statusCounts = $this->parseStatusDistribution($statusDistribution, $quantity, $io);
        if ($statusCounts === null) {
            return static::CODE_ERROR;
        }

        // Validate total matches requested quantity
        $totalFromDistribution = array_sum($statusCounts);
        if ($totalFromDistribution !== $quantity) {
            $io->error(__(
                'Status distribution total ({0}) does not match requested quantity ({1}).',
                $totalFromDistribution,
                $quantity,
            ));

            return static::CODE_ERROR;
        }

        $io->out(__('Generating {0} products with distribution:', $quantity));
        foreach ($statusCounts as $status => $count) {
            $io->out(__('  - {0}: {1} products', $status, $count));
        }

        $successCount = 0;
        $failCount = 0;
        $featuredPercent = max(0, min(100, (int)$args->getOption('featured-percent')));
        $adminOnly = $args->getOption('admin-only');
        $withImages = $args->getOption('with-images');

        // Generate products for each status
        foreach ($statusCounts as $status => $count) {
            $io->out(__('Generating {0} products with status: {1}', $count, $status));

            for ($i = 0; $i < $count; $i++) {
                $product = $this->generateProduct($status, $featuredPercent, $adminOnly, $withImages);

                if ($this->Products->save($product, ['associated' => ['Tags']])) {
                    $io->verbose(__(
                        'Generated product: {0} (Status: {1})',
                        $product->title,
                        $product->verification_status,
                    ));
                    $successCount++;
                } else {
                    $io->error(__('Failed to generate product: {0}', $product->title ?? 'Unknown'));
                    $errors = $product->getErrors();
                    foreach ($errors as $field => $fieldErrors) {
                        foreach ($fieldErrors as $error) {
                            $io->error(__('Error in {0}: {1}', $field, $error));
                        }
                    }
                    $failCount++;
                }
            }
        }

        // Summary
        $io->success(__('Successfully generated {0} products.', $successCount));
        if ($failCount > 0) {
            $io->warning(__('Failed to generate {0} products.', $failCount));
        }

        // Admin interface guidance
        $io->out('');
        $io->info(__('Products are now available in the admin interface:'));
        $io->out(__('  - Dashboard: /admin/products/dashboard'));
        $io->out(__('  - All Products: /admin/products'));
        $io->out(__('  - Pending Review: /admin/products/pending-review'));

        if ($statusCounts['pending'] ?? 0 > 0) {
            $io->out(__('  - {0} products are pending admin review', $statusCounts['pending']));
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Parse status distribution argument
     *
     * @param string|null $distribution Status distribution string
     * @param int $totalQuantity Total quantity requested
     * @param \Cake\Console\ConsoleIo $io Console IO for error output
     * @return array<string, int>|null Parsed status counts or null on error
     */
    private function parseStatusDistribution(?string $distribution, int $totalQuantity, ConsoleIo $io): ?array
    {
        if (empty($distribution)) {
            // Default: all products as pending for admin review
            return [self::DEFAULT_STATUS => $totalQuantity];
        }

        $statusCounts = [];
        $pairs = explode(',', $distribution);

        foreach ($pairs as $pair) {
            $parts = explode(':', trim($pair));
            if (count($parts) !== 2) {
                $io->error(__(
                    'Invalid status distribution format: {0}. Expected format: "status:count,status:count"',
                    $pair,
                ));

                return null;
            }

            $status = strtolower(trim($parts[0]));
            $count = (int)trim($parts[1]);

            if (!in_array($status, self::VALID_STATUSES, true)) {
                $io->error(__(
                    'Invalid status: {0}. Valid statuses: {1}',
                    $status,
                    implode(', ', self::VALID_STATUSES),
                ));

                return null;
            }

            if ($count <= 0) {
                $io->error(__('Count must be positive for status: {0}', $status));

                return null;
            }

            if (isset($statusCounts[$status])) {
                $statusCounts[$status] += $count;
            } else {
                $statusCounts[$status] = $count;
            }
        }

        return $statusCounts;
    }

    /**
     * Generate a single product with specified parameters
     *
     * @param string $status Verification status
     * @param int $featuredPercent Percentage chance of being featured
     * @param bool $adminOnly Whether to assign only to admin user
     * @param bool $withImages Whether to include sample images
     * @return \App\Model\Entity\Product
     */
    private function generateProduct(string $status, int $featuredPercent, bool $adminOnly, bool $withImages): Product
    {
        // Generate realistic product title and details
        $manufacturer = $this->getRandomItem($this->sampleData['manufacturers']);
        $adjective = $this->getRandomItem($this->sampleData['adjectives']);
        $productType = $this->getRandomItem($this->sampleData['product_types']);
        $category = $this->getRandomItem($this->sampleData['categories']);

        // Create varied title formats
        $titleFormats = [
            '{manufacturer} {adjective} {productType}',
            '{manufacturer} {productType} {adjective}',
            '{adjective} {manufacturer} {productType}',
            '{manufacturer} {productType}',
        ];
        $titleFormat = $this->getRandomItem($titleFormats);
        $title = str_replace(
            ['{manufacturer}', '{adjective}', '{productType}'],
            [$manufacturer, $adjective, $productType],
            $titleFormat,
        );

        // Generate model number
        $modelNumber = $manufacturer . '-' . strtoupper(substr($productType, 0, 3)) . '-' .
                      str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT);

        // Generate description
        $description = $this->generateProductDescription($manufacturer, $productType, $category);

        // Generate price (varied by category and status)
        $price = $this->generatePrice($productType, $status);

        // Create product entity
        $product = $this->Products->newEmptyEntity();
        $product->title = $title;
        $product->description = $description;
        $product->manufacturer = $manufacturer;
        $product->model_number = $modelNumber;
        $product->price = $price;
        $product->currency = 'USD';

        // Image handling
        if ($withImages) {
            $product->image = $this->generateImageReference($productType);
            $product->alt_text = $title . ' product image';
        }

        // Status and publication logic
        $product->verification_status = $status;

        // Publishing logic based on status and admin settings
        if ($status === 'approved') {
            $product->is_published = true;
        } elseif ($status === 'pending') {
            // Pending products are unpublished until approved
            $product->is_published = false;
        } else { // rejected
            $product->is_published = false;
        }

        // Featured status (only for approved/published products typically)
        $product->featured = ($product->is_published && rand(1, 100) <= $featuredPercent);

        // User assignment
        if ($adminOnly || empty($this->regularUserIds)) {
            $product->user_id = $this->adminUserId;
        } else {
            // For pending status, prefer regular users (realistic workflow)
            if ($status === 'pending') {
                $product->user_id = $this->getRandomItem($this->regularUserIds);
            } else {
                // Mix of admin and regular users for approved/rejected
                $allUsers = array_merge([$this->adminUserId], $this->regularUserIds);
                $product->user_id = $this->getRandomItem($allUsers);
            }
        }

        // Reliability score (based on status)
        $product->reliability_score = $this->generateReliabilityScore($status);

        // View count (based on publication status)
        $product->view_count = $product->is_published ? rand(0, 500) : rand(0, 10);

        // Random creation date (within last 6 months for realism)
        $product->created = $this->generateRandomDate();

        // Add tags
        $product->tags = $this->selectRandomTags();

        return $product;
    }

    /**
     * Generate realistic product description
     *
     * @param string $manufacturer Manufacturer name
     * @param string $productType Product type
     * @param string $category Product category
     * @return string Generated description
     */
    private function generateProductDescription(string $manufacturer, string $productType, string $category): string
    {
        $features = [
            'High-quality construction', 'Advanced technology', 'User-friendly design',
            'Energy efficient', 'Compact form factor', 'Reliable performance',
            'Professional grade', 'Easy to use', 'Durable materials',
            'Innovative features', 'Ergonomic design', 'Superior craftsmanship',
        ];

        $benefits = [
            'Perfect for daily use', 'Ideal for professionals', 'Great for beginners',
            'Suitable for all skill levels', 'Excellent value for money',
            'Industry leading performance', 'Trusted by experts worldwide',
        ];

        // Select 2-4 random features
        $numFeatures = rand(2, 4);
        $shuffledFeatures = $features;
        shuffle($shuffledFeatures);
        $selectedFeatures = array_slice($shuffledFeatures, 0, $numFeatures);
        $selectedBenefit = $this->getRandomItem($benefits);

        $description = "The {$manufacturer} {$productType} is a premium {$category} product featuring " .
                      strtolower(implode(', ', $selectedFeatures)) .
                      ". {$selectedBenefit}.";

        return $description;
    }

    /**
     * Generate price based on product type and status
     *
     * @param string $productType Product type
     * @param string $status Verification status
     * @return float Generated price
     */
    private function generatePrice(string $productType, string $status): float
    {
        // Base prices by product type
        $basePrices = [
            'Smartphone' => [400, 1200],
            'Laptop' => [500, 2500],
            'Tablet' => [200, 800],
            'Headphones' => [50, 400],
            'Camera' => [300, 2000],
            'Watch' => [100, 800],
        ];

        // Default range if type not found
        $range = $basePrices[$productType] ?? [25, 500];

        $basePrice = rand($range[0] * 100, $range[1] * 100) / 100;

        // Slight price adjustment based on status (for realism)
        $multiplier = match ($status) {
            'approved' => rand(95, 105) / 100, // Approved products vary normally
            'pending' => rand(90, 110) / 100, // Pending might have more variation
            'rejected' => rand(80, 95) / 100, // Rejected might be slightly lower
        };

        return round($basePrice * $multiplier, 2);
    }

    /**
     * Generate reliability score based on status
     *
     * @param string $status Verification status
     * @return float Generated reliability score
     */
    private function generateReliabilityScore(string $status): float
    {
        return match ($status) {
            'approved' => rand(750, 1000) / 100, // 7.5 to 10.0
            'pending' => rand(0, 500) / 100, // 0.0 to 5.0 (not yet verified)
            'rejected' => rand(100, 400) / 100, // 1.0 to 4.0 (failed verification)
        };
    }

    /**
     * Generate image reference
     *
     * @param string $productType Product type
     * @return string Generated image filename
     */
    private function generateImageReference(string $productType): string
    {
        $baseNames = [
            'sample', 'product', 'demo', 'test', 'placeholder',
        ];

        $baseName = $this->getRandomItem($baseNames);
        $suffix = rand(1000, 9999);

        return "{$baseName}_{$productType}_{$suffix}.jpg";
    }

    /**
     * Generate random date within the last 6 months
     *
     * @return \Cake\I18n\FrozenTime
     */
    private function generateRandomDate(): DateTime
    {
        $sixMonthsAgo = (new DateTime())->subMonths(6);
        $now = new DateTime();

        $randomTimestamp = rand($sixMonthsAgo->getTimestamp(), $now->getTimestamp());

        return new DateTime('@' . $randomTimestamp);
    }

    /**
     * Select random tags for the product
     *
     * @return array<\App\Model\Entity\Tag> Selected tags
     */
    private function selectRandomTags(): array
    {
        $tagsTable = TableRegistry::getTableLocator()->get('Tags');
        $availableTags = $tagsTable->find()
            ->select(['id', 'title'])
            ->limit(50)
            ->toArray();

        if (empty($availableTags)) {
            return [];
        }

        $numTags = rand(1, min(5, count($availableTags)));
        $selectedIndices = (array)array_rand($availableTags, $numTags);

        // Convert to array if only one tag selected
        if (!is_array($selectedIndices)) {
            $selectedIndices = [$selectedIndices];
        }

        $selectedTags = [];
        foreach ($selectedIndices as $index) {
            $selectedTags[] = $availableTags[$index];
        }

        return $selectedTags;
    }

    /**
     * Get random item from array
     *
     * @param array<mixed> $items Array of items
     * @return mixed Random item
     */
    private function getRandomItem(array $items): mixed
    {
        return $items[array_rand($items)];
    }

    /**
     * Load admin and regular users
     *
     * @throws \RuntimeException When no admin user is found
     * @return void
     */
    private function loadUsers(): void
    {
        $usersTable = TableRegistry::getTableLocator()->get('Users');

        // Load admin user
        $adminUser = $usersTable->find()
            ->select(['id'])
            ->where(['is_admin' => true])
            ->first();

        if (!$adminUser) {
            throw new RuntimeException(__('No admin user found. Please create an admin user first.'));
        }

        $this->adminUserId = $adminUser->id;

        // Load regular users
        $regularUsers = $usersTable->find()
            ->select(['id'])
            ->where(['is_admin' => false, 'active' => true])
            ->limit(10)
            ->toArray();

        $this->regularUserIds = array_column($regularUsers, 'id');
    }

    /**
     * Ensure minimum number of tags exist
     *
     * @return void
     */
    private function ensureTags(): void
    {
        $tagsTable = TableRegistry::getTableLocator()->get('Tags');
        $existingCount = $tagsTable->find()->count();

        if ($existingCount >= 15) {
            return;
        }

        $tagsToCreate = max(15 - $existingCount, 0);
        $sampleTags = [
            'Electronics', 'Mobile', 'Computing', 'Gaming', 'Audio', 'Video',
            'Photography', 'Wearables', 'Smart Home', 'Automotive', 'Sports',
            'Health', 'Kitchen', 'Tools', 'Office', 'Entertainment', 'Education',
            'Professional', 'Consumer', 'Premium', 'Budget', 'Wireless',
            'Portable', 'Indoor', 'Outdoor',
        ];

        for ($i = 0; $i < $tagsToCreate; $i++) {
            $tag = $tagsTable->newEmptyEntity();
            $baseTag = $sampleTags[$i % count($sampleTags)];
            $suffix = $i >= count($sampleTags) ? ' ' . (intval($i / count($sampleTags)) + 1) : '';
            $tagTitle = $baseTag . $suffix;
            $tag->title = $tagTitle;
            $tag->description = "Sample tag: {$tagTitle}";

            $tagsTable->save($tag);
        }
    }
}
