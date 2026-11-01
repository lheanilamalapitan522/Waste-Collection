(function () {
    let currentVersion = null;

    async function checkForChanges() {
        try {
            const response = await fetch('dev-version.php', { cache: 'no-store' });
            if (!response.ok) return;

            const nextVersion = await response.text();
            if (currentVersion !== null && nextVersion !== currentVersion) {
                window.location.reload();
                return;
            }
            currentVersion = nextVersion;
        } catch (error) {
            // The development server may restart while files are being changed.
        }
    }

    checkForChanges();
    window.setInterval(checkForChanges, 1500);
})();
