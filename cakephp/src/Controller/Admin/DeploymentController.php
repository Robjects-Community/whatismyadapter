<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Cache\Cache;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use Cake\Validation\Validator;
use Exception;

/**
 * Admin Deployment Controller
 *
 * Handles deployment path selection and management for Willow CMS.
 * Provides two main deployment paths:
 * 1. Link Existing URL/Domain - Connect to an existing website or application
 * 2. Create New Deployment - Build a fresh deployment with custom files
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 */
class DeploymentController extends AppController
{
    /**
     * @var \App\Model\Table\ArticlesTable
     */
    protected $Articles;

    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Articles = TableRegistry::getTableLocator()->get('Articles');
    }

    /**
     * Clears the content cache
     *
     * @return void
     */
    private function clearContentCache(): void
    {
        Cache::clear('content');
    }

    /**
     * Main deployment path chooser interface
     *
     * @return \Cake\Http\Response|null
     */
    public function choosePath(): ?Response
    {
        $this->set('pageTitle', __('Choose Your Deployment Path'));
        $this->set('title', __('Deployment Path'));

        return null;
    }

    /**
     * Link existing URL/domain functionality
     *
     * @return \Cake\Http\Response|null
     */
    public function linkExisting(): ?Response
    {
        $this->set('pageTitle', __('Link Existing URL/Domain'));
        $this->set('title', __('Link Existing'));

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Validate and process existing URL/domain linking
            $validator = new Validator();
            $validator
                ->requirePresence('domain_url', 'create')
                ->notEmptyString('domain_url', __('Please enter a valid URL or domain'))
                ->url('domain_url', __('Please enter a valid URL'))
                ->requirePresence('verification_method', 'create')
                ->inList('verification_method', ['dns', 'file', 'meta'], __('Please select a valid verification method'));

            $errors = $validator->validate($data);
            
            if (empty($errors)) {
                // Process the linking
                $linkResult = $this->processExistingLink($data);
                
                if ($linkResult['success']) {
                    $this->Flash->success($linkResult['message']);
                    return $this->redirect(['action' => 'verifyConnection', '?' => ['domain' => $data['domain_url']]]);
                } else {
                    $this->Flash->error($linkResult['message']);
                }
            } else {
                $this->Flash->error(__('Please correct the errors below.'));
                $this->set('errors', $errors);
            }
        }

        return null;
    }

    /**
     * Create new deployment functionality
     *
     * @return \Cake\Http\Response|null
     */
    public function createNew(): ?Response
    {
        $page = $this->Articles->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['kind'] = 'page'; // Ensure this is a page
            $data['user_id'] = $this->request->getAttribute('identity')->id;
            
            // Handle custom file uploads
            $customFiles = $this->processCustomFiles($data);
            if ($customFiles) {
                $data = array_merge($data, $customFiles);
            }
            
            $page = $this->Articles->patchEntity($page, $data);
            
            if ($this->Articles->save($page)) {
                $this->clearContentCache();
                $this->Flash->success(__('The new page deployment has been created successfully.'));
                return $this->redirect(['controller' => 'Pages', 'action' => 'view', $page->id]);
            }
            $this->Flash->error(__('The deployment could not be created. Please, try again.'));
        }

        // Get existing slugs for validation
        $existingSlugs = $this->Articles->find()
            ->select(['slug'])
            ->where(['kind' => 'page'])
            ->toArray();

        $this->set(compact('page', 'existingSlugs'));
        $this->set('pageTitle', __('Create New Deployment'));
        $this->set('title', __('Create New Deployment'));

        return null;
    }

    /**
     * Verify connection to existing domain
     *
     * @return \Cake\Http\Response|null
     */
    public function verifyConnection(): ?Response
    {
        $domain = $this->request->getQuery('domain');
        
        if (empty($domain)) {
            $this->Flash->error(__('No domain specified for verification.'));
            return $this->redirect(['action' => 'linkExisting']);
        }

        $verificationResult = $this->performDomainVerification($domain);
        
        $this->set(compact('domain', 'verificationResult'));
        $this->set('pageTitle', __('Verify Domain Connection'));
        $this->set('title', __('Domain Verification'));

        return null;
    }

    /**
     * Process existing URL/domain linking
     *
     * @param array $data Form data
     * @return array Result with success status and message
     */
    private function processExistingLink(array $data): array
    {
        try {
            $domain = $data['domain_url'];
            $verificationMethod = $data['verification_method'];
            
            // Basic URL validation and cleanup
            if (!filter_var($domain, FILTER_VALIDATE_URL)) {
                // Try adding http:// if not present
                if (!preg_match('/^https?:\/\//', $domain)) {
                    $domain = 'http://' . $domain;
                }
                
                if (!filter_var($domain, FILTER_VALIDATE_URL)) {
                    return [
                        'success' => false,
                        'message' => __('Invalid URL format. Please enter a valid URL.')
                    ];
                }
            }

            // Check if domain is reachable
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $domain);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($result === false || $httpCode >= 400) {
                return [
                    'success' => false,
                    'message' => __('Unable to connect to the specified domain. Please check the URL and try again.')
                ];
            }

            // Store link information for verification
            $this->request->getSession()->write('pending_domain_link', [
                'domain' => $domain,
                'verification_method' => $verificationMethod,
                'timestamp' => time()
            ]);

            return [
                'success' => true,
                'message' => __('Domain connection initiated. Please proceed with verification.')
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => __('An error occurred while processing the domain link: {0}', $e->getMessage())
            ];
        }
    }

    /**
     * Process custom file uploads for new deployments
     *
     * @param array $data Form data
     * @return array|null Processed custom files data
     */
    private function processCustomFiles(array $data): ?array
    {
        $customFiles = [];
        
        // Process custom CSS
        if (!empty($data['custom_css'])) {
            $customFiles['custom_css'] = $data['custom_css'];
        }
        
        // Process custom JavaScript
        if (!empty($data['custom_js'])) {
            $customFiles['custom_js'] = $data['custom_js'];
        }
        
        // Process custom HTML (will be integrated into body)
        if (!empty($data['custom_html'])) {
            if (!empty($data['body'])) {
                $data['body'] = $data['body'] . "\n\n" . $data['custom_html'];
            } else {
                $data['body'] = $data['custom_html'];
            }
        }
        
        // Handle file uploads if any
        $uploadedFiles = $this->request->getUploadedFiles();
        
        if (!empty($uploadedFiles['css_file']) && $uploadedFiles['css_file']->getError() === UPLOAD_ERR_OK) {
            $cssContent = file_get_contents($uploadedFiles['css_file']->getStream()->getMetadata('uri'));
            $customFiles['custom_css'] = ($customFiles['custom_css'] ?? '') . "\n" . $cssContent;
        }
        
        if (!empty($uploadedFiles['js_file']) && $uploadedFiles['js_file']->getError() === UPLOAD_ERR_OK) {
            $jsContent = file_get_contents($uploadedFiles['js_file']->getStream()->getMetadata('uri'));
            $customFiles['custom_js'] = ($customFiles['custom_js'] ?? '') . "\n" . $jsContent;
        }
        
        if (!empty($uploadedFiles['html_file']) && $uploadedFiles['html_file']->getError() === UPLOAD_ERR_OK) {
            $htmlContent = file_get_contents($uploadedFiles['html_file']->getStream()->getMetadata('uri'));
            $data['body'] = ($data['body'] ?? '') . "\n\n" . $htmlContent;
        }

        return empty($customFiles) ? null : $customFiles;
    }

    /**
     * Perform domain verification
     *
     * @param string $domain Domain to verify
     * @return array Verification results
     */
    private function performDomainVerification(string $domain): array
    {
        $pendingLink = $this->request->getSession()->read('pending_domain_link');
        
        if (!$pendingLink || $pendingLink['domain'] !== $domain) {
            return [
                'status' => 'error',
                'message' => __('No pending verification found for this domain.')
            ];
        }

        $verificationMethod = $pendingLink['verification_method'];
        $result = ['status' => 'pending', 'checks' => []];

        try {
            // Perform basic connectivity check
            $result['checks']['connectivity'] = $this->checkDomainConnectivity($domain);
            
            // Perform specific verification based on method
            switch ($verificationMethod) {
                case 'dns':
                    $result['checks']['dns'] = $this->checkDNSVerification($domain);
                    break;
                case 'file':
                    $result['checks']['file'] = $this->checkFileVerification($domain);
                    break;
                case 'meta':
                    $result['checks']['meta'] = $this->checkMetaTagVerification($domain);
                    break;
            }
            
            // Determine overall status
            $allPassed = true;
            foreach ($result['checks'] as $check) {
                if (!$check['passed']) {
                    $allPassed = false;
                    break;
                }
            }
            
            $result['status'] = $allPassed ? 'verified' : 'failed';
            $result['message'] = $allPassed 
                ? __('Domain verification completed successfully!')
                : __('Domain verification failed. Please check the requirements and try again.');

        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['message'] = __('Verification error: {0}', $e->getMessage());
        }

        return $result;
    }

    /**
     * Check domain connectivity
     *
     * @param string $domain Domain to check
     * @return array Check result
     */
    private function checkDomainConnectivity(string $domain): array
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $domain);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $passed = ($result !== false && $httpCode < 400);
            
            return [
                'passed' => $passed,
                'message' => $passed 
                    ? __('Domain is accessible (HTTP {0})', $httpCode)
                    : __('Domain is not accessible (HTTP {0})', $httpCode)
            ];

        } catch (Exception $e) {
            return [
                'passed' => false,
                'message' => __('Connectivity check failed: {0}', $e->getMessage())
            ];
        }
    }

    /**
     * Check DNS verification
     *
     * @param string $domain Domain to check
     * @return array Check result
     */
    private function checkDNSVerification(string $domain): array
    {
        // This would check for specific DNS records
        // For now, return a placeholder
        return [
            'passed' => false,
            'message' => __('DNS verification not yet implemented. Please use file or meta tag verification.')
        ];
    }

    /**
     * Check file verification
     *
     * @param string $domain Domain to check
     * @return array Check result
     */
    private function checkFileVerification(string $domain): array
    {
        $verificationToken = Text::uuid();
        $verificationFile = '/willow-cms-verification.txt';
        $verificationUrl = rtrim($domain, '/') . $verificationFile;

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $verificationUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && !empty($content)) {
                return [
                    'passed' => true,
                    'message' => __('Verification file found and accessible.')
                ];
            } else {
                return [
                    'passed' => false,
                    'message' => __('Verification file not found. Please upload willow-cms-verification.txt to your domain root.')
                ];
            }

        } catch (Exception $e) {
            return [
                'passed' => false,
                'message' => __('File verification failed: {0}', $e->getMessage())
            ];
        }
    }

    /**
     * Check meta tag verification
     *
     * @param string $domain Domain to check
     * @return array Check result
     */
    private function checkMetaTagVerification(string $domain): array
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $domain);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $html = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || empty($html)) {
                return [
                    'passed' => false,
                    'message' => __('Unable to retrieve domain HTML for meta tag verification.')
                ];
            }

            // Look for Willow CMS meta tag
            $metaPattern = '/<meta\s+name=["\']willow-cms-verification["\']\s+content=["\']([^"\']+)["\']\s*\/?>/i';
            
            if (preg_match($metaPattern, $html, $matches)) {
                return [
                    'passed' => true,
                    'message' => __('Willow CMS verification meta tag found.')
                ];
            } else {
                return [
                    'passed' => false,
                    'message' => __('Verification meta tag not found. Please add the required meta tag to your page head.')
                ];
            }

        } catch (Exception $e) {
            return [
                'passed' => false,
                'message' => __('Meta tag verification failed: {0}', $e->getMessage())
            ];
        }
    }
}