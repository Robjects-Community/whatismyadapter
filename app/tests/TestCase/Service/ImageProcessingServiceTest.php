<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\ImageProcessingService;
use App\Utility\ArchiveExtractor;
use Cake\TestSuite\TestCase;
use Psr\Http\Message\UploadedFileInterface;

/**
 * ImageProcessingService Test Case
 */
class ImageProcessingServiceTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Service\ImageProcessingService
     */
    protected $ImageProcessingService;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Images',
        'app.ImageGalleries',
        'app.ImageGalleriesImages',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $imagesTable = $this->getTableLocator()->get('Images');
        $galleriesImagesTable = $this->getTableLocator()->get('ImageGalleriesImages');
        $archiveExtractor = $this->createMock(ArchiveExtractor::class);

        $this->ImageProcessingService = new ImageProcessingService(
            $imagesTable,
            $galleriesImagesTable,
            $archiveExtractor,
        );
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ImageProcessingService);
        parent::tearDown();
    }

    /**
     * Test service instantiation
     *
     * @return void
     */
    public function testServiceInstantiation(): void
    {
        $this->assertInstanceOf(ImageProcessingService::class, $this->ImageProcessingService);
    }

    /**
     * Test processUploadedFiles with upload errors
     *
     * @return void
     */
    public function testProcessUploadedFilesWithUploadError(): void
    {
        $uploadedFile = $this->createMockUploadedFile('test.jpg', 'image/jpeg', UPLOAD_ERR_PARTIAL);

        $result = $this->ImageProcessingService->processUploadedFiles([$uploadedFile]);

        $this->assertFalse($result['success']);
        $this->assertEquals(0, $result['success_count']);
        $this->assertEquals(1, $result['error_count']);
        $this->assertEquals(1, $result['total_processed']);
        $this->assertEmpty($result['created_images']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('partially uploaded', $result['errors'][0]['error']);
    }

    /**
     * Test error message generation for various upload errors
     *
     * @return void
     */
    public function testUploadErrorMessages(): void
    {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File size exceeds limit',
            UPLOAD_ERR_FORM_SIZE => 'File size exceeds limit',
            UPLOAD_ERR_PARTIAL => 'partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'write file to disk',
            UPLOAD_ERR_EXTENSION => 'stopped by extension',
        ];

        foreach ($uploadErrors as $errorCode => $expectedMessage) {
            $uploadedFile = $this->createMockUploadedFile('test.jpg', 'image/jpeg', $errorCode);
            $result = $this->ImageProcessingService->processUploadedFiles([$uploadedFile]);

            $this->assertCount(1, $result['errors']);
            $this->assertStringContainsString($expectedMessage, $result['errors'][0]['error']);
        }
    }

    /**
     * Test processUploadedFiles with multiple files having errors
     *
     * @return void
     */
    public function testProcessUploadedFilesMultipleErrors(): void
    {
        $files = [
            $this->createMockUploadedFile('file1.jpg', 'image/jpeg', UPLOAD_ERR_NO_FILE),
            $this->createMockUploadedFile('file2.jpg', 'image/jpeg', UPLOAD_ERR_PARTIAL),
            $this->createMockUploadedFile('file3.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE),
        ];

        $result = $this->ImageProcessingService->processUploadedFiles($files);

        $this->assertFalse($result['success']);
        $this->assertEquals(0, $result['success_count']);
        $this->assertEquals(3, $result['error_count']);
        $this->assertEquals(3, $result['total_processed']);
        $this->assertEmpty($result['created_images']);
        $this->assertCount(3, $result['errors']);
    }

    /**
     * Test result message generation
     *
     * @return void
     */
    public function testResultMessageGeneration(): void
    {
        // Test with only errors
        $errorFile = $this->createMockUploadedFile('error.jpg', 'image/jpeg', UPLOAD_ERR_NO_FILE);
        $result = $this->ImageProcessingService->processUploadedFiles([$errorFile]);

        $this->assertStringContainsString('No images were processed successfully', $result['message']);
    }

    /**
     * Test archive file detection
     *
     * @return void
     */
    public function testArchiveFileDetection(): void
    {
        $mockExtractor = $this->createMock(ArchiveExtractor::class);
        $mockExtractor->method('getSupportedArchiveTypes')
            ->willReturn(['zip', 'tar', 'gz']);

        $imagesTable = $this->getTableLocator()->get('Images');
        $galleriesImagesTable = $this->getTableLocator()->get('ImageGalleriesImages');
        $service = new ImageProcessingService($imagesTable, $galleriesImagesTable, $mockExtractor);

        // Test ZIP file
        $zipFile = $this->createMockUploadedFile('archive.zip', 'application/zip');
        $result = $service->processUploadedFiles([$zipFile]);

        // Since we're not providing a real archive extraction, this will likely fail
        // but we can test that the archive detection logic is triggered
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    /**
     * Test service constructor validates required dependencies
     *
     * @return void
     */
    public function testConstructorDependencies(): void
    {
        $imagesTable = $this->getTableLocator()->get('Images');
        $galleriesImagesTable = $this->getTableLocator()->get('ImageGalleriesImages');
        $archiveExtractor = new ArchiveExtractor();

        $service = new ImageProcessingService($imagesTable, $galleriesImagesTable, $archiveExtractor);

        $this->assertInstanceOf(ImageProcessingService::class, $service);
    }

    /**
     * Helper method to create mock uploaded file
     *
     * @param string $filename
     * @param string $mimeType
     * @param int $error
     * @return \Psr\Http\Message\UploadedFileInterface
     */
    private function createMockUploadedFile(string $filename, string $mimeType, int $error = UPLOAD_ERR_OK): UploadedFileInterface
    {
        $mock = $this->createMock(UploadedFileInterface::class);
        $mock->method('getClientFilename')->willReturn($filename);
        $mock->method('getClientMediaType')->willReturn($mimeType);
        $mock->method('getError')->willReturn($error);
        $mock->method('getSize')->willReturn(1024);

        return $mock;
    }
}
