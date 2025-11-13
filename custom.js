// custom.js
document.addEventListener('DOMContentLoaded', function() {
    // Auto-accept geolocation permissions
    navigator.permissions.query({name: 'geolocation'}).then(function(result) {
        console.log('Geolocation permission:', result.state);
    });
});