<?php
/**
 * Choose New Page Type Template
 * 
 * This template displays a selection interface for choosing between
 * creating a custom link page or a new page with asset uploads.
 * 
 * @var \App\View\AppView $this
 */
?>
<div class="page-type-selector">
    <div class="container">
        <h2 class="text-center mb-4">Choose New Page Type</h2>
        <p class="text-center text-muted mb-5">Select how you want to create your new page</p>
        
        <div class="row justify-content-center">
            <!-- Link Custom Page Option -->
            <div class="col-md-5">
                <div class="page-type-card" 
                     role="button" 
                     tabindex="0" 
                     data-type="link"
                     data-url="<?= $this->Url->build(['action' => 'add', '?' => ['page_type' => 'link']]) ?>"
                     aria-label="Link to an external custom page not controlled by CakePHP">
                    <div class="card-icon">
                        <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                    </div>
                    <h3>Link Custom Page</h3>
                    <p class="card-description">
                        Link to an external page or resource that exists outside of this CMS.
                        Perfect for linking to custom applications, external sites, or static content.
                    </p>
                    <div class="card-features">
                        <ul>
                            <li><i class="fas fa-check text-success" aria-hidden="true"></i> External URL linking</li>
                            <li><i class="fas fa-check text-success" aria-hidden="true"></i> Quick setup</li>
                            <li><i class="fas fa-check text-success" aria-hidden="true"></i> Menu integration</li>
                        </ul>
                    </div>
                    <div class="card-action">
                        <span class="btn btn-outline-primary btn-block">
                            Select Link Page
                            <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Create New Page Option -->
            <div class="col-md-5">
                <div class="page-type-card" 
                     role="button" 
                     tabindex="0" 
                     data-type="create"
                     data-url="<?= $this->Url->build(['action' => 'add', '?' => ['page_type' => 'standard']]) ?>"
                     aria-label="Create a new page with file upload capabilities for animations and assets">
                    <div class="card-icon">
                        <i class="fas fa-plus-circle" aria-hidden="true"></i>
                    </div>
                    <h3>Create New Page</h3>
                    <p class="card-description">
                        Build a new page with full content management capabilities.
                        Upload JS, CSS, and HTML files for custom animations and interactive content.
                    </p>
                    <div class="card-features">
                        <ul>
                            <li><i class="fas fa-check text-success" aria-hidden="true"></i> File uploads (JS, CSS, HTML)</li>
                            <li><i class="fas fa-check text-success" aria-hidden="true"></i> Animation support</li>
                            <li><i class="fas fa-check text-success" aria-hidden="true"></i> Full content editor</li>
                        </ul>
                    </div>
                    <div class="card-action">
                        <span class="btn btn-primary btn-block">
                            Create New Page
                            <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center mt-4">
            <div class="col-auto">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Cancel page creation">
                    <i class="fas fa-times mr-2" aria-hidden="true"></i>
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<?php $this->start('scriptBottom'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.page-type-card');
    
    // Add hover and focus effects
    cards.forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            this.classList.add('card-hover');
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('card-hover');
        });
        
        card.addEventListener('focus', function() {
            this.classList.add('card-focus');
        });
        
        card.addEventListener('blur', function() {
            this.classList.remove('card-focus');
        });
        
        // Handle clicks and keyboard navigation
        card.addEventListener('click', function() {
            handleCardSelection(this);
        });
        
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                handleCardSelection(this);
            }
        });
    });
    
    function handleCardSelection(card) {
        const url = card.dataset.url;
        const type = card.dataset.type;
        
        // Add selection animation
        card.classList.add('card-selected');
        
        // Redirect after a short delay for animation
        setTimeout(function() {
            if (typeof window.parent !== 'undefined' && window.parent !== window) {
                // If in modal, close modal and navigate parent
                if (window.parent.jQuery && window.parent.jQuery('.modal').length) {
                    window.parent.jQuery('.modal').modal('hide');
                    window.parent.location.href = url;
                } else {
                    window.parent.location.href = url;
                }
            } else {
                // Direct navigation
                window.location.href = url;
            }
        }, 300);
    }
});
</script>
<?php $this->end(); ?>

<style>
.page-type-selector {
    padding: 2rem 1rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 500px;
}

.page-type-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    height: 400px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}

.page-type-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.6s ease;
}

.page-type-card:hover::before,
.page-type-card.card-hover::before {
    left: 100%;
}

.page-type-card:hover,
.page-type-card.card-hover,
.page-type-card:focus,
.page-type-card.card-focus {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    border-color: #007bff;
}

.page-type-card.card-selected {
    transform: scale(0.98);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    background: #f8f9fa;
}

.card-icon {
    font-size: 3rem;
    color: #007bff;
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
}

.page-type-card:hover .card-icon,
.page-type-card.card-hover .card-icon {
    transform: scale(1.1);
    color: #0056b3;
}

.page-type-card h3 {
    color: #333;
    font-weight: 600;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.card-description {
    color: #666;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    flex-grow: 1;
}

.card-features {
    margin-bottom: 1.5rem;
}

.card-features ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.card-features li {
    padding: 0.25rem 0;
    font-size: 0.9rem;
    color: #555;
}

.card-features .fas {
    width: 16px;
    margin-right: 0.5rem;
}

.card-action .btn {
    transition: all 0.3s ease;
    font-weight: 600;
}

.page-type-card:hover .btn-outline-primary,
.page-type-card.card-hover .btn-outline-primary {
    background-color: #007bff;
    color: white;
}

.page-type-card:hover .btn-primary,
.page-type-card.card-hover .btn-primary {
    background-color: #0056b3;
    border-color: #0056b3;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .page-type-selector {
        padding: 1rem;
    }
    
    .page-type-card {
        margin-bottom: 2rem;
        height: auto;
        min-height: 350px;
    }
    
    .card-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }
    
    .page-type-card h3 {
        font-size: 1.25rem;
    }
}

/* Accessibility improvements */
.page-type-card:focus {
    outline: 3px solid #80bdff;
    outline-offset: 2px;
}

/* Animation for loading states */
.page-type-card.loading {
    pointer-events: none;
    opacity: 0.7;
}

.page-type-card.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 24px;
    height: 24px;
    margin: -12px 0 0 -12px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>