document.addEventListener('DOMContentLoaded', () => {
    // 1. Fade out Preloader
    const loader = document.querySelector('.loader');
    if (loader) {
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => loader.remove(), 300);
        }, 300);
    }

    // 2. Sticky Header Scroll Behavior (Hide on Scroll Down, Show on Scroll Up)
    let lastScroll = 0;
    const header = document.querySelector('header.header');
    if (header) {
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            if (currentScroll <= 72) {
                header.classList.remove('header-hidden');
                return;
            }
            if (currentScroll > lastScroll && !header.classList.contains('header-hidden')) {
                // Scroll down
                header.classList.add('header-hidden');
            } else if (currentScroll < lastScroll && header.classList.contains('header-hidden')) {
                // Scroll up
                header.classList.remove('header-hidden');
            }
            lastScroll = currentScroll;
        });
    }

    // 3. Theme Manager (Light / Dark / System)
    const initTheme = () => {
        const toggleBtn = document.querySelector('.theme-toggle-btn');
        const theme = localStorage.getItem('theme') || 'system';
        
        const applyTheme = (themeName) => {
            if (themeName === 'dark' || (themeName === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        };

        applyTheme(theme);

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                localStorage.setItem('theme', newTheme);
                applyTheme(newTheme);
            });
        }

        // Listen for system changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (localStorage.getItem('theme') === 'system' || !localStorage.getItem('theme')) {
                applyTheme('system');
            }
        });
    };
    initTheme();

    // 4. Mobile Menu Drawer
    const menuToggler = document.querySelector('.navbar-toggler');
    const drawer = document.querySelector('.mobile-drawer');
    const drawerClose = document.querySelector('.mobile-drawer-close');
    const overlay = document.querySelector('.drawer-overlay');

    if (menuToggler && drawer && overlay) {
        const openDrawer = () => {
            drawer.classList.add('open');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        };

        const closeDrawer = () => {
            drawer.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        };

        menuToggler.addEventListener('click', openDrawer);
        if (drawerClose) drawerClose.addEventListener('click', closeDrawer);
        overlay.addEventListener('click', closeDrawer);
    }

    // 5. Toast Notification System
    window.showToast = (message, type = 'success') => {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const successSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle" style="color: var(--color-success)"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`;
        const errorSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-circle" style="color: var(--color-danger)"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12" y1="16" y2="16"/></svg>`;
        const iconSvg = type === 'success' ? successSvg : errorSvg;
        toast.innerHTML = `
            ${iconSvg}
            <span>${message}</span>
        `;
        
        container.appendChild(toast);
        
        // Trigger reflow for transition
        toast.offsetHeight;
        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    };


    // 7. Dynamic Table of Contents (TOC) Generation
    const postBody = document.querySelector('.post-body');
    const tocLinksContainer = document.getElementById('toc-links');

    if (postBody && tocLinksContainer) {
        // ── Query H2, H3, H4 (H4 was missing previously) ──
        const headings = postBody.querySelectorAll('h2, h3, h4');

        if (headings.length > 0) {
            // Depth map for CSS class assignment
            const depthMap = { H2: 'depth-2', H3: 'depth-3', H4: 'depth-4' };

            headings.forEach((heading, idx) => {
                // Ensure each heading has a unique, stable ID
                if (!heading.id) {
                    const slug = heading.textContent
                        .trim()
                        .toLowerCase()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .substring(0, 60);
                    heading.id = slug || ('heading-' + idx);
                }

                const link = document.createElement('a');
                link.href = '#' + heading.id;
                link.className = `toc-link ${depthMap[heading.tagName] || 'depth-2'}`;
                link.textContent = heading.textContent.trim();
                link.setAttribute('data-heading-id', heading.id);
                tocLinksContainer.appendChild(link);
            });

            // ── Smooth scroll on TOC link click ──
            tocLinksContainer.addEventListener('click', (e) => {
                const link = e.target.closest('.toc-link');
                if (!link) return;
                e.preventDefault();

                const targetId = link.getAttribute('href');
                const targetEl = document.getElementById(targetId.substring(1));
                if (!targetEl) return;

                const headerOffset = 96; // header height + buffer
                const top = targetEl.getBoundingClientRect().top + window.pageYOffset - headerOffset;
                window.scrollTo({ top, behavior: 'smooth' });

                // On mobile: close accordion parent after clicking a link
                const parentContainer = link.closest('.toc-container');
                if (parentContainer && window.innerWidth <= 768) {
                    parentContainer.classList.remove('expanded');
                    const parentTitle = parentContainer.querySelector('.toc-title');
                    if (parentTitle) parentTitle.setAttribute('aria-expanded', 'false');
                }
            });

            // ── Active heading highlight via IntersectionObserver ──
            // rootMargin: top offset accounts for sticky header (-96px),
            // bottom margin -50% means heading must be in top half of viewport
            let activeObserver = null;

            const setupObserver = () => {
                if (activeObserver) activeObserver.disconnect();

                activeObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const id = entry.target.id;
                            document.querySelectorAll('.toc-link').forEach(l => {
                                const isActive = l.getAttribute('data-heading-id') === id;
                                l.classList.toggle('active', isActive);
                            });
                        }
                    });
                }, {
                    root: null,
                    rootMargin: '-96px 0px -50% 0px',
                    threshold: 0
                });

                headings.forEach(h => activeObserver.observe(h));
            };

            setupObserver();

            // Cleanup observer when navigating away (prevents memory leaks)
            window.addEventListener('beforeunload', () => {
                if (activeObserver) activeObserver.disconnect();
            });

        } else {
            // No headings found — hide only the TOC container, NOT the entire panel
            const tocContainer = document.getElementById('toc-container');
            if (tocContainer) tocContainer.style.display = 'none';

            // Hide the entire panel only if there are no visible containers inside it
            const tocPanel = document.querySelector('.post-toc-panel');
            if (tocPanel) {
                const visibleContainers = Array.from(tocPanel.querySelectorAll('.toc-container'))
                    .filter(c => c.style.display !== 'none');
                if (visibleContainers.length === 0) {
                    tocPanel.style.display = 'none';
                }
            }
        }
    }


    // 8. Code Blocks copy buttons and styling
    if (postBody) {
        postBody.querySelectorAll('pre').forEach((pre) => {
            let wrapper = pre.closest('.code-block-wrapper');
            let copyBtn = null;
            let codeText = '';

            // If not wrapped, wrap it and build header
            if (!wrapper) {
                // Strip any old header/copy button baked into the <pre> HTML
                const oldHeader = pre.querySelector('.code-header');
                if (oldHeader) oldHeader.remove();

                // Grab clean code text AFTER removing old header
                const codeEl = pre.querySelector('code');
                codeText = (codeEl ? codeEl.innerText : pre.innerText).trim();

                // Detect language
                let lang = 'CODE';
                if (codeEl) {
                    codeEl.classList.forEach(c => {
                        if (c.startsWith('language-')) { lang = c.replace('language-', '').toUpperCase(); }
                        if (c.startsWith('lang-')) { lang = c.replace('lang-', '').toUpperCase(); }
                    });
                }

                // Build wrapper
                wrapper = document.createElement('div');
                wrapper.className = 'code-block-wrapper';

                // Build header
                const header = document.createElement('div');
                header.className = 'code-header';

                const langSpan = document.createElement('span');
                langSpan.className = 'code-language';
                langSpan.textContent = lang;

                copyBtn = document.createElement('button');
                copyBtn.type = 'button';
                copyBtn.className = 'code-copy-btn';
                copyBtn.setAttribute('aria-label', 'Copy code');

                const copySvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                copySvg.setAttribute('width', '13'); copySvg.setAttribute('height', '13');
                copySvg.setAttribute('viewBox', '0 0 24 24'); copySvg.setAttribute('fill', 'none');
                copySvg.setAttribute('stroke', 'currentColor'); copySvg.setAttribute('stroke-width', '2.5');
                copySvg.setAttribute('stroke-linecap', 'round'); copySvg.setAttribute('stroke-linejoin', 'round');
                copySvg.innerHTML = '<rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>';

                const copyLabel = document.createElement('span');
                copyLabel.textContent = 'Copy';

                copyBtn.appendChild(copySvg);
                copyBtn.appendChild(copyLabel);
                header.appendChild(langSpan);
                header.appendChild(copyBtn);

                // Insert into DOM
                pre.parentNode.insertBefore(wrapper, pre);
                wrapper.appendChild(header);
                wrapper.appendChild(pre);
            } else {
                // It is already wrapped. Find the existing copy button.
                copyBtn = wrapper.querySelector('.code-copy-btn');
                const codeEl = pre.querySelector('code');
                codeText = (codeEl ? codeEl.innerText : pre.innerText).trim();
            }

            // If we found or created a copy button, attach listener
            if (copyBtn && !copyBtn.dataset.hasListener) {
                copyBtn.dataset.hasListener = 'true';

                const copyLabel = copyBtn.querySelector('span') || copyBtn;
                const copySvg = copyBtn.querySelector('svg');

                copyBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    const doCopy = () => {
                        if (navigator.clipboard) {
                            return navigator.clipboard.writeText(codeText);
                        }
                        return new Promise((resolve, reject) => {
                            try {
                                const ta = document.createElement('textarea');
                                ta.value = codeText;
                                ta.setAttribute('readonly', '');
                                ta.style.position = 'fixed';
                                ta.style.opacity = '0';
                                ta.style.left = '-9999px';
                                ta.style.top = '0';
                                document.body.appendChild(ta);
                                ta.focus();
                                ta.select();
                                const ok = document.execCommand('copy');
                                document.body.removeChild(ta);
                                if (ok) resolve();
                                else reject(new Error('Gagal mengeksekusi copy command.'));
                            } catch (err) {
                                reject(err);
                            }
                        });
                    };

                    doCopy().then(() => {
                        copyLabel.textContent = 'Copied!';
                        copyBtn.style.color = 'var(--color-success)';
                        if (copySvg) {
                            copySvg.innerHTML = '<polyline points="20 6 9 17 4 12"/>';
                        }
                        setTimeout(() => {
                            copyLabel.textContent = 'Copy';
                            copyBtn.style.color = '';
                            if (copySvg) {
                                copySvg.innerHTML = '<rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>';
                            }
                        }, 2000);
                    }).catch((err) => {
                        console.error('Copy failed:', err);
                        showToast('Gagal menyalin kode: ' + (err ? err.message : ''), 'error');
                    });
                });
            }
        });
    }

    // 8.5 Inline Copy Text Helper
    const copyTextElements = document.querySelectorAll('.copy-text');
    copyTextElements.forEach((el) => {
        if (el.dataset.hasListener) return;
        el.dataset.hasListener = 'true';

        // Save target text (stripping code icons if already present)
        const textToCopy = el.innerText.trim();

        // Create a wrapper for the copy icon
        const copyIcon = document.createElement('span');
        copyIcon.className = 'copy-text-icon';
        copyIcon.style.marginLeft = '6px';
        copyIcon.style.display = 'inline-flex';
        copyIcon.style.alignItems = 'center';
        copyIcon.style.verticalAlign = 'middle';
        copyIcon.style.color = 'var(--text-muted)';
        copyIcon.style.transition = 'color 0.2s';

        const copySvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        copySvg.setAttribute('width', '13');
        copySvg.setAttribute('height', '13');
        copySvg.setAttribute('viewBox', '0 0 24 24');
        copySvg.setAttribute('fill', 'none');
        copySvg.setAttribute('stroke', 'currentColor');
        copySvg.setAttribute('stroke-width', '2.5');
        copySvg.setAttribute('stroke-linecap', 'round');
        copySvg.setAttribute('stroke-linejoin', 'round');
        copySvg.innerHTML = '<rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>';

        copyIcon.appendChild(copySvg);
        el.appendChild(copyIcon);

        el.addEventListener('mouseenter', () => {
            copyIcon.style.color = 'var(--color-primary)';
        });
        el.addEventListener('mouseleave', () => {
            copyIcon.style.color = 'var(--text-muted)';
        });

        el.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const doCopy = () => {
                if (navigator.clipboard && window.isSecureContext) {
                    return navigator.clipboard.writeText(textToCopy);
                }
                return new Promise((resolve, reject) => {
                    try {
                        const ta = document.createElement('textarea');
                        ta.value = textToCopy;
                        ta.setAttribute('readonly', '');
                        ta.style.position = 'fixed';
                        ta.style.opacity = '0';
                        ta.style.left = '-9999px';
                        ta.style.top = '0';
                        document.body.appendChild(ta);
                        ta.focus();
                        ta.select();
                        const ok = document.execCommand('copy');
                        document.body.removeChild(ta);
                        if (ok) resolve();
                        else reject(new Error('Gagal mengeksekusi copy command.'));
                    } catch (err) {
                        reject(err);
                    }
                });
            };

            doCopy().then(() => {
                showToast('Teks berhasil disalin!', 'success');
                copySvg.innerHTML = '<polyline points="20 6 9 17 4 12"/>';
                copyIcon.style.color = 'var(--color-success)';
                setTimeout(() => {
                    copySvg.innerHTML = '<rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>';
                    copyIcon.style.color = 'var(--text-muted)';
                }, 2000);
            }).catch((err) => {
                console.error('Copy failed:', err);
                showToast('Gagal menyalin teks.', 'error');
            });
        });
    });

    // 9. Share Button — works on HTTP, HTTPS, and localhost
    const shareBtn = document.getElementById('share-link-btn');
    if (shareBtn) {
        shareBtn.addEventListener('click', async () => {
            const url = window.location.href;
            const title = document.title;

            // 1. Try native Web Share API (mobile / HTTPS only)
            if (navigator.share) {
                try {
                    await navigator.share({ title, url });
                    return;
                } catch (err) {
                    if (err.name === 'AbortError') return; // user cancelled
                }
            }

            // 2. Try modern Clipboard API (HTTPS / localhost)
            if (navigator.clipboard && window.isSecureContext) {
                try {
                    await navigator.clipboard.writeText(url);
                    showToast('Link artikel berhasil disalin!', 'success');
                    return;
                } catch (err) { /* fall through */ }
            }

            // 3. Fallback: execCommand (works on HTTP)
            try {
                const ta = document.createElement('textarea');
                ta.value = url;
                ta.style.cssText = 'position:fixed;top:0;left:0;opacity:0;';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                const ok = document.execCommand('copy');
                document.body.removeChild(ta);
                if (ok) {
                    showToast('Link artikel berhasil disalin!', 'success');
                    return;
                }
            } catch (err) { /* fall through */ }

            // 4. Last resort: show share popup modal
            showShareModal(url);
        });
    }

    function showShareModal(url) {
        const existing = document.getElementById('share-modal-overlay');
        if (existing) existing.remove();

        const overlay = document.createElement('div');
        overlay.id = 'share-modal-overlay';
        overlay.style.cssText = 'position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;padding:16px;';

        overlay.innerHTML = `
            <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:16px;padding:24px;max-width:480px;width:100%;box-shadow:var(--shadow-lg);">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                    <span style="font-weight:700;font-size:15px;color:var(--text-primary);">Bagikan Artikel</span>
                    <button id="share-modal-close" style="background:none;border:none;cursor:pointer;color:var(--text-muted);padding:4px;" aria-label="Tutup">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div style="display:flex;gap:8px;">
                    <input id="share-modal-url" type="text" value="${url}" readonly style="flex:1;padding:9px 12px;border:1px solid var(--border-color);border-radius:var(--radius-input);background:var(--bg-primary);color:var(--text-primary);font-size:13px;outline:none;">
                    <button id="share-modal-copy" style="padding:9px 16px;background:var(--color-primary);color:#fff;border:none;border-radius:var(--radius-button);cursor:pointer;font-size:13px;font-weight:600;white-space:nowrap;">Salin</button>
                </div>
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <a href="https://wa.me/?text=${encodeURIComponent(document.title + ' ' + url)}" target="_blank" rel="noopener" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:9px;background:#25D366;color:#fff;border-radius:var(--radius-button);text-decoration:none;font-size:13px;font-weight:600;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=${encodeURIComponent(document.title)}&url=${encodeURIComponent(url)}" target="_blank" rel="noopener" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:9px;background:#000;color:#fff;border-radius:var(--radius-button);text-decoration:none;font-size:13px;font-weight:600;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        X / Twitter
                    </a>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        document.getElementById('share-modal-close').onclick = () => overlay.remove();
        overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.remove(); });

        const copyBtn = document.getElementById('share-modal-copy');
        copyBtn.onclick = () => {
            const input = document.getElementById('share-modal-url');
            input.select();
            try {
                document.execCommand('copy');
                copyBtn.textContent = 'Tersalin ✓';
                copyBtn.style.background = 'var(--color-success)';
                setTimeout(() => { copyBtn.textContent = 'Salin'; copyBtn.style.background = 'var(--color-primary)'; }, 2000);
            } catch (e) {
                showToast('Silakan salin URL di atas secara manual.', 'error');
            }
        };
    }

    // 10. Command Palette Search (Ctrl + K)
    const cpOverlay = document.querySelector('.command-palette-overlay');
    const cpInput = document.getElementById('command-palette-search-input');
    const cpResults = document.querySelector('.command-palette-results');
    const searchIcons = document.querySelectorAll('.search-icon-btn, .search-icon');

    if (cpOverlay && cpInput && cpResults) {
        const openPalette = () => {
            cpOverlay.classList.add('open');
            cpInput.value = '';
            cpInput.focus();
            cpResults.innerHTML = '<div style="padding:16px;text-align:center;color:var(--text-secondary)">Type something to search articles...</div>';
            document.body.style.overflow = 'hidden';
        };

        const closePalette = () => {
            cpOverlay.classList.remove('open');
            document.body.style.overflow = '';
        };

        // Keyboard bindings (Ctrl + K)
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                if (cpOverlay.classList.contains('open')) {
                    closePalette();
                } else {
                    openPalette();
                }
            }
            if (e.key === 'Escape' && cpOverlay.classList.contains('open')) {
                closePalette();
            }
        });

        // Click handlers
        searchIcons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                openPalette();
            });
        });

        cpOverlay.addEventListener('click', (e) => {
            if (e.target === cpOverlay) {
                closePalette();
            }
        });

        // Search fetching input handler (Debounced)
        let searchTimeout;
        cpInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            const query = cpInput.value.trim();
            if (query.length < 2) {
                cpResults.innerHTML = '<div style="padding:16px;text-align:center;color:var(--text-secondary)">Type at least 2 characters to search...</div>';
                return;
            }

            cpResults.innerHTML = `
                <div class="skeleton-search-wrapper" style="padding: 16px; display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <div class="skeleton" style="width: 24px; height: 24px; border-radius: 50%;"></div>
                        <div style="flex-grow: 1; display: flex; flex-direction: column; gap: 6px;">
                            <div class="skeleton" style="width: 60%; height: 14px;"></div>
                            <div class="skeleton" style="width: 30%; height: 10px;"></div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <div class="skeleton" style="width: 24px; height: 24px; border-radius: 50%;"></div>
                        <div style="flex-grow: 1; display: flex; flex-direction: column; gap: 6px;">
                            <div class="skeleton" style="width: 80%; height: 14px;"></div>
                            <div class="skeleton" style="width: 40%; height: 10px;"></div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <div class="skeleton" style="width: 24px; height: 24px; border-radius: 50%;"></div>
                        <div style="flex-grow: 1; display: flex; flex-direction: column; gap: 6px;">
                            <div class="skeleton" style="width: 50%; height: 14px;"></div>
                            <div class="skeleton" style="width: 25%; height: 10px;"></div>
                        </div>
                    </div>
                </div>
            `;

            searchTimeout = setTimeout(() => {
                // Fetch search results asynchronously from route
                fetch(`/search?q=${encodeURIComponent(query)}&ajax=1`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.length === 0) {
                            cpResults.innerHTML = '<div style="padding:16px;text-align:center;color:var(--text-secondary)">No articles found.</div>';
                            return;
                        }
                        cpResults.innerHTML = '';
                        data.forEach(item => {
                            const resEl = document.createElement('div');
                            resEl.className = 'command-palette-result-item';
                            resEl.innerHTML = `
                                <div class="command-palette-result-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text" style="color: var(--text-secondary)"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><line x1="10" x2="8" y1="9" y2="9"/></svg>
                                </div>
                                <div class="command-palette-result-info">
                                    <h5>${item.title}</h5>
                                    <span>In ${item.category_title}</span>
                                </div>
                            `;
                            resEl.addEventListener('click', () => {
                                window.location.href = `/post/${item.slug}`;
                            });
                            cpResults.appendChild(resEl);
                        });
                    })
                    .catch(() => {
                        cpResults.innerHTML = '<div style="padding:16px;text-align:center;color:var(--color-danger)">An error occurred while searching.</div>';
                    });
            }, 300);
        });
    }

    // 11. Image Zoom / Lightbox
    const contentImages = document.querySelectorAll('.post-body img');
    contentImages.forEach(img => {
        img.style.cursor = 'zoom-in';
        img.addEventListener('click', () => {
            const overlay = document.createElement('div');
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100vw';
            overlay.style.height = '100vh';
            overlay.style.backgroundColor = 'rgba(9, 9, 11, 0.9)';
            overlay.style.zIndex = '9999';
            overlay.style.display = 'flex';
            overlay.style.alignItems = 'center';
            overlay.style.justifyContent = 'center';
            overlay.style.cursor = 'zoom-out';
            overlay.style.opacity = '0';
            overlay.style.transition = 'opacity 0.25s ease';

            const zoomImg = document.createElement('img');
            zoomImg.src = img.src;
            zoomImg.style.maxHeight = '90vh';
            zoomImg.style.maxWidth = '90vw';
            zoomImg.style.borderRadius = 'var(--radius-card)';
            zoomImg.style.objectFit = 'contain';

            overlay.appendChild(zoomImg);
            document.body.appendChild(overlay);

            // Reflow and show
            overlay.offsetHeight;
            overlay.style.opacity = '1';

            const closeZoom = () => {
                overlay.style.opacity = '0';
                setTimeout(() => overlay.remove(), 250);
            };

            overlay.addEventListener('click', closeZoom);
            document.addEventListener('keydown', function escapeClose(e) {
                if (e.key === 'Escape') {
                    closeZoom();
                    document.removeEventListener('keydown', escapeClose);
                }
            });
        });
    });

    // 12. Comment Reply Actions Handler (Vanilla JS)
    const replyButtons = document.querySelectorAll('.btn-reply');
    const commentForm = document.getElementById('comment-form');
    if (replyButtons.length > 0 && commentForm) {
        const commentFormLocation = document.getElementById('comment-form-location');
        const defaultAction = commentForm.querySelector('form').getAttribute('action');
        const submitBtn = commentForm.querySelector('button[type="submit"]');

        replyButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const commentId = btn.getAttribute('data-comment-id');
                
                // Add reply indicator class
                commentForm.classList.add('mt-3');
                
                // Append form under the parent comment content block
                const targetLocation = btn.closest('.content');
                targetLocation.appendChild(commentForm);

                // Add or update hidden parent_id input
                let parentIdInput = commentForm.querySelector('input[name="parent_id"]');
                if (!parentIdInput) {
                    parentIdInput = document.createElement('input');
                    parentIdInput.type = 'hidden';
                    parentIdInput.name = 'parent_id';
                    commentForm.querySelector('form').appendChild(parentIdInput);
                }
                parentIdInput.value = commentId;

                // Update form action to replies url
                commentForm.querySelector('form').action = replyUrl; // replyUrl is defined globally in Blade template
                if (submitBtn) submitBtn.textContent = 'Post Reply';

                // Add cancel link if not exists
                let cancelBtn = commentForm.querySelector('.cancel-reply-btn');
                if (!cancelBtn) {
                    cancelBtn = document.createElement('button');
                    cancelBtn.type = 'button';
                    cancelBtn.className = 'btn-secondary cancel-reply-btn';
                    cancelBtn.style.marginLeft = '12px';
                    cancelBtn.textContent = 'Cancel';
                    commentForm.querySelector('.form-group:last-child, button[type="submit"]').after(cancelBtn);
                    
                    cancelBtn.addEventListener('click', () => {
                        // Reset parent_id
                        if (parentIdInput) parentIdInput.remove();
                        // Reset action url
                        commentForm.querySelector('form').action = defaultAction;
                        if (submitBtn) submitBtn.textContent = 'Comment';
                        // Move comment form back to normal location
                        commentFormLocation.appendChild(commentForm);
                        cancelBtn.remove();
                    });
                }
            });
        });
    }
    // 13. Newsletter Form Validation & Mock Submission
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const emailInput = document.getElementById('newsletter-email');
            const errorMsg = document.getElementById('newsletter-error');
            const successMsg = document.getElementById('newsletter-success');
            
            const email = emailInput.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!email) {
                errorMsg.textContent = 'Email address is required.';
                errorMsg.style.display = 'block';
                successMsg.style.display = 'none';
            } else if (!emailRegex.test(email)) {
                errorMsg.textContent = 'Please enter a valid email address.';
                errorMsg.style.display = 'block';
                successMsg.style.display = 'none';
            } else {
                errorMsg.style.display = 'none';
                successMsg.style.display = 'block';
                emailInput.value = '';
                showToast('Successfully subscribed to newsletter!', 'success');
            }
        });
    }

    // 14. Mobile Sidebar Drawer Toggle
    const sidebar = document.querySelector('.oredoo-sidebar');
    if (sidebar) {
        // Create toggle button dynamically
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'sidebar-toggle-btn';
        toggleBtn.id = 'sidebar-toggle-btn';
        toggleBtn.setAttribute('aria-label', 'Toggle Sidebar Widgets');
        toggleBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sliders-horizontal"><line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/><line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/><line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/><line x1="14" x2="14" y1="2" y2="6"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="16" x2="16" y1="18" y2="22"/></svg>`;
        document.body.appendChild(toggleBtn);

        const overlay = document.querySelector('.drawer-overlay');

        const openSidebar = () => {
            sidebar.classList.add('open');
            if (overlay) overlay.classList.add('active');
        };

        const closeSidebar = () => {
            sidebar.classList.remove('open');
            const mobDrawer = document.querySelector('.mobile-drawer');
            if (overlay && (!mobDrawer || !mobDrawer.classList.contains('open'))) {
                overlay.classList.remove('active');
            }
        };

        toggleBtn.addEventListener('click', openSidebar);
        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }
    }
});
