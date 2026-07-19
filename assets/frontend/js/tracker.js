/**
 * RuangAiTi V3 Analytics - Frontend Page View & Read Time Tracker
 * Isolated from legacy scripts.
 */
(function () {
    'use strict';

    // Helper: Generate UUID v4 for unique visitor tracking
    function generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            const r = (Math.random() * 16) | 0;
            const v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    }

    // Initialize or retrieve visitor_id from LocalStorage
    let visitorId = localStorage.getItem('ruangaiti_visitor_id');
    if (!visitorId) {
        visitorId = generateUUID();
        localStorage.setItem('ruangaiti_visitor_id', visitorId);
    }

    // Initialize or retrieve session_id from SessionStorage
    let sessionId = sessionStorage.getItem('ruangaiti_session_id');
    if (!sessionId) {
        sessionId = generateUUID();
        sessionStorage.setItem('ruangaiti_session_id', sessionId);
    }

    // CSRF Token Helper
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    const currentPath = window.location.pathname;
    const referrer = document.referrer;

    // Detect viewable entity metadata from meta tags (injected in specific views)
    const entityTypeMeta = document.querySelector('meta[name="page-entity-type"]');
    const entityIdMeta = document.querySelector('meta[name="page-entity-id"]');
    const viewableType = entityTypeMeta ? entityTypeMeta.getAttribute('content') : null;
    const viewableId = entityIdMeta ? entityIdMeta.getAttribute('content') : null;

    // ────────────────────────────────────────────────────────────────────────
    // Page View Tracking
    // ────────────────────────────────────────────────────────────────────────
    function trackPageView() {
        const token = getCsrfToken();
        if (!token) return; // CSRF token not ready yet

        const payload = {
            visitor_id: visitorId,
            session_id: sessionId,
            path: currentPath,
            viewable_type: viewableType,
            viewable_id: viewableId ? parseInt(viewableId, 10) : null,
            referrer: referrer
        };

        fetch('/internal-analytics/track', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        }).catch(err => console.warn('PV tracking error:', err));
    }

    // ────────────────────────────────────────────────────────────────────────
    // Active Reading Time Tracker (Heartbeat)
    // ────────────────────────────────────────────────────────────────────────
    const pingIntervalSeconds = 15;
    let pingTimer = null;
    let lastActiveTime = Date.now();

    // Check user activity to detect idle state
    function updateActivity() {
        lastActiveTime = Date.now();
    }

    // Listen to user interaction events to refresh active time
    ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(eventName => {
        document.addEventListener(eventName, updateActivity, { passive: true });
    });

    function startReadTimeTracker() {
        if (pingTimer) return;

        pingTimer = setInterval(() => {
            // Only ping if tab is active/visible and user is not idle (idle limit: 2 minutes)
            const isVisible = document.visibilityState === 'visible';
            const isNotIdle = (Date.now() - lastActiveTime) < 120000; // 120,000 ms = 2 min

            if (isVisible && isNotIdle) {
                const token = getCsrfToken();
                if (!token) return;

                fetch('/internal-analytics/ping', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        visitor_id: visitorId,
                        path: currentPath,
                        seconds: pingIntervalSeconds
                    })
                }).catch(err => console.warn('Heartbeat ping error:', err));
            }
        }, pingIntervalSeconds * 1000);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Search Tracking
    // ────────────────────────────────────────────────────────────────────────
    function trackSearch() {
        const queryMeta = document.querySelector('meta[name="search-query"]');
        const countMeta = document.querySelector('meta[name="search-results-count"]');
        if (!queryMeta) return;

        const token = getCsrfToken();
        if (!token) return;

        const payload = {
            visitor_id: visitorId,
            query: queryMeta.getAttribute('content'),
            results_count: countMeta ? parseInt(countMeta.getAttribute('content'), 10) : 0,
            search_type: 'global',
            page: 1,
            duration_ms: null
        };

        fetch('/internal-analytics/search', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        }).catch(err => console.warn('Search tracking error:', err));
    }

    // Initialize tracking on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            trackPageView();
            trackSearch();
            startReadTimeTracker();
        });
    } else {
        trackPageView();
        trackSearch();
        startReadTimeTracker();
    }
})();
