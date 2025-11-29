/**
 * Cursor-following orb effect for dark sections
 * Works on hero sections, CTA sections, and footers
 */
(function() {
    'use strict';

    const ORB_SIZE = 400;
    const EASING_SPEED = 0.08;

    function initCursorOrbs() {
        // Find all sections that should have cursor orbs
        // Include various hero variants used across different pages
        const sections = document.querySelectorAll('.hero, .community-hero, .upgrade-hero, .contact-hero, .cta-section, .contact-section, .footer');

        sections.forEach(section => {
            // Check if section already has a cursor orb
            if (section.querySelector('.cursor-orb')) return;

            // Create the orb element
            const orb = document.createElement('div');
            orb.className = 'hero-gradient-orb cursor-orb';

            // Find the background container or create positioning context
            let container = section.querySelector('.hero-bg, .footer-bg');
            if (!container) {
                // For sections without a bg container, add orb directly
                // but ensure section has relative positioning
                section.style.position = 'relative';
                container = section;
            }

            container.appendChild(orb);

            // Track mouse position and animate
            let mouseX = 0, mouseY = 0;
            let orbX = 0, orbY = 0;
            let isActive = false;
            let animationId = null;

            function onMouseEnter(e) {
                // Immediately position orb at cursor to prevent jump
                const rect = container.getBoundingClientRect();
                mouseX = e.clientX - rect.left - (ORB_SIZE / 2);
                mouseY = e.clientY - rect.top - (ORB_SIZE / 2);
                // Snap orb to cursor position instantly
                orbX = mouseX;
                orbY = mouseY;
                orb.style.transform = `translate(${orbX}px, ${orbY}px)`;

                isActive = true;
                orb.classList.add('active');
                if (!animationId) {
                    animateOrb();
                }
            }

            function onMouseLeave() {
                isActive = false;
                orb.classList.remove('active');
            }

            function onMouseMove(e) {
                // Calculate position relative to the container where orb is placed
                const rect = container.getBoundingClientRect();
                mouseX = e.clientX - rect.left - (ORB_SIZE / 2);
                mouseY = e.clientY - rect.top - (ORB_SIZE / 2);
            }

            function animateOrb() {
                orbX += (mouseX - orbX) * EASING_SPEED;
                orbY += (mouseY - orbY) * EASING_SPEED;
                orb.style.transform = `translate(${orbX}px, ${orbY}px)`;

                animationId = requestAnimationFrame(animateOrb);
            }

            // Attach event listeners
            section.addEventListener('mouseenter', onMouseEnter);
            section.addEventListener('mouseleave', onMouseLeave);
            section.addEventListener('mousemove', onMouseMove);

            // Start animation loop
            animateOrb();
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCursorOrbs);
    } else {
        initCursorOrbs();
    }
})();
