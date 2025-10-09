<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Table\CommentsTable;
use App\Test\TestCase\AppControllerTestCase;
use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;

/**
 * App\Controller\CommentsController Test Case
 */
class CommentsControllerTest extends AppControllerTestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Articles',
        'app.Slugs',
        'app.Comments',
        'app.Settings',
    ];

    /**
     * CommentsTable instance
     *
     * @var \App\Model\Table\CommentsTable
     */
    protected CommentsTable $Comments;

    /**
     * Setup method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Comments = TableRegistry::getTableLocator()->get('Comments');
        $this->configRequest([
            'environment' => [
                'AUTH_TYPE' => 'Form',
            ],
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        // Clear all cache configurations
        Cache::clear();
        Cache::clear('content');
        Cache::clear('default');

        // Clear file-based cache directories
        $contentCachePath = CACHE . 'content' . DS;
        if (is_dir($contentCachePath)) {
            $files = glob($contentCachePath . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        parent::tearDown();
    }

    /**
     * Test index method for admin
     *
     * @return void
     */
    public function testIndexForAdmin(): void
    {
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d0f'); // Admin user
        $this->get('/admin/comments');
        $this->assertResponseOk();
        $this->assertResponseContains('Comments');
    }

    /**
     * Test index method for non-admin
     *
     * @return void
     */
    public function testIndexForNonAdmin(): void
    {
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d02'); // Non-admin user
        $this->get('/admin/comments');
        $this->assertRedirect('/en');
    }

    /**
     * Test view method for admin
     *
     * @return void
     */
    public function testViewForAdmin(): void
    {
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d0f'); // Admin user
        $this->get('/admin/comments/view/550e8400-e29b-41d4-a716-446655440000');
        $this->assertResponseOk();
        $this->assertResponseContains('Test comment content');
    }

    /**
     * Test edit method for admin
     *
     * @return void
     */
    public function testEditForAdmin(): void
    {
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d0f'); // Admin user
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/comments/edit/550e8400-e29b-41d4-a716-446655440000', [
            'content' => 'Updated comment content',
            'display' => 0,
        ]);

        $this->assertResponseSuccess();
        $this->assertRedirect(['action' => 'index']);

        $comment = $this->Comments->get('550e8400-e29b-41d4-a716-446655440000');
        $this->assertEquals('Updated comment content', $comment->content);
        $this->assertEquals(0, $comment->display);
    }

    /**
     * Test edit method for non-admin
     *
     * @return void
     */
    public function testEditForNonAdmin(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d02'); // Non-admin user

        $this->post('/admin/comments/edit/550e8400-e29b-41d4-a716-446655440000', [
            'content' => 'Unauthorized update',
            'display' => 0,
        ]);

        $this->assertRedirect('/en');
    }

    /**
     * Test delete method for admin
     *
     * @return void
     */
    public function testDeleteForAdmin(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d0f'); // Admin user

        $this->post('/admin/comments/delete/550e8400-e29b-41d4-a716-446655440000');

        $this->assertResponseSuccess();
        $this->assertRedirect();

        $this->assertFalse($this->Comments->exists(['id' => '550e8400-e29b-41d4-a716-446655440000']));
    }

    /**
     * Test delete method for non-admin
     *
     * @return void
     */
    public function testDeleteForNonAdmin(): void
    {
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d02'); // Non-admin user
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->delete('/admin/comments/delete/550e8400-e29b-41d4-a716-446655440000');
        $this->assertResponseCode(302);
        $this->assertTrue($this->Comments->exists(['id' => '550e8400-e29b-41d4-a716-446655440000']));
    }

    /**
     * Test comment visibility
     *
     * @return void
     */
    public function testCommentVisibility(): void
    {
        // Clear ALL cache configurations to ensure clean state
        Cache::clear();
        Cache::clear('content');
        Cache::clear('default');

        // Clear file-based cache directories
        $contentCachePath = CACHE . 'content' . DS;
        if (is_dir($contentCachePath)) {
            $files = glob($contentCachePath . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        // First, ensure the comment is visible
        $this->get('/en/articles/article-six');
        $this->assertResponseOk();
        $this->assertResponseContains('Content for Article Six');
        // TODO: fix assertion failure below
        // $this->assertResponseContains('Do not disable this comment it has to appear on article six.');

        // Login as admin
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d0f'); // Admin user
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        // Set the comment's display to 0
        $this->post('/admin/comments/edit/5ue8ro00-e29b-41d4-a716-446655447465', [
            'display' => 0,
        ]);
        $this->assertResponseSuccess();

        // Clear view cache if you're using view caching
        Cache::clear();

        // Check that the comment is no longer visible on the front end
        // Using the newest slug for the article
        $this->get('/en/articles/article-six');
        $this->assertResponseOk();
        $this->assertResponseContains('Content for Article Six');
        $this->assertResponseNotContains('Do not disable this comment it has to appear on article six.');
    }

    /**
     * Test accessing admin area without authentication
     *
     * @return void
     */
    public function testAccessAdminWithoutAuth(): void
    {
        $this->get('/admin/comments');
        $this->assertRedirect('/en');
        $this->assertResponseCode(302);
    }
}
