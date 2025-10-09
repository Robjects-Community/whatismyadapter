<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Cake\I18n\DateTime;
use Cake\Utility\Text;

class CreateProductsReliability extends AbstractMigration
{
    /**
     * Create products reliability system with three tables:
     * 1. products_reliability - polymorphic summary/current values
     * 2. products_reliability_fields - current per-field scores for analysis
     * 3. products_reliability_logs - immutable history with checksums
     *
     * Also creates MySQL triggers for backward compatibility with existing
     * products.reliability_score column.
     *
     * @return void
     */
    public function up(): void
    {
        // =====================================================================
        // TABLE 1: products_reliability (polymorphic summary/current values)
        // =====================================================================
        $productsReliability = $this->table('products_reliability', [
            'id' => false,
            'primary_key' => ['id'],
        ]);

        $productsReliability
            ->addColumn('id', 'uuid', [
                'default' => null,
                'null' => false,
                'comment' => 'Primary key'
            ])
            ->addColumn('model', 'string', [
                'default' => null,
                'limit' => 20, // Match slugs table pattern
                'null' => false,
                'comment' => 'Model name (e.g., Products, Articles)'
            ])
            ->addColumn('foreign_key', 'uuid', [
                'default' => null,
                'null' => false,
                'comment' => 'Foreign key to the referenced model record'
            ])
            ->addColumn('total_score', 'decimal', [
                'precision' => 3,
                'scale' => 2,
                'default' => '0.00',
                'null' => false,
                'comment' => 'Normalized total reliability score (0.00-1.00)'
            ])
            ->addColumn('completeness_percent', 'decimal', [
                'precision' => 5,
                'scale' => 2,
                'default' => '0.00',
                'null' => false,
                'comment' => 'Completeness percentage (0.00-100.00)'
            ])
            ->addColumn('field_scores_json', 'json', [
                'default' => null,
                'null' => true,
                'comment' => 'Current per-field breakdown (canonicalized JSON)'
            ])
            ->addColumn('scoring_version', 'string', [
                'limit' => 32,
                'default' => 'v1',
                'null' => false,
                'comment' => 'Scoring algorithm version'
            ])
            ->addColumn('last_source', 'string', [
                'limit' => 20,
                'default' => 'system',
                'null' => false,
                'comment' => 'Last update source: user, ai, admin, system'
            ])
            ->addColumn('last_calculated', 'datetime', [
                'default' => null,
                'null' => true,
                'comment' => 'When reliability was last calculated'
            ])
            ->addColumn('updated_by_user_id', 'uuid', [
                'default' => null,
                'null' => true,
                'comment' => 'User who last updated (no FK constraint)'
            ])
            ->addColumn('updated_by_service', 'string', [
                'limit' => 100,
                'default' => null,
                'null' => true,
                'comment' => 'Service that last updated (e.g., openai:gpt-4o-mini)'
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => false,
                'comment' => 'Record creation timestamp'
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => false,
                'comment' => 'Record modification timestamp'
            ])
            // Indexes for performance
            ->addIndex(['model', 'foreign_key'], [
                'unique' => true,
                'name' => 'idx_rel_model_fk'
            ])
            ->addIndex(['total_score'], [
                'name' => 'idx_rel_total_score'
            ])
            ->addIndex(['model'], [
                'name' => 'idx_rel_model'
            ])
            ->addIndex(['foreign_key'], [
                'name' => 'idx_rel_fk'
            ])
            ->addIndex(['last_calculated'], [
                'name' => 'idx_rel_last_calculated'
            ])
            ->addIndex(['updated_by_user_id'], [
                'name' => 'idx_rel_updated_by_user'
            ])
            ->create();

        // =====================================================================
        // TABLE 2: products_reliability_fields (current per-field scores)
        // =====================================================================
        $productsReliabilityFields = $this->table('products_reliability_fields', [
            'id' => false,
            'primary_key' => ['model', 'foreign_key', 'field'],
        ]);

        $productsReliabilityFields
            ->addColumn('model', 'string', [
                'default' => null,
                'limit' => 20, // Match slugs table pattern
                'null' => false,
                'comment' => 'Model name (e.g., Products, Articles)'
            ])
            ->addColumn('foreign_key', 'uuid', [
                'default' => null,
                'null' => false,
                'comment' => 'Foreign key to the referenced model record'
            ])
            ->addColumn('field', 'string', [
                'limit' => 64,
                'null' => false,
                'comment' => 'Field name (e.g., title, description, manufacturer)'
            ])
            ->addColumn('score', 'decimal', [
                'precision' => 3,
                'scale' => 2,
                'default' => '0.00',
                'null' => false,
                'comment' => 'Field reliability score (0.00-1.00)'
            ])
            ->addColumn('weight', 'decimal', [
                'precision' => 4,
                'scale' => 3,
                'default' => '0.000',
                'null' => false,
                'comment' => 'Field weight in total score calculation (0-1)'
            ])
            ->addColumn('max_score', 'decimal', [
                'precision' => 3,
                'scale' => 2,
                'default' => '1.00',
                'null' => false,
                'comment' => 'Maximum possible score for this field'
            ])
            ->addColumn('notes', 'string', [
                'limit' => 255,
                'default' => null,
                'null' => true,
                'comment' => 'Scoring rationale/notes'
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => false,
                'comment' => 'Record creation timestamp'
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => false,
                'comment' => 'Record modification timestamp'
            ])
            // Indexes for performance
            ->addIndex(['model', 'foreign_key'], [
                'name' => 'idx_prf_model_fk'
            ])
            ->addIndex(['field'], [
                'name' => 'idx_prf_field'
            ])
            ->create();

        // =====================================================================
        // TABLE 3: products_reliability_logs (immutable history with checksums)
        // =====================================================================
        $productsReliabilityLogs = $this->table('products_reliability_logs', [
            'id' => false,
            'primary_key' => ['id'],
        ]);

        $productsReliabilityLogs
            ->addColumn('id', 'uuid', [
                'default' => null,
                'null' => false,
                'comment' => 'Primary key'
            ])
            ->addColumn('model', 'string', [
                'default' => null,
                'limit' => 20, // Match slugs table pattern  
                'null' => false,
                'comment' => 'Model name (e.g., Products, Articles)'
            ])
            ->addColumn('foreign_key', 'uuid', [
                'default' => null,
                'null' => false,
                'comment' => 'Foreign key to the referenced model record'
            ])
            ->addColumn('from_total_score', 'decimal', [
                'precision' => 3,
                'scale' => 2,
                'default' => null,
                'null' => true,
                'comment' => 'Previous total reliability score'
            ])
            ->addColumn('to_total_score', 'decimal', [
                'precision' => 3,
                'scale' => 2,
                'default' => null,
                'null' => false,
                'comment' => 'New total reliability score'
            ])
            ->addColumn('from_field_scores_json', 'json', [
                'default' => null,
                'null' => true,
                'comment' => 'Previous field scores (canonicalized JSON)'
            ])
            ->addColumn('to_field_scores_json', 'json', [
                'default' => null,
                'null' => false,
                'comment' => 'New field scores (canonicalized JSON)'
            ])
            ->addColumn('source', 'string', [
                'limit' => 20,
                'null' => false,
                'comment' => 'Update source: user, ai, admin, system'
            ])
            ->addColumn('actor_user_id', 'uuid', [
                'default' => null,
                'null' => true,
                'comment' => 'User who triggered the update (no FK constraint)'
            ])
            ->addColumn('actor_service', 'string', [
                'limit' => 100,
                'default' => null,
                'null' => true,
                'comment' => 'Service that triggered update (e.g., openai:gpt-4o-mini)'
            ])
            ->addColumn('message', 'text', [
                'default' => null,
                'null' => true,
                'comment' => 'Reason/context for the update'
            ])
            ->addColumn('checksum_sha256', 'char', [
                'limit' => 64,
                'null' => false,
                'comment' => 'SHA256 checksum for log verification'
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => false,
                'comment' => 'Log entry timestamp'
            ])
            // Indexes for performance
            ->addIndex(['model', 'foreign_key', 'created'], [
                'name' => 'idx_prl_model_fk_created'
            ])
            ->addIndex(['source'], [
                'name' => 'idx_prl_source'
            ])
            ->addIndex(['actor_user_id'], [
                'name' => 'idx_prl_actor_user'
            ])
            ->create();

        // =====================================================================
        // DATA MIGRATION: Backfill from products.reliability_score
        // =====================================================================
        $this->backfillProductsReliability();

        // =====================================================================
        // MYSQL TRIGGERS: Maintain backward compatibility with products table
        // =====================================================================
        $this->createBackwardCompatibilityTriggers();
    }

