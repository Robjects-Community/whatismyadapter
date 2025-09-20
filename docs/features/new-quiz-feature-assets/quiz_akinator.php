<?php
/**
 * Akinator Quiz Template
 * @var \App\View\AppView $this
 * @var string $title
 * @var string $quizType
 * @var array $questions
 */
?>

<div class="akinator-quiz-container">
    <!-- Quiz Header -->
    <div class="akinator-header">
        <div class="genie-lamp">
            <div class="lamp-glow"></div>
            🧞‍♂️
        </div>
        <h1><?= h($title) ?></h1>
        <p class="akinator-subtitle">Think of your device and I'll guess the adapter you need!</p>
    </div>

    <!-- Progress & Confidence Meters -->
    <div class="quiz-meters">
        <div class="progress-container">
            <div class="progress-label">Progress</div>
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
            <span class="progress-text" id="progress-text">0%</span>
        </div>

        <div class="confidence-container">
            <div class="confidence-label">AI Confidence</div>
            <div class="confidence-bar">
                <div class="confidence-fill" id="confidence-fill"></div>
            </div>
            <span class="confidence-text" id="confidence-text">0%</span>
        </div>
    </div>

    <!-- Main Quiz Interface -->
    <div class="akinator-main" id="quiz-main">

        <!-- Question Display -->
        <div class="question-container" id="question-container">
            <div class="thinking-bubble" id="thinking-bubble" style="display: none;">
                <div class="thinking-animation">
                    <span>🤔</span>
                    <div class="thinking-dots">
                        <span>.</span><span>.</span><span>.</span>
                    </div>
                </div>
                <p id="thinking-text">I'm thinking...</p>
            </div>

            <div class="question-display" id="question-display">
                <div class="question-number" id="question-number">Question 1</div>
                <h2 class="question-text" id="question-text">
                    Is your device something you can hold in one hand?
                </h2>

                <!-- Binary Answer Options -->
                <div class="answer-options" id="answer-options">
                    <button class="answer-btn yes-btn" id="yes-btn" data-answer="yes">
                        <span class="answer-icon">✅</span>
                        <span class="answer-text">Yes</span>
                    </button>

                    <button class="answer-btn no-btn" id="no-btn" data-answer="no">
                        <span class="answer-icon">❌</span>
                        <span class="answer-text">No</span>
                    </button>
                </div>

                <!-- Answer History -->
                <div class="answer-history" id="answer-history">
                    <div class="history-label">Your answers:</div>
                    <div class="history-items" id="history-items"></div>
                </div>
            </div>
        </div>

        <!-- Quiz Controls -->
        <div class="quiz-controls">
            <button class="control-btn back-btn" id="back-btn" style="display: none;">
                ⬅️ Go Back
            </button>

            <button class="control-btn restart-btn" id="restart-btn" style="display: none;">
                🔄 Start Over
            </button>
        </div>
    </div>

    <!-- Results Display -->
    <div class="akinator-results" id="quiz-results" style="display: none;">
        <div class="results-header">
            <div class="genie-success">🎉</div>
            <h2>I found your perfect match!</h2>
            <div class="final-confidence">
                <span>AI Confidence: </span>
                <strong id="final-confidence-score">0%</strong>
            </div>
        </div>

        <div class="results-content" id="results-content">
            <!-- Results will be populated via JavaScript -->
        </div>

        <div class="results-actions">
            <button class="btn btn-primary" id="try-again-btn">
                🔄 Try Another Device
            </button>
            <button class="btn btn-secondary" id="view-all-btn">
                📋 View All Results
            </button>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="genie-thinking">
                <div class="lamp-spinning">🧞‍♂️</div>
            </div>
            <h3>The genie is analyzing...</h3>
            <div class="loading-dots">
                <span>.</span><span>.</span><span>.</span>
            </div>
        </div>
    </div>
</div>

<?= $this->Html->css(['quiz/quiz-akinator', 'quiz/quiz-animations']) ?>
<?= $this->Html->script('quiz/akinator-quiz') ?>

<script>
// Initialize Akinator Quiz
document.addEventListener('DOMContentLoaded', function() {
    const quiz = new AkinatorQuiz({
        apiEndpoint: '<?= $this->Url->build(['controller' => 'Api/Quiz', 'action' => 'nextQuestion', '_ext' => 'json']) ?>',
        submitEndpoint: '<?= $this->Url->build(['controller' => 'Api/Quiz', 'action' => 'submit', '_ext' => 'json']) ?>',
        questions: <?= json_encode($questions) ?>,
        quizType: '<?= $quizType ?>',
        sessionId: '<?= $this->request->getSession()->id() ?>'
    });

    quiz.start();
});
</script>