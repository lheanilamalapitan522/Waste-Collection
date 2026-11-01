let trackingTimer = null;
let locationRequestPending = false;
const statusElement = document.getElementById('collector-status');
const toggleButton = document.getElementById('collector-toggle');
const permissionSavedKey = 'maubanCollectorLocationPermissionGranted';
const locationOptions = {
    enableHighAccuracy: true,
    maximumAge: 0,
    timeout: 20000
};

function setTrackingButton(isTracking) {
    toggleButton.textContent = isTracking ? 'Stop sharing location' : 'Enable location sharing';
    toggleButton.setAttribute('aria-pressed', isTracking ? 'true' : 'false');
}

async function sharePosition(position) {
    try {
        window.localStorage.setItem(permissionSavedKey, '1');
    } catch (error) {
        // Browser storage can be disabled; the browser's own permission still applies.
    }

    try {
        const response = await fetch('location-update.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                accuracy: position.coords.accuracy
            })
        });
        const responseText = await response.text();
        let result = {};
        try {
            result = responseText ? JSON.parse(responseText) : {};
        } catch (error) {
            result = {};
        }

        if (response.status === 403 && result.code === 'not_on_duty') {
            statusElement.textContent = 'Location permission is saved. Your location is only shared during your assigned active collection schedule.';
            return;
        }
        if (!response.ok) {
            const message = typeof result.error === 'string' ? result.error : 'Server returned HTTP ' + response.status + '.';
            throw new Error(message);
        }

        const accuracy = Number.isFinite(position.coords.accuracy)
            ? ' (GPS accuracy about ' + Math.round(position.coords.accuracy) + ' m)'
            : '';
        statusElement.textContent = 'Location shared for ' + (result.route || 'your assigned route') + ' at ' + new Date().toLocaleTimeString() + accuracy + '.';
    } catch (error) {
        statusElement.textContent = error instanceof TypeError
            ? 'Could not reach the server. Check your internet connection.'
            : (error.message || 'The server could not save this location.');
    } finally {
        locationRequestPending = false;
    }
}

function locationError(error) {
    locationRequestPending = false;
    if (error.code === 1) {
        stopTracking();
        statusElement.textContent = 'Location permission is blocked. Allow location for this site in your browser settings, then return here.';
    } else if (error.code === 2) {
        statusElement.textContent = 'GPS location is unavailable. Turn on your phone location service and try again.';
    } else {
        statusElement.textContent = 'GPS is taking too long to respond. Keep this page open and try again.';
    }
}

function requestLocation() {
    if (locationRequestPending || trackingTimer === null) return;
    locationRequestPending = true;
    navigator.geolocation.getCurrentPosition(sharePosition, locationError, locationOptions);
}

function startTracking() {
    if (!navigator.geolocation) {
        statusElement.textContent = 'This browser does not support location sharing.';
        return;
    }
    if (trackingTimer !== null) return;

    trackingTimer = window.setInterval(requestLocation, 5000);
    setTrackingButton(true);
    statusElement.textContent = 'Getting a fresh, high-accuracy GPS reading... Keep this page open while on duty.';
    requestLocation();
}

function stopTracking() {
    if (trackingTimer !== null) {
        window.clearInterval(trackingTimer);
        trackingTimer = null;
    }
    setTrackingButton(false);
}

toggleButton.addEventListener('click', function() {
    if (trackingTimer !== null) {
        stopTracking();
        statusElement.textContent = 'Location sharing is stopped on this page.';
        return;
    }
    startTracking();
});

async function resumeIfPermissionIsSaved() {
    if (!navigator.geolocation) {
        statusElement.textContent = 'This browser does not support location sharing.';
        toggleButton.disabled = true;
        return;
    }

    let permissionWasPreviouslyGranted = false;
    try {
        permissionWasPreviouslyGranted = window.localStorage.getItem(permissionSavedKey) === '1';
    } catch (error) {
        // Continue with the browser permission API when local storage is unavailable.
    }

    if (!navigator.permissions || !navigator.permissions.query) {
        if (permissionWasPreviouslyGranted) {
            startTracking();
        } else {
            statusElement.textContent = 'Enable location sharing once and allow the browser permission. It can then start automatically on future visits.';
        }
        return;
    }

    try {
        const permission = await navigator.permissions.query({name: 'geolocation'});
        if (permission.state === 'granted' || (permission.state === 'prompt' && permissionWasPreviouslyGranted)) {
            startTracking();
        } else if (permission.state === 'denied') {
            statusElement.textContent = 'Location permission is blocked. Allow location for this site in your browser settings, then return here.';
        } else {
            statusElement.textContent = 'Enable location sharing once and allow the browser permission. It can then start automatically on future visits.';
        }
    } catch (error) {
        statusElement.textContent = 'Enable location sharing once and allow the browser permission. It can then start automatically on future visits.';
    }
}

resumeIfPermissionIsSaved();
