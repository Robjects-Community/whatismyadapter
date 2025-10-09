<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\CacheService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * Console command to insert sample prototype data for testing
 *
 * This command inserts sample data with all prototype fields populated
 * to demonstrate the functionality before database normalization.
 */
class InsertSampleDataCommand extends Command
{
    /**
     * Configure command options
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription('Insert sample prototype data for testing');

        $parser->addOption('force', [
            'short' => 'f',
            'boolean' => true,
            'help' => 'Force insert even if sample data already exists',
        ]);

        $parser->addOption('count', [
            'short' => 'c',
            'default' => 2,
            'help' => 'Number of sample products to create (default: 2)',
        ]);

        return $parser;
    }

    /**
     * Execute the command
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $productsTable = TableRegistry::getTableLocator()->get('Products');

        $io->out('<info>Inserting sample prototype data...</info>');

        // Check if we need a user ID
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $usersTable->find()->first();

        if (!$user) {
            $io->error('No users found in the system. Please create a user first.');

            return static::CODE_ERROR;
        }

        $userId = $user->id;
        $io->out("Using user ID: {$userId}");

        // Check if sample data already exists
        $existingSample = $productsTable->find()
            ->where(['prototype_notes LIKE' => '%Sample data for testing prototype schema%'])
            ->count();

        if ($existingSample > 0 && !$args->getOption('force')) {
            $io->warning("Sample data already exists ({$existingSample} records).");
            $io->out('Use --force to overwrite existing sample data.');

            return static::CODE_SUCCESS;
        }

        if ($args->getOption('force') && $existingSample > 0) {
            $io->out('Removing existing sample data...');
            $productsTable->deleteAll(['prototype_notes LIKE' => '%Sample data for testing prototype schema%']);
            $io->success("Removed {$existingSample} existing sample records.");
        }

        // Create enhanced sample data
        $sampleCount = (int)$args->getOption('count');
        $sampleProducts = $this->generateSampleData($userId, $sampleCount);

        $successCount = 0;
        $errorCount = 0;

        foreach ($sampleProducts as $i => $productData) {
            $io->out('Creating sample product ' . ($i + 1) . "/{$sampleCount}...");

            try {
                $product = $productsTable->newEntity($productData);

                if ($productsTable->save($product)) {
                    $successCount++;
                    $io->success("✓ Created: {$product->title}");
                } else {
                    $errorCount++;
                    $io->error('✗ Failed to create product ' . ($i + 1));
                    foreach ($product->getErrors() as $field => $errors) {
                        foreach ($errors as $error) {
                            $io->error("  {$field}: {$error}");
                        }
                    }
                }
            } catch (Exception $e) {
                $errorCount++;
                $io->error('✗ Exception creating product ' . ($i + 1) . ': ' . $e->getMessage());
            }
        }

        // Summary
        $io->out('');
        $io->out('<info>Sample data insertion completed:</info>');
        $io->success("✓ Successfully created: {$successCount} products");

        if ($errorCount > 0) {
            $io->error("✗ Failed to create: {$errorCount} products");
        }

        // Clear related caches
        $io->out('Clearing caches...');
        $cacheService = new CacheService();
        $cacheService->invalidateProductCaches();
        $io->success('✓ Caches cleared');

        return $successCount > 0 ? static::CODE_SUCCESS : static::CODE_ERROR;
    }

    /**
     * Generate comprehensive sample data
     */
    private function generateSampleData(string $userId, int $count): array
    {
        $sampleTemplates = [
            [
                'title' => 'USB-C to HDMI 4K Adapter',
                'slug' => 'usb-c-to-hdmi-4k-adapter',
                'description' => 'High-quality USB-C to HDMI adapter supporting 4K@60Hz output with HDR support',
                'manufacturer' => 'TechConnector',
                'model_number' => 'TC-UCH4K-001',
                'capability_category' => 'Display',
                'port_family' => 'USB',
                'device_category' => 'Laptop',
            ],
            [
                'title' => 'Lightning to USB-A Cable',
                'slug' => 'lightning-to-usb-a-cable',
                'description' => 'MFi Certified Lightning to USB-A cable for iPhone and iPad charging and data transfer',
                'manufacturer' => 'AppleConnect',
                'model_number' => 'AC-LTU-MFI-3FT',
                'capability_category' => 'Power & Data',
                'port_family' => 'Lightning',
                'device_category' => 'Smartphone',
            ],
            [
                'title' => 'Thunderbolt 3 to Dual DisplayPort Hub',
                'slug' => 'thunderbolt-3-dual-displayport-hub',
                'description' => 'Professional Thunderbolt 3 hub with dual DisplayPort outputs for multi-monitor setups',
                'manufacturer' => 'ProConnect',
                'model_number' => 'PC-TB3-DP2-PRO',
                'capability_category' => 'Display',
                'port_family' => 'Thunderbolt',
                'device_category' => 'Desktop',
            ],
            [
                'title' => 'Ethernet to USB-C Adapter',
                'slug' => 'ethernet-to-usb-c-adapter',
                'description' => 'Gigabit Ethernet adapter for USB-C devices with LED activity indicators',
                'manufacturer' => 'NetBridge',
                'model_number' => 'NB-ETH-UC-GIG',
                'capability_category' => 'Network',
                'port_family' => 'USB',
                'device_category' => 'Laptop',
            ],
        ];

        $products = [];

        for ($i = 0; $i < $count; $i++) {
            $template = $sampleTemplates[$i % count($sampleTemplates)];

            // Create unique variations
            $suffix = $i > 0 ? '-v' . ($i + 1) : '';

            $products[] = array_merge([
                'id' => sprintf('550e8400-e29b-41d4-a716-%012d', 440001 + $i),
                'user_id' => $userId,
                'price' => rand(1999, 9999) / 100, // $19.99 to $99.99
                'currency' => 'USD',
                'image' => strtolower(str_replace(' ', '-', $template['title'])) . '.jpg',
                'alt_text' => $template['title'] . ' product image',

                // Capability fields with variations
                'capability_name' => $template['title'] . ' Capability',
                'technical_specifications' => json_encode([
                    'resolution' => ['4K@60Hz', '1080p@60Hz', '4K@30Hz'][rand(0, 2)],
                    'bandwidth' => rand(5, 40) . ' Gbps',
                    'power_delivery' => rand(15, 100) . 'W',
                    'hdr_support' => (bool)rand(0, 1),
                    'version' => '2.' . rand(0, 1),
                ]),
                'testing_standard' => ['HDMI 2.1', 'USB 3.2', 'Thunderbolt 4', 'MFi Certified'][rand(0, 3)],
                'certifying_organization' => ['HDMI Licensing LLC', 'USB-IF', 'Intel', 'Apple Inc.'][rand(0, 3)],
                'capability_value' => rand(1, 10) . 'x Performance',
                'numeric_rating' => rand(70, 95) / 10,
                'is_certified' => (bool)rand(0, 1),
                'certification_date' => date('Y-m-d', strtotime('-' . rand(30, 365) . ' days')),

                // Category fields
                'parent_category_name' => $template['capability_category'] . ' Adapters',
                'category_description' => 'Professional ' . strtolower($template['capability_category']) . ' connectivity solutions',
                'category_icon' => ['fas fa-tv', 'fas fa-bolt', 'fas fa-network-wired', 'fas fa-plug'][rand(0, 3)],
                'display_order' => $i + 1,

                // Port/Connector fields with realistic variations
                'port_type_name' => $template['port_family'] . '-' . ['A', 'B', 'C'][rand(0, 2)],
                'endpoint_position' => ['end_a', 'end_b'][rand(0, 1)],
                'is_detachable' => (bool)rand(0, 1),
                'adapter_functionality' => 'Converts ' . $template['port_family'] . ' signals with enhanced compatibility',
                'form_factor' => ['Standard', 'Mini', 'Micro', 'Type-A', 'Type-B', 'Type-C'][rand(0, 5)],
                'connector_gender' => ['Male', 'Female', 'Reversible'][rand(0, 2)],
                'pin_count' => [8, 24, 40][rand(0, 2)],
                'max_voltage' => [5.0, 12.0, 20.0][rand(0, 2)],
                'max_current' => [1.5, 3.0, 5.0][rand(0, 2)],
                'data_pin_count' => rand(2, 8),
                'power_pin_count' => rand(2, 4),
                'ground_pin_count' => rand(1, 4),
                'electrical_shielding' => ['Braided Shield', 'Aluminum Foil', 'EMI Protection'][rand(0, 2)],
                'durability_cycles' => [5000, 10000, 15000][rand(0, 2)],

                // Device compatibility with realistic data
                'device_brand' => ['Apple', 'Samsung', 'Dell', 'HP', 'Lenovo'][rand(0, 4)],
                'device_model' => $template['device_category'] . ' Series ' . rand(1, 5),
                'compatibility_level' => ['Full', 'Full', 'Partial', 'Limited'][rand(0, 3)], // Weighted toward Full
                'compatibility_notes' => 'Tested and verified compatible with major ' . strtolower($template['device_category']) . ' models',
                'performance_rating' => rand(75, 98) / 10,
                'verification_date' => date('Y-m-d', strtotime('-' . rand(7, 90) . ' days')),
                'verified_by' => $template['manufacturer'] . ' QA Team',
                'user_reported_rating' => rand(70, 95) / 10,

                // Physical specs
                'physical_spec_name' => 'Cable Length',
                'spec_value' => rand(1, 6) . ' feet',
                'numeric_value' => (float)rand(1, 6),
                'spec_type' => 'measurement',
                'measurement_unit' => 'feet',
                'spec_description' => 'Optimal length for desktop and portable use',
                'introduced_date' => date('Y-m-d', strtotime('-' . rand(365, 1825) . ' days')),
                'physical_specs_summary' => rand(1, 6) . 'ft cable, premium materials',

                // Administrative
                'prototype_notes' => "Sample data for testing prototype schema - {$template['title']}{$suffix}",
                'needs_normalization' => true,
                'is_published' => true,
                'featured' => ($i % 3 === 0), // Every 3rd item is featured
                'verification_status' => ['verified', 'pending', 'verified'][rand(0, 2)],
                'reliability_score' => rand(75, 95) / 10,
                'view_count' => rand(0, 1000),
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ], $template);
        }

        return $products;
    }
}
