<?php
declare(strict_types=1);

namespace AdminTheme\Test\TestCase\View;

use AdminTheme\View\AppView;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * AdminTheme\View\AppView Test Case
 */
class AppViewTest extends TestCase
{
    /**
     * Subject under test
     *
     * @var \AdminTheme\View\AppView
     */
    protected $AppView;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $request = new ServerRequest();
        $response = new Response();

        $this->AppView = new AppView($request, $response);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->AppView);
        parent::tearDown();
    }

    /**
     * Test view instantiation
     *
     * @return void
     */
    public function testViewInstantiation(): void
    {
        $this->assertInstanceOf(AppView::class, $this->AppView);
        $this->assertInstanceOf(View::class, $this->AppView);
    }

    /**
     * Test that Gallery helper is loaded during initialization
     *
     * @return void
     */
    public function testGalleryHelperLoaded(): void
    {
        // Initialize the view
        $this->AppView->initialize();

        // Check if Gallery helper is loaded
        $this->assertTrue($this->AppView->helpers()->has('Gallery'));
    }

    /**
     * Test that Form helper has bootstrap templates configured
     *
     * @return void
     */
    public function testBootstrapFormTemplates(): void
    {
        // Initialize the view
        $this->AppView->initialize();

        // Get form helper
        $formHelper = $this->AppView->Form;

        // Test some key bootstrap template configurations
        $templates = $formHelper->getTemplates();

        $this->assertStringContainsString('form-label', $templates['label']);
        $this->assertStringContainsString('form-control', $templates['input']);
        $this->assertStringContainsString('form-control', $templates['textarea']);
        $this->assertStringContainsString('form-select', $templates['select']);
        $this->assertStringContainsString('form-check-input', $templates['checkbox']);
        $this->assertStringContainsString('invalid-feedback', $templates['error']);
    }

    /**
     * Test that various form control templates are properly configured for Bootstrap
     *
     * @return void
     */
    public function testFormControlTemplates(): void
    {
        $this->AppView->initialize();

        $formHelper = $this->AppView->Form;
        $templates = $formHelper->getTemplates();

        // Test input container templates
        $this->assertArrayHasKey('inputContainer', $templates);
        $this->assertArrayHasKey('inputContainerError', $templates);

        // Test that error template includes Bootstrap validation classes
        $this->assertStringContainsString('has-validation', $templates['inputContainerError']);

        // Test radio button configuration
        $this->assertArrayHasKey('radioContainer', $templates);
        $this->assertStringContainsString('form-check', $templates['radioContainer']);

        // Test input group templates for Bootstrap input groups
        $this->assertArrayHasKey('inputGroupContainer', $templates);
        $this->assertStringContainsString('input-group', $templates['inputGroupContainer']);
    }
}
