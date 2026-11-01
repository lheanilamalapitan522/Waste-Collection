let trackingTimer = null;
let locationRequestPending = false;
const statusElement = document.getElementById('collector-status');
const toggleButton = document.getElementById('collector-toggle');
const locationOptions = {
    enableHighAccuracy: true,
    maximumAge: 5000,
    timeout: 15000
};

async function sharePosition(position) {
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
        const result = await response.json().catch(function() { return {}; });
        if (response.status === 403 && result.code === 'not_on_duty') {
            statusElement.textContent = 'GPS permission may remain enabled, but your location is not shared outside your assigned collection schedule.';
            return;
        }
        if (!response.ok) throw new Error('Location update failed');
        statusElement.textContent = 'Location shared for ' + (result.route || 'your assigned route') + ' at ' + new Date().toLocaleTimeString();
    } catch (error) {
        statusElement.textContent = 'Unable to send location. Check your connection.';
    } finally {
        locationRequestPending = false;
    }
}

function locationError(error) {
    locationRequestPending = false;
    statusElement.textContent = error.code === 1
        ? 'Location permission was denied.'
        : 'Your location is currently unavailable.';
}

function requestLocation() {
    if (locationRequestPending || trackingTimer === null) return;
    locationRequestPending = true;
    navigator.geolocation.getCurrentPosition(sharePosition, locationError, locationOptions);
}

toggleButton.addEventListener('click', function() {
    if (trackingTimer !== null) {
        window.clearInterval(trackingTimer);
        trackingTimer = null;
        toggleButton.textContent = 'Start sharing location';
        statusElement.textContent = 'Tracking is stopped.';
        return;
    }

    if (!navigator.geolocation) {
        statusElement.textContent = 'This browser does not support location sharing.';
        return;
    }

    trackingTimer = window.setInterval(requestLocation, 5000);
    toggleButton.textContent = 'Stop sharing location';
    statusElement.textContent = 'Waiting for your location...';
    requestLocation();
});
