/**
 * Peanut Connect Tracker
 *
 * Lightweight visitor and event tracking for Peanut Hub.
 *
 * @package Peanut_Connect
 * @since 2.3.0
 */

(function() {
    'use strict';

    // Exit if not configured
    if (typeof peanutConnectTracker === 'undefined') {
        return;
    }

    const config = peanutConnectTracker;
    let visitorId = config.visitorId;

    /**
     * Set visitor ID cookie
     */
    function setVisitorCookie() {
        const expires = new Date();
        expires.setTime(expires.getTime() + (config.cookieExpiry * 1000));
        document.cookie = `${config.cookieName}=${visitorId};expires=${expires.toUTCString()};path=/;SameSite=Lax`;
    }

    /**
     * Get visitor ID from cookie
     */
    function getVisitorFromCookie() {
        const match = document.cookie.match(new RegExp('(^| )' + config.cookieName + '=([^;]+)'));
        return match ? match[2] : null;
    }

    /**
     * Track an event via REST API
     */
    function trackEvent(eventType, eventData = {}) {
        const data = {
            visitor_id: visitorId,
            event_type: eventType,
            page_url: window.location.href,
            page_title: document.title,
            referrer: document.referrer || null,
            ...eventData,
            occurred_at: new Date().toISOString(),
        };

        // Add UTM params if present
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('utm_source')) data.utm_source = urlParams.get('utm_source');
        if (urlParams.has('utm_medium')) data.utm_medium = urlParams.get('utm_medium');
        if (urlParams.has('utm_campaign')) data.utm_campaign = urlParams.get('utm_campaign');
        if (urlParams.has('utm_term')) data.utm_term = urlParams.get('utm_term');
        if (urlParams.has('utm_content')) data.utm_content = urlParams.get('utm_content');

        // Use sendBeacon for reliability (especially on page unload)
        const url = `${config.restUrl}/track`;
        const payload = JSON.stringify(data);

        if (navigator.sendBeacon) {
            const blob = new Blob([payload], { type: 'application/json' });
            navigator.sendBeacon(url, blob);
        } else {
            // Fallback to fetch
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': config.nonce,
                },
                body: payload,
                keepalive: true,
            }).catch(() => {});
        }
    }

    /**
     * Track scroll depth
     */
    function trackScrollDepth() {
        let maxScroll = 0;
        let tracked25 = false;
        let tracked50 = false;
        let tracked75 = false;
        let tracked100 = false;

        function getScrollPercent() {
            const h = document.documentElement;
            const b = document.body;
            const st = 'scrollTop';
            const sh = 'scrollHeight';
            return Math.round((h[st] || b[st]) / ((h[sh] || b[sh]) - h.clientHeight) * 100);
        }

        window.addEventListener('scroll', function() {
            const percent = getScrollPercent();
            if (percent > maxScroll) {
                maxScroll = percent;

                if (!tracked25 && percent >= 25) {
                    tracked25 = true;
                    trackEvent('scroll', { metadata: { depth: 25 } });
                }
                if (!tracked50 && percent >= 50) {
                    tracked50 = true;
                    trackEvent('scroll', { metadata: { depth: 50 } });
                }
                if (!tracked75 && percent >= 75) {
                    tracked75 = true;
                    trackEvent('scroll', { metadata: { depth: 75 } });
                }
                if (!tracked100 && percent >= 100) {
                    tracked100 = true;
                    trackEvent('scroll', { metadata: { depth: 100 } });
                }
            }
        }, { passive: true });
    }

    /**
     * Track outbound link clicks
     */
    function trackOutboundLinks() {
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;

            const href = link.getAttribute('href');
            if (!href) return;

            // Check if external link
            try {
                const url = new URL(href, window.location.origin);
                if (url.hostname !== window.location.hostname) {
                    trackEvent('outbound_click', {
                        metadata: {
                            url: href,
                            text: link.textContent?.substring(0, 100),
                        },
                    });
                }
            } catch (e) {
                // Invalid URL, ignore
            }
        });
    }

    /**
     * Track form submissions
     */
    function trackFormSubmissions() {
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form || form.tagName !== 'FORM') return;

            // Get form identifier
            const formId = form.id || form.getAttribute('name') || 'unknown';
            const formAction = form.getAttribute('action') || window.location.href;

            trackEvent('form_submit', {
                metadata: {
                    form_id: formId,
                    form_action: formAction,
                },
            });
        });
    }

    /**
     * Track time on page
     */
    function trackTimeOnPage() {
        const startTime = Date.now();

        window.addEventListener('beforeunload', function() {
            const timeOnPage = Math.round((Date.now() - startTime) / 1000);
            trackEvent('time_on_page', {
                metadata: {
                    seconds: timeOnPage,
                },
            });
        });
    }

    /**
     * Identify visitor
     */
    window.peanutConnect = window.peanutConnect || {};
    window.peanutConnect.identify = function(email, name = null, properties = {}) {
        fetch(`${config.restUrl}/identify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': config.nonce,
            },
            body: JSON.stringify({
                visitor_id: visitorId,
                email: email,
                name: name,
                properties: properties,
            }),
        }).catch(() => {});
    };

    /**
     * Track custom event
     */
    window.peanutConnect.track = function(eventType, properties = {}) {
        trackEvent(eventType, { metadata: properties });
    };

    /**
     * Track conversion
     */
    window.peanutConnect.conversion = function(type, value = null, properties = {}) {
        fetch(`${config.restUrl}/conversion`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': config.nonce,
            },
            body: JSON.stringify({
                visitor_id: visitorId,
                type: type,
                value: value,
                ...properties,
            }),
        }).catch(() => {});
    };

    /**
     * Initialize
     */
    function init() {
        // Ensure cookie is set
        if (!getVisitorFromCookie()) {
            setVisitorCookie();
        }

        // Enable tracking features
        trackScrollDepth();
        trackOutboundLinks();
        trackFormSubmissions();
        trackTimeOnPage();
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
