<?php
declare(strict_types=1);

namespace DefaultTheme\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\Http\Response;
use Cake\TestSuite\TestCase;
use DefaultTheme\Controller\Component\FrontEndSiteComponent;

/**
 * DefaultTheme\Controller\Component\FrontEndSiteComponent Test Case
 *
 * Tests the FrontEndSiteComponent functionality for setting up
 * front-end data like menu pages, tags, featured articles, etc.
 */
class FrontEndSiteComponentTest extends TestCase
{
    /**
     * Test fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Articles',
        'app.Tags',
        'app.Users',
    ];

    /**
     * Component instance
     *
     * @var \DefaultTheme\Controller\Component\FrontEndSiteComponent
     */
    protected FrontEndSiteComponent $component;

    /**
     * Controller instance
     *
     * @var \Cake\Controller\Controller
     */
    protected Controller $controller;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a test controller
        $request = new ServerRequest(['url' => '/']);
        $response = new Response();
        $this->controller = new Controller($request, $response);
        $this->controller->cacheKey = 'test_cache_';

        // Create component registry and component
        $registry = new ComponentRegistry($this->controller);
        $this->component = new FrontEndSiteComponent($registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->component, $this->controller);
        parent::tearDown();
    }

    /**
     * Test component instantiation
     *
     * @return void
     */
    public function testComponentInstantiation(): void
    {
        $this->assertInstanceOf(
            FrontEndSiteComponent::class,
            $this->component,
            'Component should be an instance of FrontEndSiteComponent'
        );
    }

    /**
     * Test that beforeRender is registered as an implemented event
     *
     * @return void
     */
    public function testImplementedEvents(): void
    {
        $events = $this->component->implementedEvents();
        
        $this->assertArrayHasKey(
            'Controller.beforeRender',
            $events,
            'Component should implement Controller.beforeRender event'
        );
        
        $this->assertEquals(
            'beforeRender',
            $events['Controller.beforeRender'],
            'Controller.beforeRender should call beforeRender method'
        );
    }

    /**
     * Test beforeRender skips admin routes
     *
     * @return void
     */
    public function testBeforeRenderSkipsAdminRoutes(): void
    {
        $request = new ServerRequest([
            'url' => '/admin/articles',
            'params' => ['prefix' => 'Admin']
        ]);
        $this->controller->setRequest($request);

        $event = new Event('Controller.beforeRender', $this->controller);
        $this->component->beforeRender($event);

        // Should not set any variables for admin routes
        $viewVars = $this->controller->viewBuilder()->getVars();
        $this->assertEmpty($viewVars, 'No view variables should be set for admin routes');
    }

    /**
     * Test beforeRender sets minimal variables for user auth actions
     *
     * @return void
     */
    public function testBeforeRenderSetsMinimalVariablesForUserActions(): void
    {
        $request = new ServerRequest([
            'url' => '/users/login',
            'params' => [
                'controller' => 'Users',
                'action' => 'login'
            ]
        ]);
        $this->controller->setRequest($request);

        $event = new Event('Controller.beforeRender', $this->controller);
        $this->component->beforeRender($event);

        // Should set minimal required variables
        $viewVars = $this->controller->viewBuilder()->getVars();
        $this->assertArrayHasKey('menuPages', $viewVars);
        $this->assertArrayHasKey('rootTags', $viewVars);
        $this->assertArrayHasKey('siteLanguages', $viewVars);
        $this->assertEquals([], $viewVars['menuPages']);
        $this->assertEquals([], $viewVars['rootTags']);
    }

    /**
     * Test beforeRender sets view variables for regular pages
     *
     * @return void
     */
    public function testBeforeRenderSetsViewVariablesForRegularPages(): void
    {
        $request = new ServerRequest([
            'url' => '/articles',
            'params' => [
                'controller' => 'Articles',
                'action' => 'index'
            ]
        ]);
        $this->controller->setRequest($request);

        $event = new Event('Controller.beforeRender', $this->controller);
        $this->component->beforeRender($event);

        // Should set all required front-end variables
        $viewVars = $this->controller->viewBuilder()->getVars();
        
        $expectedVariables = [
            'menuPages',
            'footerMenuPages',
            'rootTags',
            'featuredArticles',
            'articleArchives',
            'siteLanguages',
            'selectedSiteLanguage'
        ];

        foreach ($expectedVariables as $varName) {
            $this->assertArrayHasKey(
                $varName,
                $viewVars,
                "View variable '{$varName}' should be set"
            );
        }
    }

    /**
     * Test that component gets controller instance
     *
     * @return void
     */
    public function testComponentHasControllerReference(): void
    {
        $controller = $this->component->getController();
        
        $this->assertInstanceOf(
            Controller::class,
            $controller,
            'Component should have access to controller instance'
        );
    }
}
