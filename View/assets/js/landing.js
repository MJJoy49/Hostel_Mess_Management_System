// View/assets/js/landing.js
// Search seat ads by location (address) only

document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('adSearchInput');
    var adCards = document.querySelectorAll('.ad-card');

    if (!searchInput || !adCards.length) return;

    searchInput.addEventListener('input', function () {
        var q = searchInput.value.toLowerCase();

        adCards.forEach(function (card) {
            var loc = (card.getAttribute('data-location') || '').toLowerCase();
            if (!q || loc.indexOf(q) !== -1) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});