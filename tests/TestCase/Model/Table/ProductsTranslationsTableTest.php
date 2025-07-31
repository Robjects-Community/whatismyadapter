<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProductsTranslationsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ProductsTranslationsTable Test Case
 */
class ProductsTranslationsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ProductsTranslationsTable
     */
    protected $ProductsTranslations;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.ProductsTranslations',
        'app.Products',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ProductsTranslations') ? [] : ['className' => ProductsTranslationsTable::class];
        $this->ProductsTranslations = $this->getTableLocator()->get('ProductsTranslations', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ProductsTranslations);
        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        // Test valid translation
        $validTranslation = $this->ProductsTranslations->newEntity([
            'id' => 'prod-001-usb-c-cable',
            'locale' => 'fr_FR',
            'title' => 'Câble USB-C vers Lightning',
            'body' => 'Un excellent câble pour vos appareils Apple.',
        ]);

        $this->assertEmpty($validTranslation->getErrors(), 'Valid translation should not have errors');

        // Test invalid locale format
        $invalidLocale = $this->ProductsTranslations->newEntity([
            'id' => 'prod-001-usb-c-cable',
            'locale' => 'invalid_locale',
            'title' => 'Test Title',
        ]);

        $this->assertNotEmpty($invalidLocale->getErrors()['locale'], 'Invalid locale format should cause validation error');

        // Test missing required fields
        $missingFields = $this->ProductsTranslations->newEntity([]);
        $errors = $missingFields->getErrors();
        
        $this->assertArrayHasKey('id', $errors, 'Missing id should cause validation error');
        $this->assertArrayHasKey('locale', $errors, 'Missing locale should cause validation error');

        // Test title length validation
        $longTitle = $this->ProductsTranslations->newEntity([
            'id' => 'prod-001-usb-c-cable',
            'locale' => 'en_GB',
            'title' => str_repeat('A', 256), // Over 255 character limit
        ]);

        $this->assertNotEmpty($longTitle->getErrors()['title'], 'Title over 255 characters should cause validation error');

        // Test lede length validation
        $longLede = $this->ProductsTranslations->newEntity([
            'id' => 'prod-001-usb-c-cable',
            'locale' => 'en_GB',
            'lede' => str_repeat('A', 401), // Over 400 character limit
        ]);

        $this->assertNotEmpty($longLede->getErrors()['lede'], 'Lede over 400 characters should cause validation error');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        // Test foreign key constraint
        $invalidProduct = $this->ProductsTranslations->newEntity([
            'id' => 'non-existent-product-id',
            'locale' => 'en_GB',
            'title' => 'Test Title',
        ]);

        $result = $this->ProductsTranslations->save($invalidProduct);
        $this->assertFalse($result, 'Should not save translation for non-existent product');
        $this->assertNotEmpty($invalidProduct->getErrors()['id'], 'Invalid product ID should cause rule violation');

        // Test unique constraint (id + locale combination)
        $existingTranslation = $this->ProductsTranslations->get(['prod-001-usb-c-cable', 'en_GB']);
        $this->assertNotNull($existingTranslation, 'Existing translation should be found');

        $duplicateTranslation = $this->ProductsTranslations->newEntity([
            'id' => 'prod-001-usb-c-cable',
            'locale' => 'en_GB', // Same product and locale as existing
            'title' => 'Duplicate Title',
        ]);

        $result = $this->ProductsTranslations->save($duplicateTranslation);
        $this->assertFalse($result, 'Should not save duplicate translation for same product and locale');
        $this->assertNotEmpty($duplicateTranslation->getErrors()['locale'], 'Duplicate locale should cause rule violation');
    }

    /**
     * Test findByLocale method
     *
     * @return void
     */
    public function testFindByLocale(): void
    {
        // Test finding French translations
        $frenchTranslations = $this->ProductsTranslations->find('byLocale', ['locale' => 'fr_FR'])->toArray();
        $this->assertNotEmpty($frenchTranslations, 'Should find French translations');
        
        foreach ($frenchTranslations as $translation) {
            $this->assertEquals('fr_FR', $translation->locale, 'All translations should be in French');
        }

        // Test finding translations for non-existent locale
        $nonExistentLocale = $this->ProductsTranslations->find('byLocale', ['locale' => 'xx_XX'])->toArray();
        $this->assertEmpty($nonExistentLocale, 'Should not find translations for non-existent locale');

        // Test default locale behavior
        $defaultLocale = $this->ProductsTranslations->find('byLocale')->toArray();
        $this->assertNotEmpty($defaultLocale, 'Should find translations using default locale');
    }

    /**
     * Test findComplete method
     *
     * @return void
     */
    public function testFindComplete(): void
    {
        $completeTranslations = $this->ProductsTranslations->find('complete')->toArray();
        $this->assertNotEmpty($completeTranslations, 'Should find complete translations');

        foreach ($completeTranslations as $translation) {
            $this->assertNotEmpty($translation->title, 'Complete translation should have title');
            $this->assertNotEmpty($translation->body, 'Complete translation should have body');
        }

        // Create incomplete translation for testing
        $incompleteTranslation = $this->ProductsTranslations->newEntity([
            'id' => 'prod-002-hdmi-adapter',
            'locale' => 'pl_PL',
            'title' => 'Test Title',
            'body' => '', // Empty body makes it incomplete
        ]);

        $saved = $this->ProductsTranslations->save($incompleteTranslation);
        $this->assertNotFalse($saved, 'Should be able to save incomplete translation');

        // Verify incomplete translation is not found by findComplete
        $completeAfterAdd = $this->ProductsTranslations->find('complete')
            ->where(['id' => 'prod-002-hdmi-adapter', 'locale' => 'pl_PL'])
            ->toArray();
        
        $this->assertEmpty($completeAfterAdd, 'Incomplete translation should not be found by findComplete');
    }

    /**
     * Test findWithSeo method
     *
     * @return void
     */
    public function testFindWithSeo(): void
    {
        $seoTranslations = $this->ProductsTranslations->find('withSeo')->toArray();
        $this->assertNotEmpty($seoTranslations, 'Should find translations with SEO metadata');

        foreach ($seoTranslations as $translation) {
            $this->assertNotEmpty($translation->meta_title, 'SEO translation should have meta title');
            $this->assertNotEmpty($translation->meta_description, 'SEO translation should have meta description');
        }

        // Create translation without SEO for testing
        $noSeoTranslation = $this->ProductsTranslations->newEntity([
            'id' => 'prod-003-unpublished',
            'locale' => 'pt_PT',
            'title' => 'Test Title',
            'body' => 'Test body',
            'meta_title' => '', // Empty SEO fields
            'meta_description' => '',
        ]);

        $saved = $this->ProductsTranslations->save($noSeoTranslation);
        $this->assertNotFalse($saved, 'Should be able to save translation without SEO');

        // Verify translation without SEO is not found by findWithSeo
        $seoAfterAdd = $this->ProductsTranslations->find('withSeo')
            ->where(['id' => 'prod-003-unpublished', 'locale' => 'pt_PT'])
            ->toArray();
        
        $this->assertEmpty($seoAfterAdd, 'Translation without SEO should not be found by findWithSeo');
    }

    /**
     * Test getAvailableLocales method
     *
     * @return void
     */
    public function testGetAvailableLocales(): void
    {
        $locales = $this->ProductsTranslations->getAvailableLocales('prod-001-usb-c-cable');
        $this->assertNotEmpty($locales, 'Should find available locales for product');
        
        $localeValues = array_column($locales, 'locale');
        $this->assertContains('en_GB', $localeValues, 'Should include English locale');
        $this->assertContains('fr_FR', $localeValues, 'Should include French locale');

        // Test non-existent product
        $noLocales = $this->ProductsTranslations->getAvailableLocales('non-existent-product');
        $this->assertEmpty($noLocales, 'Should return empty array for non-existent product');
    }

    /**
     * Test getTranslationStats method
     *
     * @return void
     */
    public function testGetTranslationStats(): void
    {
        $stats = $this->ProductsTranslations->getTranslationStats('prod-001-usb-c-cable');
        
        $this->assertArrayHasKey('total_translations', $stats, 'Stats should include total translations');
        $this->assertArrayHasKey('complete_translations', $stats, 'Stats should include complete translations');
        $this->assertArrayHasKey('translations_with_seo', $stats, 'Stats should include SEO translations');
        $this->assertArrayHasKey('completion_percentage', $stats, 'Stats should include completion percentage');
        $this->assertArrayHasKey('seo_percentage', $stats, 'Stats should include SEO percentage');

        $this->assertGreaterThan(0, $stats['total_translations'], 'Should have some translations');
        $this->assertGreaterThanOrEqual(0, $stats['completion_percentage'], 'Completion percentage should be non-negative');
        $this->assertLessThanOrEqual(100, $stats['completion_percentage'], 'Completion percentage should not exceed 100');

        // Test non-existent product
        $emptyStats = $this->ProductsTranslations->getTranslationStats('non-existent-product');
        
        $this->assertEquals(0, $emptyStats['total_translations'], 'Non-existent product should have 0 translations');
        $this->assertEquals(0, $emptyStats['completion_percentage'], 'Non-existent product should have 0% completion');
    }

    /**
     * Test associations
     *
     * @return void
     */
    public function testAssociations(): void
    {
        $translation = $this->ProductsTranslations->get(['prod-001-usb-c-cable', 'en_GB'], [
            'contain' => ['Products']
        ]);

        $this->assertNotNull($translation->product, 'Translation should have associated product');
        $this->assertEquals('prod-001-usb-c-cable', $translation->product->id, 'Associated product should have correct ID');
        $this->assertEquals('USB-C to Lightning Cable', $translation->product->title, 'Associated product should have correct title');
    }

    /**
     * Test entity methods
     *
     * @return void
     */
    public function testEntityMethods(): void
    {
        $translation = $this->ProductsTranslations->get(['prod-001-usb-c-cable', 'en_GB']);

        // Test isComplete method
        $this->assertTrue($translation->isComplete(), 'Complete translation should return true for isComplete');

        // Test hasSeoMetadata method
        $this->assertTrue($translation->hasSeoMetadata(), 'Translation with SEO should return true for hasSeoMetadata');

        // Test getSocialDescriptions method
        $socialDescriptions = $translation->getSocialDescriptions();
        $this->assertIsArray($socialDescriptions, 'getSocialDescriptions should return array');
        $this->assertArrayHasKey('facebook', $socialDescriptions, 'Should include Facebook description');
        $this->assertArrayHasKey('linkedin', $socialDescriptions, 'Should include LinkedIn description');
        $this->assertArrayHasKey('instagram', $socialDescriptions, 'Should include Instagram description');
        $this->assertArrayHasKey('twitter', $socialDescriptions, 'Should include Twitter description');

        // Test display_locale virtual field
        $this->assertNotEmpty($translation->display_locale, 'Should have display locale');
        $this->assertEquals('English (UK)', $translation->display_locale, 'English locale should display correctly');
    }

    /**
     * Test CRUD operations
     *
     * @return void
     */
    public function testCrudOperations(): void
    {
        // Test Create
        $newTranslation = $this->ProductsTranslations->newEntity([
            'id' => 'prod-002-hdmi-adapter',
            'locale' => 'nl_NL',
            'title' => 'USB-C naar HDMI Adapter',
            'lede' => '4K HDMI uitgang adapter voor USB-C apparaten',
            'body' => 'Ondersteunt 4K@60Hz video uitgang met HDR ondersteuning.',
            'summary' => '4K HDMI adapter met 60Hz en HDR ondersteuning voor USB-C apparaten.',
            'meta_title' => 'USB-C naar HDMI Adapter 4K 60Hz HDR | DisplayAccessoires',
            'meta_description' => '4K USB-C naar HDMI adapter met 60Hz en HDR ondersteuning. Verbind je USB-C apparaten met monitoren en TVs.',
        ]);

        $saved = $this->ProductsTranslations->save($newTranslation);
        $this->assertNotFalse($saved, 'Should save new translation');
        $this->assertEquals('nl_NL', $saved->locale, 'Saved translation should have correct locale');

        // Test Read
        $retrieved = $this->ProductsTranslations->get(['prod-002-hdmi-adapter', 'nl_NL']);
        $this->assertEquals('USB-C naar HDMI Adapter', $retrieved->title, 'Retrieved translation should have correct title');

        // Test Update
        $retrieved->title = 'USB-C naar HDMI Adapter - Bijgewerkt';
        $updated = $this->ProductsTranslations->save($retrieved);
        $this->assertNotFalse($updated, 'Should update existing translation');
        $this->assertEquals('USB-C naar HDMI Adapter - Bijgewerkt', $updated->title, 'Updated translation should have new title');

        // Test Delete
        $deleted = $this->ProductsTranslations->delete($retrieved);
        $this->assertTrue($deleted, 'Should delete translation');

        // Verify deletion
        $this->expectException(\Cake\Datasource\Exception\RecordNotFoundException::class);
        $this->ProductsTranslations->get(['prod-002-hdmi-adapter', 'nl_NL']);
    }
}
