<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Entity\Article;
use App\Model\Table\ArticlesTable;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;
use RuntimeException;

/**
 * GenerateArticles command.
 */
class GenerateArticlesCommand extends Command
{
    /**
     * @var string UUID of the admin user
     */
    private string $adminUserId;

    /**
     * @var \App\Model\Table\ArticlesTable
     */
    private ArticlesTable $Articles;

    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Articles = TableRegistry::getTableLocator()->get('Articles');
        $this->loadAdminUser();
    }

    /**
     * Build option parser method.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->addArgument('count', [
            'help' => __('Number of articles to generate'),
            'required' => true,
        ])->addOption('delete', [
            'help' => __('Delete all articles before generating new ones'),
            'boolean' => true,
        ]);

        return $parser;
    }

    /**
     * Execute the command
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        if ($args->getOption('delete')) {
            $this->Articles->deleteAll([]);
            $io->out(__('All articles have been deleted.'));
        }

        // Check and create top-level tags if needed
        $this->ensureTopLevelTags($io);

        $count = (int)$args->getArgument('count');
        $io->out(__('Generating {0} articles...', $count));

        $successCount = 0;
        $failCount = 0;

        for ($i = 0; $i < $count; $i++) {
            $article = $this->generateArticle();

            $publishedDate = $article->published;

            if ($this->Articles->save($article, ['associated' => ['Tags']])) {
                $article->published = $publishedDate;
                $this->Articles->save($article);
                $io->out(__('Generated article: {0}', $article->title));
                $successCount++;
            } else {
                $io->error(__('Failed to generate article: {0}', $article->title));
                $errors = $article->getErrors();
                foreach ($errors as $field => $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        $io->error(__('Error in {0}: {1}', $field, $error));
                    }
                }
                $failCount++;
            }
        }

        $io->success(__('Generated {0} articles successfully.', $successCount));
        if ($failCount > 0) {
            $io->warning(__('Failed to generate {0} articles.', $failCount));
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Ensures there are at least 10 top-level tags in the system
     *
     * @param \Cake\Console\ConsoleIo $io The console IO object
     * @return void
     */
    private function ensureTopLevelTags(ConsoleIo $io): void
    {
        $tagsTable = TableRegistry::getTableLocator()->get('Tags');

        // Count existing top-level tags
        $existingCount = $tagsTable->find()
            ->where(['parent_id IS' => null])
            ->count();

        if ($existingCount >= 10) {
            return;
        }

        $tagsToCreate = 10 - $existingCount;
        $io->out(__('Creating {0} new top-level tags...', $tagsToCreate));

        for ($i = 0; $i < $tagsToCreate; $i++) {
            $tag = $tagsTable->newEmptyEntity();

            // Generate a single word for name (max 10 characters)
            $tag->title = substr($this->generateRandomText(10), 0, 10);

            // Generate description (max 10 words)
            $tag->description = $this->generateRandomText(10, true);

            if ($tagsTable->save($tag)) {
                $io->out(__('Created tag: {0}', $tag->name));
            } else {
                $io->error(__('Failed to create tag: {0}', $tag->name));
                $errors = $tag->getErrors();
                foreach ($errors as $field => $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        $io->error(__('Error in {0}: {1}', $field, $error));
                    }
                }
            }
        }
    }

    /**
     * Generate a single article with random tags
     *
     * @return \App\Model\Entity\Article
     */
    private function generateArticle(): Article
    {
        // Generate shorter content to ensure it fits database constraints
        $title = $this->generateRandomText(100);
        $lede = $this->generateRandomText(200);
        $summary = $this->generateRandomText(50, true);
        $body = $this->generateRandomText(200, true);

        // Generate a random date between 2000 and now
        $year = rand(2000, (int)date('Y'));
        $month = str_pad((string)rand(1, 12), 2, '0', STR_PAD_LEFT);
        $day = str_pad((string)rand(1, 28), 2, '0', STR_PAD_LEFT);
        $hour = str_pad((string)rand(0, 23), 2, '0', STR_PAD_LEFT);
        $minute = str_pad((string)rand(0, 59), 2, '0', STR_PAD_LEFT);
        $second = str_pad((string)rand(0, 59), 2, '0', STR_PAD_LEFT);

        $publishedDate = new DateTime("{$year}-{$month}-{$day} {$hour}:{$minute}:{$second}");

        // Create new article entity
        $article = $this->Articles->newEmptyEntity();
        $article->title = $title;
        $article->lede = $lede;
        $article->summary = $summary;
        $article->body = $body;
        $article->slug = ''; // Will be auto-generated
        $article->user_id = $this->adminUserId;
        $article->kind = 'article';
        $article->is_published = true;
        $article->published = $publishedDate;

        // Get all available tags
        $tagsTable = TableRegistry::getTableLocator()->get('Tags');
        $allTags = $tagsTable->find()
            ->select(['id', 'title'])
            ->toArray();

        if (!empty($allTags)) {
            // Randomly select between 1 and 3 tags
            $numTags = min(rand(1, 3), count($allTags));
            $selectedIndices = array_rand($allTags, $numTags);

            // Convert to array if only one tag selected
            if (!is_array($selectedIndices)) {
                $selectedIndices = [$selectedIndices];
            }

            // Create array of tag entities
            $tags = [];
            foreach ($selectedIndices as $index) {
                $tags[] = $allTags[$index];
            }

            // Set the tags
            $article->tags = $tags;
        }

        return $article;
    }

    /**
     * Generate random text
     *
     * @param int $maxLength Maximum length of the text
     * @param bool $isWordCount Whether the length is in words
     * @return string Random text
     */
    private function generateRandomText(int $maxLength, bool $isWordCount = false): string
    {
        if ($isWordCount) {
            // Generate by word count
            $words = [];
            for ($i = 0; $i < $maxLength; $i++) {
                $wordLength = rand(3, 10);
                $word = '';
                for ($j = 0; $j < $wordLength; $j++) {
                    $word .= chr(rand(97, 122));
                }
                $words[] = $word;
            }

            return implode(' ', $words);
        }

        // Generate by character count
        $text = '';
        $currentLength = 0;

        while ($currentLength < $maxLength) {
            // Calculate remaining space
            $remainingSpace = $maxLength - $currentLength;

            // If we have very limited space left, just add a few characters
            if ($remainingSpace <= 4) {
                $text .= substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, $remainingSpace);
                break;
            }

            // Generate a word that will fit in remaining space (including a space character)
            $maxWordLength = min(10, $remainingSpace - 1);
            $wordLength = rand(3, $maxWordLength);
            $word = '';
            for ($j = 0; $j < $wordLength; $j++) {
                $word .= chr(rand(97, 122));
            }

            // Add word and space if it fits
            if (strlen($text . $word . ' ') <= $maxLength) {
                $text .= $word . ' ';
                $currentLength = strlen($text);
            } else {
                break;
            }
        }

        return trim($text);
    }

    /**
     * Load admin user ID
     *
     * @throws \RuntimeException When no admin user is found
     * @return void
     */
    private function loadAdminUser(): void
    {
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $adminUser = $usersTable->find()
            ->select(['id'])
            ->where(['is_admin' => true])
            ->first();

        if ($adminUser) {
            $this->adminUserId = $adminUser->id;
        } else {
            throw new RuntimeException(__('No admin user found.'));
        }
    }
}
