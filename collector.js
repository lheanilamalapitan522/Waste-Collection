let watchId = null;
const statusElement = document.getElementById('collector-status');
const toggleButton = document.getElementById('collector-toggle');

function updateLocation(position) {
    fetch('location-update.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy
        })
    }).then(function(response) {
        if (!response.ok) {
            throw new Error('Location update failed');
        }
        statusElement.textContent = 'Location shared at ' + new Date().toLocaleTimeString();
    }).catch(function() {
        statusElement.textContent = 'Unable to send location. Check your connection.';
    });
}

function locationError(error) {
    statusElement.textContent = error.code === 1
        ? 'Location permission was denied.'
        : 'Your location is currently unavailable.';
}

toggleButton.addEventListener('click', function() {
    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
        watchId = null;
        toggleButton.textContent = 'Start sharing location';
        statusElement.textContent = 'Tracking is stopped.';
        return;
    }

    if (!navigator.geolocation) {
        statusElement.textContent = 'This browser does not support location sharing.';
        return;
    }

    watchId = navigator.geolocation.watchPosition(updateLocation, locationError, {
        enableHighAccuracy: true,
        maximumAge: 10000,
        timeout: 15000
    });
    toggleButton.textContent = 'Stop sharing location';
    statusElement.textContent = 'Waiting for your location...';
});
