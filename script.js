// ===== Waste Collection — Mauban, Quezon | Main Script =====

document.addEventListener('DOMContentLoaded', function() {
    console.log('♻ Mauban Waste Collection — System Loaded');

    // ===== Password Toggle =====
    document.querySelectorAll('.password-toggle').forEach(function(toggle) {
        const field = document.getElementById(toggle.getAttribute('aria-controls'));
        if (!field) return;

        toggle.addEventListener('click', function() {
            const showPassword = field.type === 'password';
            field.type = showPassword ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', String(showPassword));
            toggle.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
            toggle.classList.toggle('is-visible', showPassword);
        });
    });

    // ===== Map Zoom Controls =====
    const zoomBtns = document.querySelectorAll('.map-btn');
    const mapArea = document.querySelector('.map-area');
    let currentZoom = 1;

    zoomBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.textContent === '+') {
                currentZoom = Math.min(currentZoom + 0.1, 2);
            } else if (this.textContent === '−' || this.textContent === '-') {
                currentZoom = Math.max(currentZoom - 0.1, 0.5);
            }
            if (mapArea) {
                mapArea.style.transform = `scale(${currentZoom})`;
                mapArea.style.transformOrigin = 'center center';
            }
        });
    });
});
