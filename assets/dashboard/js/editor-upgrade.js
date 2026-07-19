/**
 * RuangAiTi Editor Upgrade Javascript
 * Handles: full toolbar integration, markdown copy-paste, code block insertion,
 *          image properties sidebar, word stats, focus mode, auto-save drafts.
 */
(function() {
    'use strict';

    // Global variables
    let $activeImage = null;
    let autoSaveInterval = null;
    let draftKey = 'ruangaiti_post_draft_' + (window.location.pathname.split('/').pop() || 'new');

    document.addEventListener('DOMContentLoaded', function() {
        initEditorUpgrade();
        initFrontendFeatures();
    });

    /**
     * Bootstraps the editor upgrades
     */
    function initEditorUpgrade() {
        const editor = document.getElementById('content');
        if (!editor) return;

        // Load marked.js for Markdown paste support if not already loaded
        if (typeof marked === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/marked/marked.min.js';
            document.head.appendChild(script);
        }

        // Initialize Stats Bar DOM
        createStatsBar(editor);

        // Initialize Image Sidebar DOM
        createImageSidebar();

        // Bind image click listener inside the editor
        $(editor).on('summernote.init', function() {
            const editable = $(editor).summernote('codeview.isActivated') 
                ? null 
                : $(editor).next().find('.note-editable');
            
            if (editable) {
                // Monitor clicks inside editor editable area
                editable.on('click', function(e) {
                    const target = e.target;
                    if (target.tagName === 'IMG') {
                        selectImage($(target));
                    } else {
                        deselectImage();
                    }
                });

                // Monitor keyup/change for character count updates
                editable.on('keyup input paste', function() {
                    updateWordCount(editor);
                });

                // Monitor paste event to intercept Markdown
                editable.on('paste', function(e) {
                    handlePasteEvent(e, editor);
                });
            }

            // Load localStorage Draft toast if exists
            checkSavedDraft(editor);

            // Start Auto Save
            startAutoSave(editor);
        });

        // Listen for message from Media Manager Popup window
        window.addEventListener('message', function(e) {
            if (e.data && e.data.type === 'insert-image') {
                insertImageFromPicker(editor, e.data);
            }
        });
    }

    /**
     * Installs frontend features (copy code, lightbox, responsive wrapper)
     */
    function initFrontendFeatures() {
        // Lightbox feature
        const lightboxOverlay = document.createElement('div');
        lightboxOverlay.className = 'editor-lightbox-overlay';
        lightboxOverlay.innerHTML = '<img class="editor-lightbox-img" src="" alt="Preview">';
        document.body.appendChild(lightboxOverlay);

        lightboxOverlay.addEventListener('click', function() {
            lightboxOverlay.classList.remove('active');
        });

        document.querySelectorAll('img[data-lightbox="true"], .post-content img').forEach(img => {
            img.style.cursor = 'zoom-in';
            img.addEventListener('click', function(e) {
                // Don't trigger on editor clicks
                if (img.closest('.note-editor')) return;
                
                lightboxOverlay.querySelector('.editor-lightbox-img').src = img.src;
                lightboxOverlay.querySelector('.editor-lightbox-img').alt = img.alt || '';
                lightboxOverlay.classList.add('active');
                e.preventDefault();
            });
        });

        // Code block copy buttons & language badges
        document.querySelectorAll('pre code').forEach(code => {
            const pre = code.parentNode;
            if (pre.parentNode.classList.contains('code-block-wrapper')) return;

            // Wrap pre in a container
            const wrapper = document.createElement('div');
            wrapper.className = 'code-block-wrapper';
            pre.parentNode.insertBefore(wrapper, pre);
            wrapper.appendChild(pre);

            // Add Copy Button
            const copyBtn = document.createElement('button');
            copyBtn.className = 'code-copy-btn';
            copyBtn.type = 'button';
            copyBtn.innerHTML = '<i class="fas fa-copy mr-1"></i>Copy';
            copyBtn.addEventListener('click', function() {
                const text = code.innerText;
                navigator.clipboard.writeText(text).then(() => {
                    copyBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Copied!';
                    copyBtn.style.background = '#10b981';
                    setTimeout(() => {
                        copyBtn.innerHTML = '<i class="fas fa-copy mr-1"></i>Copy';
                        copyBtn.style.background = '';
                    }, 2000);
                });
            });
            wrapper.appendChild(copyBtn);

            // Add Language badge
            const classes = code.className.split(' ');
            const langClass = classes.find(c => c.startsWith('language-'));
            if (langClass) {
                const lang = langClass.replace('language-', '');
                const badge = document.createElement('span');
                badge.className = 'code-lang-badge';
                badge.textContent = lang;
                wrapper.appendChild(badge);
            }
        });
    }

    /**
     * Intercept paste event for Markdown to Rich Text translation
     */
    function handlePasteEvent(e, editor) {
        const clipboardData = e.originalEvent.clipboardData || window.clipboardData;
        if (!clipboardData) return;

        const text = clipboardData.getData('text/plain');
        
        // Simple Markdown detection: starts with headers, links, lists, or bold/italic markers
        const isMarkdown = /(^#\s|\*\*|__|\*|- \[[x ]\]|\[.+\]\(.+\)|```)/m.test(text);

        if (isMarkdown && typeof marked !== 'undefined') {
            e.preventDefault();
            const html = marked.parse(text);
            $(editor).summernote('pasteHTML', html);
            updateWordCount(editor);
        }
    }

    /**
     * Installs word counting/stats bar under the editor
     */
    function createStatsBar(editor) {
        const editorContainer = $(editor).next();
        if (document.getElementById('editor-stats-bar')) return;

        const statsBar = document.createElement('div');
        statsBar.id = 'editor-stats-bar';
        statsBar.className = 'editor-stats-bar';
        statsBar.innerHTML = `
            <div>
                <span>Words: <strong id="editor-word-count">0</strong></span>
                <span>Characters: <strong id="editor-char-count">0</strong></span>
                <span>Reading Time: <strong id="editor-read-time">1 min</strong></span>
            </div>
            <div>
                <span id="draft-status-msg" class="text-muted">Draft saved locally</span>
            </div>
        `;
        editorContainer.after(statsBar);
        updateWordCount(editor);
    }

    /**
     * Computes word count, character count, and reading time
     */
    function updateWordCount(editor) {
        let html = '';
        if ($(editor).data('summernote')) {
            html = $(editor).summernote('code');
        } else {
            html = editor.value || '';
        }
        const text = html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
        
        const words = text ? text.split(/\s+/).length : 0;
        const chars = text.length;
        const readTime = Math.max(1, Math.ceil(words / 200));

        const wordCountEl = document.getElementById('editor-word-count');
        const charCountEl = document.getElementById('editor-char-count');
        const readTimeEl = document.getElementById('editor-read-time');

        if (wordCountEl) wordCountEl.textContent = words;
        if (charCountEl) charCountEl.textContent = chars;
        if (readTimeEl) readTimeEl.textContent = readTime + ' min';
    }

    /**
     * Creates and attaches the image sidebar panel DOM
     */
    function createImageSidebar() {
        if (document.getElementById('image-properties-sidebar')) return;

        const sidebar = document.createElement('div');
        sidebar.id = 'image-properties-sidebar';
        sidebar.className = 'image-properties-sidebar';
        sidebar.innerHTML = `
            <div class="image-sidebar-header">
                <h5><i class="fas fa-image mr-1"></i>Image Properties</h5>
                <button type="button" class="close-sidebar" id="close-image-sidebar">&times;</button>
            </div>
            <div class="image-sidebar-body">
                <div class="image-sidebar-section">
                    <div class="image-sidebar-preview-box">
                        <img id="sidebar-img-preview" src="" alt="preview">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-block font-weight-bold" id="sidebar-action-replace">
                        <i class="fas fa-exchange-alt mr-1"></i>Replace Image
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-block font-weight-bold" id="sidebar-action-copyurl">
                        <i class="fas fa-copy mr-1"></i>Copy Image URL
                    </button>
                </div>

                <div class="image-sidebar-section">
                    <div class="image-sidebar-section-title">SEO Metadata</div>
                    <div class="form-group mb-2">
                        <label class="font-weight-bold mb-1" style="font-size:0.75rem">Alt Text</label>
                        <input type="text" class="form-control form-control-sm" id="sidebar-img-alt" placeholder="Describe image content...">
                    </div>
                    <div class="form-group mb-2">
                        <label class="font-weight-bold mb-1" style="font-size:0.75rem">Title Attribute</label>
                        <input type="text" class="form-control form-control-sm" id="sidebar-img-title" placeholder="Image title tooltips...">
                    </div>
                    <div class="form-group mb-2">
                        <label class="font-weight-bold mb-1" style="font-size:0.75rem">Caption / Label</label>
                        <input type="text" class="form-control form-control-sm" id="sidebar-img-caption" placeholder="Image caption...">
                    </div>
                </div>

                <div class="image-sidebar-section">
                    <div class="image-sidebar-section-title">Dimensions</div>
                    <div class="row no-gutters align-items-center mb-2">
                        <div class="col-5">
                            <input type="number" class="form-control form-control-sm" id="sidebar-img-width" placeholder="Width">
                        </div>
                        <div class="col-2 text-center text-muted" style="font-size:0.8rem">
                            <i class="fas fa-times"></i>
                        </div>
                        <div class="col-5">
                            <input type="number" class="form-control form-control-sm" id="sidebar-img-height" placeholder="Height">
                        </div>
                    </div>
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="sidebar-img-aspect" checked>
                        <label class="custom-control-label" for="sidebar-img-aspect" style="font-size:0.78rem">Lock Aspect Ratio</label>
                    </div>
                </div>

                <div class="image-sidebar-section">
                    <div class="image-sidebar-section-title">Styling & Layout</div>
                    <div class="form-group mb-2">
                        <label class="font-weight-bold mb-1" style="font-size:0.75rem">Alignment</label>
                        <select class="form-control form-control-sm" id="sidebar-img-align">
                            <option value="none">None (Inline)</option>
                            <option value="left">Float Left</option>
                            <option value="center">Center</option>
                            <option value="right">Float Right</option>
                            <option value="full">Full Width (Block)</option>
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label class="font-weight-bold mb-1" style="font-size:0.75rem">Margin (Spacing)</label>
                        <select class="form-control form-control-sm" id="sidebar-img-margin">
                            <option value="none">None</option>
                            <option value="sm">Small (5px)</option>
                            <option value="md">Medium (15px)</option>
                            <option value="lg">Large (30px)</option>
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label class="font-weight-bold mb-1" style="font-size:0.75rem">Rounded Corners</label>
                        <select class="form-control form-control-sm" id="sidebar-img-radius">
                            <option value="0">Square (0px)</option>
                            <option value="4px">Small (4px)</option>
                            <option value="8px">Medium (8px)</option>
                            <option value="16px">Large (16px)</option>
                            <option value="50%">Circle (Round)</option>
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label class="font-weight-bold mb-1" style="font-size:0.75rem">Shadow</label>
                        <select class="form-control form-control-sm" id="sidebar-img-shadow">
                            <option value="none">None</option>
                            <option value="sm">Subtle (sm)</option>
                            <option value="md">Medium (md)</option>
                            <option value="lg">Heavy (lg)</option>
                        </select>
                    </div>
                </div>

                <div class="image-sidebar-section">
                    <div class="image-sidebar-section-title">Hyperlink & Loading</div>
                    <div class="form-group mb-2">
                        <label class="font-weight-bold mb-1" style="font-size:0.75rem">Link URL</label>
                        <input type="text" class="form-control form-control-sm" id="sidebar-img-link" placeholder="https://...">
                    </div>
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="sidebar-img-link-tab">
                        <label class="custom-control-label" for="sidebar-img-link-tab" style="font-size:0.78rem">Open link in new tab</label>
                    </div>
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="sidebar-img-lazy" checked>
                        <label class="custom-control-label" for="sidebar-img-lazy" style="font-size:0.78rem">Lazy load image</label>
                    </div>
                </div>

                <div class="image-sidebar-section pt-2">
                    <button type="button" class="btn btn-sm btn-danger btn-block font-weight-bold" id="sidebar-action-delete">
                        <i class="fas fa-trash-alt mr-1"></i>Delete Image
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(sidebar);

        // Sidebar event listeners
        document.getElementById('close-image-sidebar').addEventListener('click', deselectImage);
        
        // Link changes in sidebar inputs to active image attributes
        document.getElementById('sidebar-img-alt').addEventListener('input', function() {
            if ($activeImage) $activeImage.attr('alt', this.value);
        });

        document.getElementById('sidebar-img-title').addEventListener('input', function() {
            if ($activeImage) $activeImage.attr('title', this.value);
        });

        // Caption support
        document.getElementById('sidebar-img-caption').addEventListener('input', function() {
            if (!$activeImage) return;
            const captionVal = this.value;
            
            // Check if nested in figure
            let parent = $activeImage.parent();
            if (parent.is('figure')) {
                let figcaption = parent.find('figcaption');
                if (figcaption.length) {
                    if (captionVal) {
                        figcaption.text(captionVal);
                    } else {
                        // Remove figure container and restore image if caption cleared
                        $activeImage.unwrap();
                        parent.find('figcaption').remove();
                    }
                } else if (captionVal) {
                    parent.append(`<figcaption>${captionVal}</figcaption>`);
                }
            } else if (captionVal) {
                // Wrap in figure
                $activeImage.wrap('<figure class="img-figure"></figure>');
                $activeImage.parent().append(`<figcaption>${captionVal}</figcaption>`);
            }
        });

        // Dimensions change (Width / Height)
        let aspectRatio = 1;
        document.getElementById('sidebar-img-width').addEventListener('input', function() {
            if (!$activeImage) return;
            const w = parseInt(this.value) || 0;
            if (w > 0) {
                $activeImage.css('width', w + 'px');
                $activeImage.attr('width', w);
                if (document.getElementById('sidebar-img-aspect').checked) {
                    const h = Math.round(w / aspectRatio);
                    document.getElementById('sidebar-img-height').value = h;
                    $activeImage.css('height', h + 'px');
                    $activeImage.attr('height', h);
                }
            }
        });

        document.getElementById('sidebar-img-height').addEventListener('input', function() {
            if (!$activeImage) return;
            const h = parseInt(this.value) || 0;
            if (h > 0) {
                $activeImage.css('height', h + 'px');
                $activeImage.attr('height', h);
                if (document.getElementById('sidebar-img-aspect').checked) {
                    const w = Math.round(h * aspectRatio);
                    document.getElementById('sidebar-img-width').value = w;
                    $activeImage.css('width', w + 'px');
                    $activeImage.attr('width', w);
                }
            }
        });

        // Alignments change
        document.getElementById('sidebar-img-align').addEventListener('change', function() {
            if (!$activeImage) return;
            const align = this.value;
            $activeImage.removeClass('img-align-left img-align-right img-align-center img-align-full');
            
            // Check for figure wrapper
            let el = $activeImage.parent().is('figure') ? $activeImage.parent() : $activeImage;
            el.removeClass('img-align-left img-align-right img-align-center img-align-full');
            
            if (align !== 'none') {
                el.addClass('img-align-' + align);
            }
        });

        // Margins change
        document.getElementById('sidebar-img-margin').addEventListener('change', function() {
            if (!$activeImage) return;
            const margin = this.value;
            $activeImage.css('margin', '');
            if (margin === 'sm') $activeImage.css('margin', '5px');
            else if (margin === 'md') $activeImage.css('margin', '15px');
            else if (margin === 'lg') $activeImage.css('margin', '30px');
        });

        // Border radius
        document.getElementById('sidebar-img-radius').addEventListener('change', function() {
            if (!$activeImage) return;
            $activeImage.css('border-radius', this.value === '0' ? '' : this.value);
        });

        // Shadow
        document.getElementById('sidebar-img-shadow').addEventListener('change', function() {
            if (!$activeImage) return;
            const val = this.value;
            $activeImage.css('box-shadow', '');
            if (val === 'sm') $activeImage.css('box-shadow', '0 1px 3px rgba(0,0,0,0.1)');
            else if (val === 'md') $activeImage.css('box-shadow', '0 4px 6px rgba(0,0,0,0.1)');
            else if (val === 'lg') $activeImage.css('box-shadow', '0 10px 15px rgba(0,0,0,0.1)');
        });

        // Hyperlinks
        document.getElementById('sidebar-img-link').addEventListener('input', function() {
            if (!$activeImage) return;
            const href = this.value;
            let parent = $activeImage.parent();
            if (parent.is('a')) {
                if (href) {
                    parent.attr('href', href);
                } else {
                    $activeImage.unwrap();
                }
            } else if (href) {
                $activeImage.wrap(`<a href="${href}"></a>`);
                if (document.getElementById('sidebar-img-link-tab').checked) {
                    $activeImage.parent().attr('target', '_blank');
                }
            }
        });

        document.getElementById('sidebar-img-link-tab').addEventListener('change', function() {
            if (!$activeImage) return;
            let parent = $activeImage.parent();
            if (parent.is('a')) {
                if (this.checked) parent.attr('target', '_blank');
                else parent.removeAttr('target');
            }
        });

        // Lazy load toggle
        document.getElementById('sidebar-img-lazy').addEventListener('change', function() {
            if ($activeImage) {
                if (this.checked) $activeImage.attr('loading', 'lazy');
                else $activeImage.removeAttr('loading');
            }
        });

        // Actions
        document.getElementById('sidebar-action-copyurl').addEventListener('click', function() {
            if ($activeImage) {
                const url = $activeImage.attr('src');
                navigator.clipboard.writeText(url).then(() => {
                    toast('success', 'URL copied to clipboard!');
                });
            }
        });

        document.getElementById('sidebar-action-replace').addEventListener('click', function() {
            window.open('/dashboard/media?mode=picker', 'Media Library', 'width=1000,height=650,status=no,toolbar=no,menubar=no,location=no');
        });

        document.getElementById('sidebar-action-delete').addEventListener('click', function() {
            if ($activeImage) {
                const $target = $activeImage;
                deselectImage();
                if ($target.parent().is('a')) $target.parent().remove();
                else if ($target.parent().is('figure')) $target.parent().remove();
                else $target.remove();
                toast('success', 'Image deleted.');
            }
        });
    }

    /**
     * Marks an image as selected, loads properties to sidebar panel and slides it open
     */
    function selectImage($img) {
        $activeImage = $img;
        
        // Highlight active image in editor
        $('.note-editable img').css('outline', '');
        $img.css('outline', '3px solid #6366f1');

        const src = $img.attr('src');
        const alt = $img.attr('alt') || '';
        const title = $img.attr('title') || '';
        
        // Find caption inside a figure
        let caption = '';
        let parent = $img.parent();
        if (parent.is('figure')) {
            const figcaption = parent.find('figcaption');
            if (figcaption.length) caption = figcaption.text();
        }

        // Get dimensions
        let w = $img.width() || $img.attr('width') || $img[0].naturalWidth;
        let h = $img.height() || $img.attr('height') || $img[0].naturalHeight;
        
        // Backup natural aspect ratio for locking aspect ratio
        let natW = $img[0].naturalWidth || w || 100;
        let natH = $img[0].naturalHeight || h || 100;
        let aspectRatio = natW / natH;

        // Fill sidebar values
        document.getElementById('sidebar-img-preview').src = src;
        document.getElementById('sidebar-img-alt').value = alt;
        document.getElementById('sidebar-img-title').value = title;
        document.getElementById('sidebar-img-caption').value = caption;
        document.getElementById('sidebar-img-width').value = w || '';
        document.getElementById('sidebar-img-height').value = h || '';
        
        // Check alignments
        let align = 'none';
        let el = parent.is('figure') ? parent : $img;
        if (el.hasClass('img-align-left')) align = 'left';
        else if (el.hasClass('img-align-center')) align = 'center';
        else if (el.hasClass('img-align-right')) align = 'right';
        else if (el.hasClass('img-align-full')) align = 'full';
        document.getElementById('sidebar-img-align').value = align;

        // Margins
        let margin = 'none';
        const cssMargin = $img.css('margin');
        if (cssMargin && cssMargin.includes('5px')) margin = 'sm';
        else if (cssMargin && cssMargin.includes('15px')) margin = 'md';
        else if (cssMargin && cssMargin.includes('30px')) margin = 'lg';
        document.getElementById('sidebar-img-margin').value = margin;

        // Border radius
        const radius = $img.css('border-radius') || '0';
        document.getElementById('sidebar-img-radius').value = radius.includes('50%') ? '50%' : radius;

        // Shadow
        let shadow = 'none';
        const cssShadow = $img.css('box-shadow');
        if (cssShadow && cssShadow.includes('10px')) shadow = 'lg';
        else if (cssShadow && cssShadow.includes('4px')) shadow = 'md';
        else if (cssShadow && cssShadow.includes('1px')) shadow = 'sm';
        document.getElementById('sidebar-img-shadow').value = shadow;

        // Hyperlinks check
        let linkUrl = '';
        let openNewTab = false;
        let p = $img.parent();
        if (p.is('a')) {
            linkUrl = p.attr('href') || '';
            openNewTab = p.attr('target') === '_blank';
        }
        document.getElementById('sidebar-img-link').value = linkUrl;
        document.getElementById('sidebar-img-link-tab').checked = openNewTab;

        // Lazy load check
        document.getElementById('sidebar-img-lazy').checked = $img.attr('loading') === 'lazy';

        // Aspect ratio tracking
        document.getElementById('sidebar-img-aspect').checked = true;

        // Open Sidebar
        document.getElementById('image-properties-sidebar').classList.add('open');
    }

    /**
     * Closes sidebar and removes active image styling
     */
    function deselectImage() {
        if ($activeImage) {
            $activeImage.css('outline', '');
            $activeImage = null;
        }
        const sidebar = document.getElementById('image-properties-sidebar');
        if (sidebar) sidebar.classList.remove('open');
    }

    /**
     * Helper to perform AJAX file upload
     */
    function uploadFileToServer(file, editor, url, csrfToken, displayName) {
        const data = new FormData();
        data.append('image', file);

        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
                console.log('[EditorUpload] Upload response:', response);
                if (response.success) {
                    toast('success', 'Image uploaded!');
                    const imgHtml = `<img src="${response.url}" class="img-fluid responsive-img" loading="lazy" alt="${displayName}">`;
                    $(editor).summernote('pasteHTML', imgHtml);
                    updateWordCount(editor);
                } else {
                    toast('error', response.message || 'Upload failed.');
                }
            },
            error: function(xhr) {
                console.error('[EditorUpload] Upload error:', xhr.status, xhr.responseText);
                let msg = 'Server error (' + xhr.status + ')';
                if (xhr.status === 419) {
                    msg = 'Session expired. Please refresh the page and try again.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                toast('error', 'Upload failed: ' + msg);
            }
        });
    }

    /**
     * Compress image using HTML5 Canvas and then upload
     */
    function compressAndUpload(file, editor, url, csrfToken) {
        // Fallback for non-images
        if (!file.type.startsWith('image/')) {
            uploadFileToServer(file, editor, url, csrfToken, file.name);
            return;
        }

        // Skip compression for small files (< 200KB) to save CPU cycles
        if (file.size < 200 * 1024) {
            uploadFileToServer(file, editor, url, csrfToken, file.name);
            return;
        }

        toast('info', 'Compressing ' + file.name + '...');

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                try {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;
                    const maxDim = 1920;

                    // Constrain max dimensions to 1920px
                    if (width > maxDim || height > maxDim) {
                        if (width > height) {
                            height = Math.round((height * maxDim) / width);
                            width = maxDim;
                        } else {
                            width = Math.round((width * maxDim) / height);
                            height = maxDim;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    // Convert to high-quality JPEG blob (quality 0.82)
                    canvas.toBlob(function(blob) {
                        if (blob) {
                            const cleanName = file.name.replace(/\.[^/.]+$/, "") + ".jpg";
                            const compressedFile = new File([blob], cleanName, {
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            });

                            console.log('[EditorUpload] Compressed', file.name, 'from', (file.size / 1024).toFixed(1), 'KB to', (compressedFile.size / 1024).toFixed(1), 'KB');
                            toast('info', 'Uploading compressed ' + cleanName + '...');
                            uploadFileToServer(compressedFile, editor, url, csrfToken, cleanName);
                        } else {
                            // Fallback to original upload if toBlob fails
                            console.warn('[EditorUpload] canvas.toBlob returned null, using original file');
                            toast('info', 'Uploading original ' + file.name + '...');
                            uploadFileToServer(file, editor, url, csrfToken, file.name);
                        }
                    }, 'image/jpeg', 0.82);
                } catch (err) {
                    console.error('[EditorUpload] Compression failed, using original file:', err);
                    toast('info', 'Uploading original ' + file.name + '...');
                    uploadFileToServer(file, editor, url, csrfToken, file.name);
                }
            };
            img.onerror = function() {
                console.error('[EditorUpload] Image load error, using original file');
                toast('info', 'Uploading original ' + file.name + '...');
                uploadFileToServer(file, editor, url, csrfToken, file.name);
            };
            img.src = e.target.result;
        };
        reader.onerror = function() {
            console.error('[EditorUpload] FileReader error, using original file');
            toast('info', 'Uploading original ' + file.name + '...');
            uploadFileToServer(file, editor, url, csrfToken, file.name);
        };
        reader.readAsDataURL(file);
    }

    /**
     * Upload an array of files via AJAX and inserts them
     */
    window.uploadEditorImages = function(files, editor) {
        const url = '/dashboard/editor/upload-image';
        
        // Show start toast immediately so the user knows it has begun
        if (files && files.length > 0) {
            toast('info', 'Mengunggah ' + files.length + ' gambar...');
        }

        // CSRF token: try meta tag first, then hidden form input as fallback
        let csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (!csrfToken) {
            csrfToken = $('input[name="_token"]').val();
        }

        if (!csrfToken) {
            console.error('[EditorUpload] No CSRF token found!');
            toast('error', 'Upload failed: CSRF token not found. Please refresh the page.');
            return;
        }

        console.log('[EditorUpload] Starting upload to:', url, 'files:', files.length, 'CSRF:', csrfToken.substring(0, 8) + '...');

        for (let i = 0; i < files.length; i++) {
            compressAndUpload(files[i], editor, url, csrfToken);
        }
    };

    /**
     * Insert image selected from picker popup window
     */
    function insertImageFromPicker(editor, data) {
        if (data.url) {
            // Replace image if sidebar was replacing
            if ($activeImage) {
                $activeImage.attr('src', data.url);
                if (data.alt) $activeImage.attr('alt', data.alt);
                if (data.title) $activeImage.attr('title', data.title);
                document.getElementById('sidebar-img-preview').src = data.url;
                toast('success', 'Image replaced!');
            } else {
                // Regular insert
                const alt = data.alt || '';
                const title = data.title || '';
                const caption = data.caption || '';
                
                let imgHtml = `<img src="${data.url}" alt="${alt}" title="${title}" class="img-fluid responsive-img" loading="lazy">`;
                if (caption) {
                    imgHtml = `<figure class="img-figure">${imgHtml}<figcaption>${caption}</figcaption></figure>`;
                }
                $(editor).summernote('pasteHTML', imgHtml);
                updateWordCount(editor);
            }
        }
    }

    /**
     * Auto Save to LocalStorage helper
     */
    function startAutoSave(editor) {
        if (autoSaveInterval) clearInterval(autoSaveInterval);
        
        autoSaveInterval = setInterval(function() {
            const html = $(editor).summernote('code');
            const wordCount = parseInt(document.getElementById('editor-word-count')?.textContent || '0');
            
            // Only auto-save if there is actually content
            if (wordCount > 5) {
                localStorage.setItem(draftKey, JSON.stringify({
                    html: html,
                    timestamp: new Date().getTime()
                }));
                const status = document.getElementById('draft-status-msg');
                if (status) {
                    status.innerHTML = '<i class="fas fa-check-circle text-success mr-1"></i>Draft saved locally (' + new Date().toLocaleTimeString() + ')';
                }
            }
        }, 30000); // Save every 30 seconds
    }

    /**
     * Checks if draft exists in localStorage and prompts to restore
     */
    function checkSavedDraft(editor) {
        const saved = localStorage.getItem(draftKey);
        if (saved) {
            try {
                const data = JSON.parse(saved);
                const age = new Date().getTime() - data.timestamp;
                
                // Only show restore prompt if draft is younger than 24 hours
                if (age < 24 * 60 * 60 * 1000) {
                    const timeStr = new Date(data.timestamp).toLocaleTimeString();
                    Swal.fire({
                        title: 'Restore Draft?',
                        text: 'An auto-saved draft from ' + timeStr + ' was found.',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, restore it',
                        cancelButtonText: 'No, discard'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $(editor).summernote('code', data.html);
                            updateWordCount(editor);
                            toast('success', 'Draft restored!');
                        } else {
                            localStorage.removeItem(draftKey);
                        }
                    });
                }
            } catch (e) {
                localStorage.removeItem(draftKey);
            }
        }
    }

    // Helper functions
    function toast(icon, title) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: title,
                showConfirmButton: false,
                timer: 3000
            });
        }
    }

    // Route Helper Fallback
    function route(name) {
        if (name === 'dashboard.editor.upload-image') {
            return '/dashboard/editor/upload-image';
        }
        return '';
    }

    // Focus Mode Toggle Handler (called from custom Summernote button)
    window.toggleFocusMode = function() {
        const body = document.body;
        const mainCard = $('.content-wrapper').find('.card').first();
        
        if (body.classList.contains('editor-focus-mode')) {
            // Disable focus mode
            body.classList.remove('editor-focus-mode');
            mainCard.unwrap();
            toast('info', 'Focus Mode Disabled');
        } else {
            // Enable focus mode
            body.classList.add('editor-focus-mode');
            mainCard.wrap('<div class="focus-container-box"></div>');
            toast('info', 'Focus Mode Enabled. Hit Esc or click toggle again to exit.');
        }
    };

    // Close focus mode with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.body.classList.contains('editor-focus-mode')) {
            window.toggleFocusMode();
        }
    });

})();
