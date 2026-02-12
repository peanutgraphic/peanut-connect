/**
 * Peanut Connect Event Banner JavaScript
 *
 * Handles dynamic banner height calculation and auto-hide functionality.
 *
 * @since 3.3.0
 */

(function() {
    'use strict';

    // Wait for DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        var banner = document.querySelector('.peanut-event-banner');
        if (!banner) {
            return;
        }

        // Calculate and set banner height CSS variable
        setBannerHeight(banner);

        // Recalculate on resize
        var resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                setBannerHeight(banner);
            }, 100);
        });

        // Set up auto-hide timer if hide time is specified
        if (typeof peanutEventBanner !== 'undefined' && peanutEventBanner.hideAt) {
            setupAutoHide(banner, peanutEventBanner.hideAt);
        }

        // Handle close button (if present)
        var closeButton = banner.querySelector('.peanut-event-banner__close');
        if (closeButton) {
            closeButton.addEventListener('click', function(e) {
                e.preventDefault();
                hideBanner(banner);
            });
        }
    }

    /**
     * Calculate and set banner height CSS variable
     */
    function setBannerHeight(banner) {
        var height = banner.offsetHeight;
        document.documentElement.style.setProperty('--peanut-banner-height', height + 'px');

        // Mobile height (might be different due to wrapping)
        if (window.innerWidth <= 768) {
            document.documentElement.style.setProperty('--peanut-banner-height-mobile', height + 'px');
        }
    }

    /**
     * Set up auto-hide based on hide time
     */
    function setupAutoHide(banner, hideAt) {
        var hideTime = new Date(hideAt).getTime();
        var now = Date.now();
        var delay = hideTime - now;

        if (delay > 0) {
            setTimeout(function() {
                hideBanner(banner);
            }, delay);
        } else if (delay <= 0) {
            // Already past hide time, hide immediately
            hideBanner(banner);
        }
    }

    /**
     * Hide the banner with animation
     */
    function hideBanner(banner) {
        var position = banner.classList.contains('peanut-event-banner--fixed-bottom') ? 'bottom' : 'top';

        // Add hide animation
        banner.style.transition = 'transform 0.3s ease-in, opacity 0.3s ease-in';
        banner.style.transform = position === 'top' ? 'translateY(-100%)' : 'translateY(100%)';
        banner.style.opacity = '0';

        // Remove after animation
        setTimeout(function() {
            banner.remove();

            // Remove body class
            document.body.classList.remove('has-peanut-banner-top');
            document.body.classList.remove('has-peanut-banner-bottom');
            document.body.classList.remove('has-peanut-banner-fixed-top');
            document.body.classList.remove('has-peanut-banner-fixed-bottom');

            // Reset CSS variable
            document.documentElement.style.removeProperty('--peanut-banner-height');
            document.documentElement.style.removeProperty('--peanut-banner-height-mobile');
        }, 300);
    }
})();
