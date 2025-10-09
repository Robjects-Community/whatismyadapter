<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use Cake\Log\Log;
use Cake\I18n\FrozenTime;
use Exception;

/**
 * ImportProducts command.
 */
class ImportProductsCommand extends Command
{
    /**
     * The name of this command.
     *
     * @var string
     */
    protected string $name = 'import_products';

    /**
     * Get the default command name.
     *
     * @return string
     */
    public static function defaultName(): string
    {
        return 'import_products';
    }

    /**
     * Get the command description.
     *
     * @return string
     */
    public static function getDescription(): string
    {
        return 'Import WhatIsMyAdapter products from CSV file';
    }

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/5/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return parent::buildOptionParser($parser)
            ->setDescription(static::getDescription())
            ->addOption('file', [
                'short' => 'f',
                'help' => 'CSV file path (default: tools/data/products_whatismyadapter.csv)',
                'default' => 'tools/data/products_whatismyadapter.csv'
            ])
            ->addOption('clear', [
                'boolean' => true,
                'help' => 'Clear existing products before import',
                'default' => false
            ]);
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null|void The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $csvFile = $args->getOption('file');
        $clearExisting = $args->getOption('clear');
        
        $io->out('<info>WhatIsMyAdapter Product Import</info>');
        $io->out('CSV file: ' . $csvFile);
        
        if (!file_exists($csvFile)) {
            $io->error("CSV file not found: $csvFile");
            return static::CODE_ERROR;
        }
        
        // Get Products table
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        
        // Clear existing products if requested
        if ($clearExisting) {
            $io->out('<warning>Clearing existing products...</warning>');
            $deleted = $productsTable->deleteAll(['1 = 1']);
            $io->out("Deleted $deleted existing products.");
        }
        
        // Parse CSV
        $handle = fopen($csvFile, 'r');
        if (!$handle) {
            $io->error('Cannot open CSV file');
            return static::CODE_ERROR;
        }
        
        // Get header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            $io->error('Cannot read CSV headers');
            fclose($handle);
            return static::CODE_ERROR;
        }
        
        $io->out('CSV Headers: ' . implode(', ', $headers));
        
        $imported = 0;
        $errors = 0;
        
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($headers)) {
                $io->warning("Row has " . count($row) . " columns, expected " . count($headers));
                continue;
            }
            
            // Combine headers with row data
            $data = array_combine($headers, $row);
            
            try {
                // Map CSV fields to database fields
                $productData = [
                    'id' => Text::uuid(),
                    'user_id' => '00000000-0000-0000-0000-000000000001', // System user
                    'title' => $data['title'],
                    'manufacturer' => $data['manufacturer'],
                    'model_number' => $data['model_number'] ?? null,
                    'price' => !empty($data['price']) ? (float) $data['price'] : null,
                    'currency' => $data['currency'] ?? 'USD',
                    'description' => $data['description'] ?? null,
                    'port_type_name' => $data['port_type_name'] ?? null,
                    'device_category' => $data['device_category'] ?? null,
                    'max_voltage' => !empty($data['max_voltage']) ? (float) $data['max_voltage'] : null,
                    'max_current' => !empty($data['max_current']) ? (float) $data['max_current'] : null,
                    'spec_value' => $data['spec_value'] ?? null,
                    'device_brand' => $data['device_brand'] ?? null,
                    'compatibility_level' => $data['compatibility_level'] ?? null,
                    'is_published' => !empty($data['is_published']) ? (bool) $data['is_published'] : false,
                    'featured' => !empty($data['featured']) ? (bool) $data['featured'] : false,
                    'is_certified' => !empty($data['is_certified']) ? (bool) $data['is_certified'] : false,
                    'certification_date' => (!empty($data['certification_date']) && $data['certification_date'] !== 'NULL') 
                        ? new FrozenTime($data['certification_date']) : null,
                    'technical_specifications' => !empty($data['technical_specifications']) ? $data['technical_specifications'] : null,
                    'slug' => strtolower(str_replace([' ', '/', '-'], ['_', '_', '_'], $data['title'])) . '_' . substr(md5($data['title']), 0, 8),
                    'display_order' => 0,
                    'numeric_rating' => null,
                    'reliability_score' => 3.50, // Default
                    'verification_status' => 'pending',
                    'view_count' => 0,
                    'created' => new FrozenTime(),
                    'modified' => new FrozenTime(),
                ];
                
                // Create new entity
                $product = $productsTable->newEntity($productData);
                
                if ($productsTable->save($product)) {
                    $imported++;
                    if ($imported % 10 == 0) {
                        $io->out("<success>Imported $imported products...</success>");
                    }
                } else {
                    $errors++;
                    $io->error("Failed to import '{$data['title']}': " . json_encode($product->getErrors()));
                }
                
            } catch (Exception $e) {
                $errors++;
                $io->error("Exception importing '{$data['title']}': " . $e->getMessage());
            }
        }
        
        fclose($handle);
        
        $io->out('');
        $io->out('<info>=== IMPORT SUMMARY ===</info>');
        $io->out("<success>Successfully imported: $imported products</success>");
        $io->out("<error>Errors: $errors</error>");
        $io->out("Total processed: " . ($imported + $errors));
        
        // Log the import
        Log::info("WhatIsMyAdapter import completed: $imported imported, $errors errors");
        
        // Show verification data
        $io->out('');
        $io->out('<info>=== VERIFICATION ===</info>');
        
        $totalCount = $productsTable->find()->count();
        $io->out("Total products in database: $totalCount");
        
        // Show breakdown by manufacturer
        $manufacturers = $productsTable->find()
            ->select(['manufacturer', 'count' => 'COUNT(*)'])
            ->group(['manufacturer'])
            ->orderAsc('manufacturer')
            ->toArray();
        
        $io->out('');
        $io->out('Breakdown by manufacturer:');
        foreach ($manufacturers as $mfg) {
            $io->out("  {$mfg->manufacturer}: {$mfg->count}");
        }
        
        // Show port types
        $portTypes = $productsTable->find()
            ->select(['port_type_name', 'count' => 'COUNT(*)'])
            ->where(['port_type_name IS NOT' => null])
            ->group(['port_type_name'])
            ->orderAsc('port_type_name')
            ->toArray();
            
        $io->out('');
        $io->out('Port types:');
        foreach ($portTypes as $port) {
            $io->out("  {$port->port_type_name}: {$port->count}");
        }
        
        return $errors > 0 ? static::CODE_ERROR : static::CODE_SUCCESS;
    }
}
