// ===== Waste Collection — Mauban, Quezon | Main Script =====

document.addEventListener('DOMContentLoaded', function() {
    console.log('♻ Mauban Waste Collection — System Loaded');

    // ===== Password Toggle =====
    const eyeBtn = document.getElementById('eyeBtn');
    const passwordField = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (eyeBtn && passwordField) {
        eyeBtn.addEventListener('click', function() {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                if (eyeIcon) eyeIcon.src = 'images/eye-off-icon.png';
            } else {
                passwordField.type = 'password';
                if (eyeIcon) eyeIcon.src = 'images/eye-icon.png';
            }
        });
    }

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