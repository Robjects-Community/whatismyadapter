<?php
require_once 'vendor/autoload.php';
require_once 'config/bootstrap.php';

use App\Job\ArticleImageGenerationJob;
use Cake\Queue\Job\Message;
use Cake\ORM\TableRegistry;

echo "Testing AI Image Generation...\n";

// Get our test article
$articlesTable = TableRegistry::getTableLocator()->get('Articles');
$article = $articlesTable->find()
    ->where(['title LIKE' => '%Renewable Energy%'])
    ->first();

if (!$article) {
    echo "Test article not found!\n";
    exit(1);
}

echo "Found article: {$article->title} (ID: {$article->id})\n";
echo "Current image: " . ($article->image ?: 'None') . "\n\n";

// Create a mock message for the job
$messageData = [
    'id' => $article->id,
    'title' => $article->title,
    'regenerate' => false
];

$message = new class($messageData) extends Message {
    private array $arguments;
    
    public function __construct(array $arguments) {
        $this->arguments = $arguments;
    }
    
    public function getArgument(mixed $key = null, mixed $default = null): mixed {
        if ($key === null) {
            return $this->arguments;
        }
        return $this->arguments[$key] ?? $default;
    }
    
    public function getArguments(): array {
        return $this->arguments;
    }
    
    public function getBody(): string {
        return json_encode($this->arguments);
    }
    
    public function getId(): ?string {
        return $this->arguments['id'] ?? null;
    }
};

// Run the job
echo "Starting image generation job...\n";
$job = new ArticleImageGenerationJob();

try {
    $result = $job->execute($message);
    echo "Job result: " . ($result ?: 'null') . "\n\n";
    
    // Check if image was generated
    $updatedArticle = $articlesTable->get($article->id);
    echo "Updated article image: " . ($updatedArticle->image ?: 'None') . "\n";
    
    if ($updatedArticle->image) {
        $imagePath = WWW_ROOT . 'files/Articles/image/' . $updatedArticle->image;
        if (file_exists($imagePath)) {
            echo "Image file exists: {$imagePath}\n";
            echo "Image size: " . filesize($imagePath) . " bytes\n";
        } else {
            echo "Image file not found at: {$imagePath}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error running job: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}