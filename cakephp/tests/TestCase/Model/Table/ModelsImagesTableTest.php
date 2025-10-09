<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ModelsImagesTable;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;

class ModelsImagesTableTest extends TestCase
{
    protected $ModelsImages;

    protected array $fixtures = [
        'app.ModelsImages',
        'app.Images',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ModelsImages') ? [] : ['className' => ModelsImagesTable::class];
        $this->ModelsImages = $this->getTableLocator()->get('ModelsImages', $config);
    }

    protected function tearDown(): void
    {
        unset($this->ModelsImages);
        parent::tearDown();
    }

    public function testInitialize(): void
    {
        $this->assertSame('models_images', $this->ModelsImages->getTable());
        $this->assertSame('id', $this->ModelsImages->getDisplayField());
        $this->assertSame('id', $this->ModelsImages->getPrimaryKey());
        $this->assertTrue($this->ModelsImages->hasBehavior('Timestamp'));
        $this->assertTrue($this->ModelsImages->hasAssociation('Images'));
    }

    public function testValidationDefault(): void
    {
        $validator = new Validator();
        $validator = $this->ModelsImages->validationDefault($validator);

        $this->assertTrue($validator->hasField('model'));
        $this->assertTrue($validator->isPresenceRequired('model', true)); // Changed to boolean
        $this->assertFalse($validator->isEmptyAllowed('model', false));

        $this->assertTrue($validator->hasField('foreign_key'));
        $this->assertTrue($validator->isPresenceRequired('foreign_key', true)); // Changed to boolean
        $this->assertFalse($validator->isEmptyAllowed('foreign_key', false));

        $this->assertTrue($validator->hasField('image_id'));
        $this->assertFalse($validator->isEmptyAllowed('image_id', false));
    }
}
