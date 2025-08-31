/**
 * Main editor initialization and handlers
 * This file handles the Trumbowyg editor setup, image/video insertion, and code highlighting
 */
$(document).ready(function() {
    // --- Constants ---
    // URLs (Note: Ideally, these should be passed from server-side PHP if changes were allowed)
    const URLS = {
        IMAGE_SELECT: '/admin/images/image_select',
        VIDEO_SELECT: '/admin/videos/video_select',
        IMAGE_BASE_PATH: '/files/Images/image/' // Example: /files/Images/image/{size}/{filename}
    };

    // DOM Selectors
    const SELECTORS = {
        // Editor
        ARTICLE_BODY: '#article-body',
        // Image Modal & Gallery related IDs/Selectors
        IMAGE_MODAL: '#imageSelectModal',
        IMAGE_MODAL_WINDOW_ID: 'selectImageWindow', // ID for the modal body that receives AJAX content
        IMAGE_GALLERY_CONTAINER: '#image-gallery', // Actual container of images within the AJAX response
        IMAGE_SEARCH_INPUT: '#imageSearch',       // Search input within AJAX response
        // Video Modal & Gallery related IDs/Selectors
        VIDEO_MODAL: '#videoSelectModal',
        VIDEO_MODAL_WINDOW_ID: 'selectVideoWindow', // ID for the modal body that receives AJAX content
        VIDEO_GALLERY_CONTAINER: '#video-gallery', // Actual container of videos within the AJAX response
        VIDEO_SEARCH_INPUT: '#videoSearch',       // Search input within AJAX response
        VIDEO_CHANNEL_FILTER: '#channelFilter',   // Channel filter checkbox within AJAX response
        // General
        PAGINATION_LINKS: '.pagination a', // Common selector for pagination links
        TRUMBOWYG_ICON_CAMERA_REELS: 'camera-reels', // Custom Trumbowyg icon name
        TRUMBOWYG_ICON_CODE_INSERT: 'code-insert'    // Custom Trumbowyg icon name
    };
    // --- End Constants ---

    /**
     * Safely escapes HTML content to prevent XSS attacks.
     * @param {*} unsafe - The unsafe string or value to escape.
     * @returns {string} The escaped string, or an empty string if input is not a string.
     */
    function escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') {
            // console.warn('escapeHtml called with non-string value:', unsafe);
            return ''; // Return empty string for non-string inputs
        }
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    /**
     * Safely highlights code blocks using highlight.js.
     * The :not(.hljs) selector in querySelectorAll prevents re-highlighting.
     */
    function safeHighlight() {
        document.querySelectorAll('pre code:not(.hljs)').forEach(block => {
            hljs.highlightElement(block);
        });
    }

    // Initialize Highlight.js on page load and Trumbowyg events
    safeHighlight();
    $(SELECTORS.ARTICLE_BODY).on('tbwchange tbwinit', function() {
        safeHighlight();
    });

    /**
     * Debounce function to limit the rate at which a function can fire.
     * @param {function} func - The function to debounce.
     * @param {number} delay - The delay in milliseconds.
     * @returns {function} - The debounced function.
     */
    function debounce(func, delay) {
        let debounceTimer;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => func.apply(context, args), delay);
        };
    }

    // --- Trumbowyg Custom Plugins ----
    $.extend(true, $.trumbowyg, {
        plugins: {
            insertImageFromLibrary: {
                init: function(trumbowyg) {
                    trumbowyg.o.plugins.insertImageFromLibrary = trumbowyg.o.plugins.insertImageFromLibrary || {};
                    trumbowyg.addBtnDef('insertImageFromLibrary', {
                        fn: function() {
                            trumbowyg.saveRange();
                            
                            // Use brilliant enhanced WillowModal directly
                            WillowModal.showImageSelector(trumbowyg, {
                                title: 'Insert Image from Library'
                            });
                        },
                        title: 'Insert Image from Library',
                        ico: 'insertImage' // Standard Trumbowyg icon
                    });
                }
            },

            insertVideoFromLibrary: {
                init: function(trumbowyg) {
                    trumbowyg.o.plugins.insertVideoFromLibrary = trumbowyg.o.plugins.insertVideoFromLibrary || {};
                    trumbowyg.addBtnDef('insertVideoFromLibrary', {
                        fn: function() {
                            trumbowyg.saveRange();
                            
                            // Use brilliant enhanced WillowModal directly
                            WillowModal.showVideoSelector(trumbowyg, {
                                title: 'Insert YouTube Video'
                            });
                        },
                        title: 'Insert Video from Library',
                        ico: SELECTORS.TRUMBOWYG_ICON_CAMERA_REELS // Custom icon name
                    });
                }
            },

            insertGalleryFromLibrary: {
                init: function(trumbowyg) {
                    trumbowyg.o.plugins.insertGalleryFromLibrary = trumbowyg.o.plugins.insertGalleryFromLibrary || {};
                    trumbowyg.addBtnDef('insertGalleryFromLibrary', {
                        fn: function() {
                            trumbowyg.saveRange();
                            
                            // Use brilliant enhanced WillowModal directly
                            WillowModal.showGallerySelector(trumbowyg, {
                                title: 'Insert Image Gallery'
                            });
                        },
                        title: 'Insert Image Gallery',
                        ico: 'gallery' // Custom gallery icon
                    });
                }
            },

            highlight: {
                init: function(trumbowyg) {
                    trumbowyg.o.plugins.highlight = trumbowyg.o.plugins.highlight || {};
                    trumbowyg.addBtnDef('highlight', {
                        fn: function() {
                            trumbowyg.saveRange();
                            
                            const highlightModalHtmlContent = `
                                <div class="form-group mb-3">
                                    <label for="code-language">Language</label>
                                    <select class="form-select" id="code-language">
                                        <option value="php">PHP</option>
                                        <option value="javascript">JavaScript</option>
                                        <option value="css">CSS</option>
                                        <option value="html">HTML</option>
                                        <option value="sql">SQL</option>
                                        <option value="bash">Bash</option>
                                        <option value="json">JSON</option>
                                        <option value="xml">XML</option>
                                        <option value="plaintext">Plain Text</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="code-content">Code</label>
                                    <textarea class="form-control" id="code-content" rows="10"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" id="insertCode">Insert</button>
                                </div>
                            `;

                            // Use WillowModal for consistent modal handling
                            WillowModal.showStatic(highlightModalHtmlContent, {
                                title: 'Insert Code Snippet',
                                dialogClass: 'modal-dialog',
                                closeable: true,
                                onContentLoaded: function() {
                                    const modal = document.getElementById('dynamicModal');
                                    const insertButton = modal.querySelector('#insertCode');
                                    
                                    if (insertButton && insertButton.dataset[WillowModalConfig.events.datasetMarker] !== 'true') {
                                        insertButton.addEventListener('click', function() {
                                            const languageSelect = modal.querySelector('#code-language');
                                            const codeTextarea = modal.querySelector('#code-content');
                                            
                                            if (languageSelect && codeTextarea) {
                                                const language = escapeHtml(languageSelect.value);
                                                const rawCode = codeTextarea.value;
                                                const escapedCodeForHtml = escapeHtml(rawCode);
                                                
                                                const htmlToInsert = `<pre><code class="language-${language}">${escapedCodeForHtml}</code></pre>`;
                                                
                                                trumbowyg.restoreRange();
                                                trumbowyg.execCmd('insertHTML', htmlToInsert, false, true);
                                                
                                                setTimeout(safeHighlight, 50);
                                                
                                                const modalInstance = bootstrap.Modal.getInstance(modal);
                                                if (modalInstance) modalInstance.hide();
                                            }
                                        });
                                        
                                        insertButton.dataset[WillowModalConfig.events.datasetMarker] = 'true';
                                    }
                                }
                            });
                        },
                        title: 'Insert Code Snippet',
                        ico: SELECTORS.TRUMBOWYG_ICON_CODE_INSERT // Custom icon name
                    });
                }
            }
        }
    });

    // Initialize Trumbowyg editor if the target element exists
    const $articleBody = $(SELECTORS.ARTICLE_BODY);
    if ($articleBody.length) {
        $articleBody.trumbowyg({
            btns: [
                ['viewHTML'],
                ['formatting'],
                ['textFormat'], // Custom definition
                ['link'],
                ['insertImageFromLibrary', 'insertVideoFromLibrary', 'insertGalleryFromLibrary', 'highlight'],
                ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                ['unorderedList', 'orderedList'],
                ['table'],
                ['tableCellBackgroundColor', 'tableBorderColor'], // Requires 'colors' plugin
                ['removeformat'],
                ['fullscreen']
            ],
            btnsDef: {
                textFormat: {
                    dropdown: ['bold', 'italic', 'underline', 'strikethrough', 'preformatted', 'superscript', 'subscript'],
                    ico: 'strong'
                }
            },
            plugins: {
                // Explicitly list plugins being used.
                // Options can be passed here if needed, e.g., colors: { colorList: [...] }
                insertImageFromLibrary: {},
                insertVideoFromLibrary: {},
                insertGalleryFromLibrary: {},
                highlight: {},
                table: {},       // For table creation
                colors: {},      // For text and table cell colors
                preformatted: {} // For the 'preformatted' option in textFormat dropdown
            },
            // Phase 3: Enhanced Trumbowyg configuration for better content alignment
            semantic: true,  // Use semantic HTML elements
            resetCss: false, // Don't reset CSS - preserve our alignment styles
            removeformatPasted: false, // Preserve formatting when pasting
            autogrow: true,
            autogrowOnEnter: true,
            minHeight: 400
        });
        // safeHighlight is called on 'tbwinit' and 'tbwchange', so no explicit call needed here after init.
    } else {
        // console.warn(`Trumbowyg target element "${SELECTORS.ARTICLE_BODY}" not found on this page.`);
    }
});