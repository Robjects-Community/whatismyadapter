<?php
declare(strict_types=1);

namespace App\Command;

use App\Utility\SettingsManager;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Log\LogTrait;
use Exception;
use Imagick;

/**
 * ResizeImages command.
 *
 * This command is responsible for resizing images for specified models.
 */
class ResizeImagesCommand extends Command
{
    use LogTrait;

    /**
     * Stores the model names and their respective columns to process.
     *
     * @var array<string, string>
     */
    protected array $modelsWithImages = [
        'Users' => [
            'file' => 'image',
            'dir' => 'dir',
            'size' => 'size',
            'type' => 'mime',
        ],
        'Images' => [
            'file' => 'image',
            'dir' => 'dir',
            'size' => 'size',
            'type' => 'mime',
        ],
        'Articles' => [
            'file' => 'image',
            'dir' => 'dir',
            'size' => 'size',
            'type' => 'mime',
        ],
        'Tags' => [
            'file' => 'image',
            'dir' => 'dir',
            'size' => 'size',
            'type' => 'mime',
        ],
    ];

    /**
     * Stores the ConsoleIo instance for output operations.
     *
     * @var \Cake\Console\ConsoleIo
     */
    protected ConsoleIo $io;

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        return $parser;
    }

    /**
     * Executes the command to resize images.
     *
     * This method iterates through the specified models, retrieves images,
     * and resizes them according to the configured sizes.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int The exit code of the command.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        //save reference for IO
        $this->io = $io;
        //for our models that have images, get their table
        foreach ($this->modelsWithImages as $model => $columns) {
            $imagesTable = $this->fetchTable($model);
            $images = $imagesTable->find('all')
            ->select(['id', $columns['image'], $columns['dir']])
            ->where([$columns['image'] . ' IS NOT' => null])
            ->toArray();

            foreach ($images as $image) {
                $folder = ROOT . DS . $image->dir;
                $original = $folder . $image->{$columns['image']};
                if (file_exists($original)) {
                    foreach (SettingsManager::read('ImageSizes') as $width) {
                        $this->createImage($folder, $image->{$columns['image']}, intval($width));
                    }
                }
            }
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Creates a resized image.
     *
     * This method resizes the given image to the specified width and saves it
     * in the appropriate directory.
     *
     * @param string $folder The directory where the original image is stored.
     * @param string $file The name of the image file.
     * @param int $width The target width for the resized image.
     * @throws \Exception If the directory cannot be created.
     * @return void
     */
    private function createImage(string $folder, string $file, int $width): void
    {
        // Make sure folder for size exists
        // Ensure the folder path ends with a directory separator
        $folder = rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // Create the full path including the width
        $sizeFolder = $folder . $width . DIRECTORY_SEPARATOR;

        // Check if the directory exists, if not, create it
        if (!is_dir($sizeFolder)) {
            if (!mkdir($sizeFolder, 0755, true)) {
                throw new Exception("Failed to create directory: $sizeFolder");
            }
        }

        try {
            if (!file_exists($folder . $file)) {
                $this->log(
                    sprintf('Original image not found for resizing. Path: %s', $folder . $file),
                    'error',
                    ['group_name' => 'image_processing'],
                );

                return;
            }

            if (file_exists($sizeFolder . $file)) {
                $this->log(
                    sprintf(
                        'Skipped resizing, image already exists. Path: %s',
                        $sizeFolder . $file,
                    ),
                    'info',
                    ['group_name' => 'image_processing'],
                );

                return;
            }

            $imagick = new Imagick($folder . $file);
            $imagick->resizeImage($width, 0, Imagick::FILTER_LANCZOS, 1);
            $imagick->writeImage($sizeFolder . $file);
            $imagick->clear();

            $this->log(
                sprintf(
                    'Successfully resized and saved image. Original: %s, Resized: %s, Width: %dpx',
                    $folder . $file,
                    $sizeFolder . $file,
                    $width,
                ),
                'info',
                ['group_name' => 'image_processing'],
            );
        } catch (Exception $e) {
            $this->log(
                sprintf(
                    'Error resizing image. Original: %s, Target Width: %dpx, Error: %s',
                    $folder . $file,
                    $width,
                    $e->getMessage(),
                ),
                'error',
                ['group_name' => 'image_processing'],
            );
        }
    }
}
