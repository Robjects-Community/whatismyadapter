<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ArticlesTranslationsFixture
 *
 * Required by the TranslateBehavior used in ArticlesTable
 */
class ArticlesTranslationsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [];
        parent::init();
    }

    /**
     * Table definition
     *
     * @return array
     */
    public function schema(): array
    {
        return [
            'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'autoIncrement' => true],
            'locale' => ['type' => 'string', 'length' => 6, 'null' => false, 'default' => null],
            'model' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null],
            'foreign_key' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null],
            'field' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null],
            'content' => ['type' => 'text', 'null' => true, 'default' => null],
            '_constraints' => [
                'primary' => ['type' => 'primary', 'columns' => ['id']],
                'I18N_LOCALE_FIELD' => [
                    'type' => 'unique',
                    'columns' => ['locale', 'model', 'foreign_key', 'field'],
                ],
            ],
        ];
    }
}
