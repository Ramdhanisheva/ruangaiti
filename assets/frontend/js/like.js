/**
 * RuangAiTi V3 Engagement - Bookmarks, Likes, Feedback, and Learning Progress Tracker
 * Uses client-side LocalStorage to optimize performance and prevent DB bloat.
 */
(function () {
    'use strict';

    // Retrieve or initialize visitor ID
    let visitorId = localStorage.getItem('ruangaiti_visitor_id');
    if (!visitorId) {
        visitorId = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            const r = (Math.random() * 16) | 0;
            const v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
        localStorage.setItem('ruangaiti_visitor_id', visitorId);
    }

    // Helper: CSRF token
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // ────────────────────────────────────────────────────────────────────────
    // 1. Likes & Helpful / Not Helpful Feedback
    // ────────────────────────────────────────────────────────────────────────
    const storageLikesKey = 'ruangaiti_likes';
    const storageFeedbackKey = 'ruangaiti_feedback';

    function getStoredLikes() {
        try {
            return JSON.parse(localStorage.getItem(storageLikesKey)) || {};
        } catch {
            return {};
        }
    }

    function saveStoredLikes(likes) {
        localStorage.setItem(storageLikesKey, JSON.stringify(likes));
    }

    function getStoredFeedback() {
        try {
            return JSON.parse(localStorage.getItem(storageFeedbackKey)) || {};
        } catch {
            return {};
        }
    }

    function saveStoredFeedback(feedback) {
        localStorage.setItem(storageFeedbackKey, JSON.stringify(feedback));
    }

    // Bind event handlers for Likes and Feedback buttons
    document.addEventListener('DOMContentLoaded', () => {
        setupLikesUI();
        setupFeedbackUI();
        setupBookmarksUI();
        recordRecentlyViewed();
    });

    function updateElementCount(el, delta) {
        const countEl = el.querySelector('.like-count, .helpful-yes-count, .helpful-no-count') || el;
        const text = countEl.textContent;
        const match = text.match(/\d+/);
        if (match) {
            const currentCount = parseInt(match[0], 10);
            const newCount = Math.max(0, currentCount + delta);
            countEl.textContent = text.replace(/\d+/, newCount);
        }
    }

    function setupLikesUI() {
        const likeBtns = document.querySelectorAll('[data-action="like"]');
        if (likeBtns.length === 0) return;

        const storedLikes = getStoredLikes();

        // 1. Initial UI state sync
        likeBtns.forEach(btn => {
            const type = btn.getAttribute('data-type');
            const id = btn.getAttribute('data-id');
            const storageKey = `${type}_${id}`;

            if (storedLikes[storageKey]) {
                btn.classList.add('liked');
                const icon = btn.querySelector('i, svg');
                if (icon) icon.style.fill = 'var(--accent-red)';
                const label = btn.querySelector('.like-label');
                if (label) label.textContent = 'Liked';
            }
        });

        // 2. Click handler registration
        likeBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const token = getCsrfToken();
                if (!token) return;

                const type = btn.getAttribute('data-type');
                const id = btn.getAttribute('data-id');
                const storageKey = `${type}_${id}`;

                fetch('/internal-analytics/feedback', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        visitor_id: visitorId,
                        likeable_type: type,
                        likeable_id: parseInt(id, 10),
                        type: 'like'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const likes = getStoredLikes();
                        const delta = (data.action === 'added') ? 1 : -1;

                        if (data.action === 'added') {
                            likes[storageKey] = true;
                            // Update all elements with the same key
                            document.querySelectorAll(`[data-action="like"][data-type="${type}"][data-id="${id}"]`).forEach(b => {
                                b.classList.add('liked');
                                const label = b.querySelector('.like-label');
                                if (label) label.textContent = 'Liked';
                                updateElementCount(b, delta);
                            });
                        } else {
                            delete likes[storageKey];
                            document.querySelectorAll(`[data-action="like"][data-type="${type}"][data-id="${id}"]`).forEach(b => {
                                b.classList.remove('liked');
                                const label = b.querySelector('.like-label');
                                if (label) label.textContent = 'Like';
                                updateElementCount(b, delta);
                            });
                        }
                        saveStoredLikes(likes);
                    }
                })
                .catch(err => console.error('Feedback like error:', err));
            });
        });
    }

    function setupFeedbackUI() {
        const yesBtns = document.querySelectorAll('[data-action="helpful-yes"]');
        const noBtns = document.querySelectorAll('[data-action="helpful-no"]');
        if (yesBtns.length === 0 && noBtns.length === 0) return;

        const storedFeedback = getStoredFeedback();

        // 1. Initial UI state sync
        yesBtns.forEach(btn => {
            const type = btn.getAttribute('data-type');
            const id = btn.getAttribute('data-id');
            const storageKey = `${type}_${id}`;
            if (storedFeedback[storageKey] === 'yes') {
                btn.classList.add('active');
            }
        });
        noBtns.forEach(btn => {
            const type = btn.getAttribute('data-type');
            const id = btn.getAttribute('data-id');
            const storageKey = `${type}_${id}`;
            if (storedFeedback[storageKey] === 'no') {
                btn.classList.add('active');
            }
        });

        // Helper to perform feedback submission
        function handleFeedback(btn, voteType) {
            const token = getCsrfToken();
            if (!token) return;

            const type = btn.getAttribute('data-type');
            const id = btn.getAttribute('data-id');
            const storageKey = `${type}_${id}`;

            fetch('/internal-analytics/feedback', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    visitor_id: visitorId,
                    likeable_type: type,
                    likeable_id: parseInt(id, 10),
                    type: voteType === 'yes' ? 'helpful_yes' : 'helpful_no'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const feedback = getStoredFeedback();
                    const prevVote = feedback[storageKey];

                    // Find all matching buttons on page to keep them in sync
                    const allYes = document.querySelectorAll(`[data-action="helpful-yes"][data-type="${type}"][data-id="${id}"]`);
                    const allNo = document.querySelectorAll(`[data-action="helpful-no"][data-type="${type}"][data-id="${id}"]`);

                    if (data.action === 'added') {
                        feedback[storageKey] = voteType;
                        
                        if (voteType === 'yes') {
                            allYes.forEach(b => {
                                b.classList.add('active');
                                updateElementCount(b, 1);
                            });
                            if (prevVote === 'no') {
                                allNo.forEach(b => {
                                    b.classList.remove('active');
                                    updateElementCount(b, -1);
                                });
                            }
                        } else {
                            allNo.forEach(b => {
                                b.classList.add('active');
                                updateElementCount(b, 1);
                            });
                            if (prevVote === 'yes') {
                                allYes.forEach(b => {
                                    b.classList.remove('active');
                                    updateElementCount(b, -1);
                                });
                            }
                        }
                    } else {
                        delete feedback[storageKey];
                        if (voteType === 'yes') {
                            allYes.forEach(b => {
                                b.classList.remove('active');
                                updateElementCount(b, -1);
                            });
                        } else {
                            allNo.forEach(b => {
                                b.classList.remove('active');
                                updateElementCount(b, -1);
                            });
                        }
                    }
                    saveStoredFeedback(feedback);
                }
            })
            .catch(err => console.error('Feedback helpful rate error:', err));
        }

        yesBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                handleFeedback(btn, 'yes');
            });
        });

        noBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                handleFeedback(btn, 'no');
            });
        });
    }

    // ────────────────────────────────────────────────────────────────────────
    // 2. Bookmarks System (LocalStorage-based)
    // ────────────────────────────────────────────────────────────────────────
    const storageBookmarksKey = 'ruangaiti_bookmarks';

    function getBookmarks() {
        try {
            return JSON.parse(localStorage.getItem(storageBookmarksKey)) || [];
        } catch {
            return [];
        }
    }

    function saveBookmarks(bookmarks) {
        localStorage.setItem(storageBookmarksKey, JSON.stringify(bookmarks));
    }

    function setupBookmarksUI() {
        const bookmarkBtn = document.querySelector('[data-action="bookmark"]');
        if (!bookmarkBtn) return;

        const type = bookmarkBtn.getAttribute('data-type');
        const id = bookmarkBtn.getAttribute('data-id');
        const title = bookmarkBtn.getAttribute('data-title') || document.title;
        const url = window.location.pathname;

        let bookmarks = getBookmarks();
        const isBookmarked = bookmarks.some(b => b.type === type && b.id == id);

        if (isBookmarked) {
            bookmarkBtn.classList.add('bookmarked');
            const label = bookmarkBtn.querySelector('.bookmark-label');
            if (label) label.textContent = 'Bookmarked';
        }

        bookmarkBtn.addEventListener('click', (e) => {
            e.preventDefault();
            bookmarks = getBookmarks();
            const index = bookmarks.findIndex(b => b.type === type && b.id == id);

            if (index > -1) {
                // Remove bookmark
                bookmarks.splice(index, 1);
                bookmarkBtn.classList.remove('bookmarked');
                const label = bookmarkBtn.querySelector('.bookmark-label');
                if (label) label.textContent = 'Bookmark';
            } else {
                // Add bookmark
                bookmarks.push({
                    type: type,
                    id: id,
                    title: title,
                    url: url,
                    timestamp: Date.now()
                });
                bookmarkBtn.classList.add('bookmarked');
                const label = bookmarkBtn.querySelector('.bookmark-label');
                if (label) label.textContent = 'Bookmarked';
            }
            saveBookmarks(bookmarks);
        });
    }

    // ────────────────────────────────────────────────────────────────────────
    // 3. Recently Viewed System (LocalStorage-based)
    // ────────────────────────────────────────────────────────────────────────
    const storageRecentKey = 'ruangaiti_recent';

    function recordRecentlyViewed() {
        const typeMeta = document.querySelector('meta[name="page-entity-type"]');
        const idMeta = document.querySelector('meta[name="page-entity-id"]');
        if (!typeMeta || !idMeta) return;

        const type = typeMeta.getAttribute('content');
        const id = idMeta.getAttribute('content');
        const title = document.title;
        const url = window.location.pathname;

        try {
            let recent = JSON.parse(localStorage.getItem(storageRecentKey)) || [];
            // Remove duplicates
            recent = recent.filter(item => !(item.type === type && item.id == id));
            // Add to front
            recent.unshift({
                type: type,
                id: id,
                title: title,
                url: url,
                timestamp: Date.now()
            });
            // Keep last 5
            if (recent.length > 5) {
                recent.pop();
            }
            localStorage.setItem(storageRecentKey, JSON.stringify(recent));
        } catch (e) {
            console.warn('Recently viewed error:', e);
        }
    }
})();
