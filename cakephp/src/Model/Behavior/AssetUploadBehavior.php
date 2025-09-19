<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\Utility\Text;
use Psr\Http\Message\UploadedFileInterface;

/**
 * AssetUpload Behavior
 *
 * This behavior handles file uploads for page assets including JS, CSS, HTML files.
 * It validates file types, creates organized directory structures, and integrates
 * with queue jobs for post-processing tasks like minification or validation.
 */
class AssetUploadBehavior extends Behavior
{
    /**
     * Default configuration
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'base_path' => 'files/assets/', // Base path within webroot
        'allowed_types' => ['js', 'css', 'html', 'htm'], // Allowed file extensions
        'max_file_size' => 5242880, // 5MB in bytes
        'create_directory' => true, // Whether to create unique directories
        'queue_processing' => true, // Whether to queue processing jobs
    ];

    /**
     * Handle asset files during beforeSave
     *
     * @param \Cake\Event\EventInterface $event The event object
     * @param \Cake\Datasource\EntityInterface $entity The entity being saved
     * @param \ArrayObject $options Save options
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (isset($entity->asset_files) && is_array($entity->asset_files)) {
            $uploadedAssets = $this->processAssetFiles($entity->asset_files);
            
            if (!empty($uploadedAssets)) {
                $entity->assets_json = json_encode($uploadedAssets);
                $entity->asset_dir = $uploadedAssets[0]['directory'] ?? null;
                $entity->has_assets = 1;
                
                // Store processed files for afterSave processing
                $entity->_processed_assets = $uploadedAssets;
            }
            
            // Remove asset_files from entity as it's not a database field
            unset($entity->asset_files);
        }
    }

    /**
     * Queue processing jobs after successful save
     *
     * @param \Cake\Event\EventInterface $event The event object
     * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
     * @param \ArrayObject $options Save options
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if ($this->getConfig('queue_processing') && 
            isset($entity->_processed_assets) && 
            !empty($entity->_processed_assets)) {
            
            // Queue asset processing job
            $data = [
                'entity_id' => $entity->id,
                'assets' => $entity->_processed_assets,
                'entity_type' => $this->_table->getAlias()
            ];
            
            $this->_table->queueJob('App\Job\ProcessPageAssetsJob', $data);
        }
    }

    /**
     * Process uploaded asset files
     *
     * @param array $files Array of uploaded files
     * @return array Array of processed asset information
     */
    private function processAssetFiles(array $files): array
    {
        $processedAssets = [];
        $config = $this->getConfig();
        
        // Create unique directory for this upload batch
        $assetDir = 'page_assets_' . Text::uuid();
        $webroot = WWW_ROOT;
        $basePath = $config['base_path'];
        $targetDir = $webroot . $basePath . $assetDir;
        
        if ($config['create_directory'] && !is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        foreach ($files as $file) {
            if (!($file instanceof UploadedFileInterface)) {
                continue;
            }
            
            if ($file->getError() !== UPLOAD_ERR_OK) {
                continue;
            }
            
            $result = $this->processSingleAsset($file, $targetDir, $assetDir);
            if ($result) {
                $processedAssets[] = $result;
            }
        }
        
        return $processedAssets;
    }

    /**
     * Process a single asset file
     *
     * @param \Psr\Http\Message\UploadedFileInterface $file Uploaded file
     * @param string $targetDir Target directory path
     * @param string $assetDir Asset directory name
     * @return array|null Asset information or null on failure
     */
    private function processSingleAsset(UploadedFileInterface $file, string $targetDir, string $assetDir): ?array
    {
        $filename = $file->getClientFilename();
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Validate file type
        if (!in_array($extension, $this->getConfig('allowed_types'))) {
            return null;
        }
        
        // Validate file size
        if ($file->getSize() > $this->getConfig('max_file_size')) {
            return null;
        }
        
        // Generate safe filename
        $safeFilename = $this->generateSafeFilename($filename);
        $targetPath = $targetDir . DS . $safeFilename;
        
        try {
            // Move uploaded file
            $file->moveTo($targetPath);
            
            return [
                'original_name' => $filename,
                'filename' => $safeFilename,
                'type' => $extension,
                'size' => filesize($targetPath),
                'directory' => $assetDir,
                'path' => $this->getConfig('base_path') . $assetDir . '/' . $safeFilename,
                'web_path' => '/' . $this->getConfig('base_path') . $assetDir . '/' . $safeFilename,
                'mime_type' => $this->getMimeTypeFromExtension($extension),
                'created' => date('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            // Log error and return null
            return null;
        }
    }

    /**
     * Generate a safe filename
     *
     * @param string $filename Original filename
     * @return string Safe filename
     */
    private function generateSafeFilename(string $filename): string
    {
        $info = pathinfo($filename);
        $basename = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $info['filename']);
        $extension = strtolower($info['extension']);
        
        // Limit basename length and add timestamp for uniqueness
        $basename = substr($basename, 0, 50);
        $timestamp = time();
        
        return $basename . '_' . $timestamp . '.' . $extension;
    }

    /**
     * Get MIME type from file extension
     *
     * @param string $extension File extension
     * @return string MIME type
     */
    private function getMimeTypeFromExtension(string $extension): string
    {
        $mimeTypes = [
            'js' => 'application/javascript',
            'css' => 'text/css',
            'html' => 'text/html',
            'htm' => 'text/html'
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Clean up asset files when entity is deleted
     *
     * @param \Cake\Event\EventInterface $event The event object
     * @param \Cake\Datasource\EntityInterface $entity The entity being deleted
     * @param \ArrayObject $options Delete options
     * @return void
     */
    public function beforeDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (!empty($entity->asset_dir)) {
            $webroot = WWW_ROOT;
            $assetDir = $webroot . $this->getConfig('base_path') . $entity->asset_dir;
            
            if (is_dir($assetDir)) {
                $this->recursiveRemoveDirectory($assetDir);
            }
        }
    }

    /**
     * Recursively remove a directory and its contents
     *
     * @param string $dir Directory path
     * @return bool Success status
     */
    private function recursiveRemoveDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $filePath = $dir . DS . $file;
            if (is_dir($filePath)) {
                $this->recursiveRemoveDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }

        return rmdir($dir);
    }
}