    /**
     * Backfill products_reliability table from existing products.reliability_score data
     *
     * @return void
     */
    private function backfillProductsReliability(): void
    {
        $connection = $this->getAdapter()->getConnection();
        $now = new DateTime();
        
        // Get all products with their current reliability scores
        $products = $connection->execute("
            SELECT id, reliability_score, created, modified 
            FROM products 
            ORDER BY id
        ")->fetchAll('assoc');

        $this->output->writeln(sprintf('Backfilling %d products...', count($products)));

        foreach ($products as $product) {
            $productId = $product['id'];
            $reliabilityScore = $product['reliability_score'] ?? '0.00';
            
            // Ensure score is within valid range
            $reliabilityScore = max(0.00, min(1.00, (float)$reliabilityScore));

            // Generate UUIDs for the new records
            $summaryId = Text::uuid();
            $logId = Text::uuid();
            
            // Insert into products_reliability
            $connection->execute("
                INSERT INTO products_reliability (
                    id, model, foreign_key, total_score, completeness_percent,
                    field_scores_json, scoring_version, last_source, last_calculated,
                    updated_by_user_id, updated_by_service, created, modified
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $summaryId,
                'Products',
                $productId,
                $reliabilityScore,
                0.00, // completeness_percent (will be calculated later)
                null, // field_scores_json (will be calculated later)
                'v1',
                'system',
                $now->format('Y-m-d H:i:s'),
                null, // updated_by_user_id
                'migration:backfill',
                $product['created'],
                $now->format('Y-m-d H:i:s')
            ]);

            // Create a simple checksum for the initial migration log
            $logPayload = [
                'model' => 'Products',
                'foreign_key' => $productId,
                'from_total_score' => null,
                'to_total_score' => $reliabilityScore,
                'from_field_scores_json' => null,
                'to_field_scores_json' => [],
                'source' => 'system',
                'actor_user_id' => null,
                'actor_service' => 'migration:backfill',
                'created' => $now->format('c')
            ];
            
            ksort($logPayload); // Ensure consistent key ordering
            $checksum = hash('sha256', json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            // Insert into products_reliability_logs
            $connection->execute("
                INSERT INTO products_reliability_logs (
                    id, model, foreign_key, from_total_score, to_total_score,
                    from_field_scores_json, to_field_scores_json, source, actor_user_id,
                    actor_service, message, checksum_sha256, created
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $logId,
                'Products',
                $productId,
                null, // from_total_score
                $reliabilityScore,
                null, // from_field_scores_json
                '{}', // to_field_scores_json (empty JSON object)
                'system',
                null, // actor_user_id
                'migration:backfill',
                'Initial migration from products.reliability_score column',
                $checksum,
                $now->format('Y-m-d H:i:s')
            ]);
        }

        $this->output->writeln('Backfill completed successfully.');
    }

    /**
     * Create MySQL triggers to maintain backward compatibility with products.reliability_score
     *
     * @return void
     */
    private function createBackwardCompatibilityTriggers(): void
    {
        $connection = $this->getAdapter()->getConnection();
        $triggersCreated = 0;

        $triggers = [
            'products_reliability_after_insert' => "
                CREATE TRIGGER products_reliability_after_insert
                AFTER INSERT ON products_reliability
                FOR EACH ROW
                BEGIN
                    IF NEW.model = 'Products' THEN
                        UPDATE products 
                        SET reliability_score = NEW.total_score,
                            modified = NEW.modified
                        WHERE id = NEW.foreign_key;
                    END IF;
                END
            ",
            'products_reliability_after_update' => "
                CREATE TRIGGER products_reliability_after_update
                AFTER UPDATE ON products_reliability
                FOR EACH ROW
                BEGIN
                    IF NEW.model = 'Products' AND (NEW.total_score <> OLD.total_score) THEN
                        UPDATE products 
                        SET reliability_score = NEW.total_score,
                            modified = NEW.modified
                        WHERE id = NEW.foreign_key;
                    END IF;
                END
            ",
            'products_reliability_after_delete' => "
                CREATE TRIGGER products_reliability_after_delete
                AFTER DELETE ON products_reliability
                FOR EACH ROW
                BEGIN
                    IF OLD.model = 'Products' THEN
                        UPDATE products 
                        SET reliability_score = 0.00,
                            modified = NOW()
                        WHERE id = OLD.foreign_key;
                    END IF;
                END
            "
        ];

        foreach ($triggers as $triggerName => $triggerSql) {
            try {
                $connection->execute($triggerSql);
                $triggersCreated++;
                $this->output->writeln("✓ Created trigger: {$triggerName}");
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
                if (strpos($errorMessage, '1419') !== false || strpos($errorMessage, 'SUPER privilege') !== false) {
                    $this->output->writeln("⚠ Skipped trigger {$triggerName}: Insufficient MySQL privileges");
                    $this->output->writeln("  Note: Backward compatibility with products.reliability_score will be handled in the application layer.");
                } else {
                    $this->output->writeln("✗ Failed to create trigger {$triggerName}: " . $errorMessage);
                }
            }
        }

        if ($triggersCreated > 0) {
            $this->output->writeln("Successfully created {$triggersCreated} MySQL triggers for backward compatibility.");
        } else {
            $this->output->writeln("⚠ No triggers created due to MySQL privilege restrictions.");
            $this->output->writeln("  The ReliabilityBehavior will handle products.reliability_score updates in the application layer.");
        }
    }

    /**
     * Rollback migration
     *
     * @return void
     */
    public function down(): void
    {
        // Drop triggers first
        $connection = $this->getAdapter()->getConnection();
        
        try {
            $connection->execute("DROP TRIGGER IF EXISTS products_reliability_after_insert");
            $connection->execute("DROP TRIGGER IF EXISTS products_reliability_after_update"); 
            $connection->execute("DROP TRIGGER IF EXISTS products_reliability_after_delete");
            $this->output->writeln('MySQL triggers dropped.');
        } catch (Exception $e) {
            $this->output->writeln('Warning: Could not drop triggers: ' . $e->getMessage());
        }

        // Drop tables in reverse order (due to potential dependencies)
        $this->table('products_reliability_logs')->drop()->save();
        $this->table('products_reliability_fields')->drop()->save();
        $this->table('products_reliability')->drop()->save();

        $this->output->writeln('All reliability tables dropped.');
        $this->output->writeln('Note: products.reliability_score column and index remain intact.');
    }
}
