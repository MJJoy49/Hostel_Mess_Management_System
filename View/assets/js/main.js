// View/assets/js/main.js
// Handles settings panel (⚙) open/close

document.addEventListener('DOMContentLoaded', function () {
    var settingsToggleBtn = document.getElementById('settingsToggleBtn');
    var settingsPanel = document.getElementById('settingsPanel');
    var settingsOverlay = document.getElementById('settingsPanelOverlay');
    var settingsCloseBtn = document.getElementById('settingsCloseBtn');

    function openSettings() {
        if (!settingsPanel || !settingsOverlay) return;
        settingsPanel.classList.add('open');
        settingsOverlay.classList.add('open');
    }

    function closeSettings() {
        if (!settingsPanel || !settingsOverlay) return;
        settingsPanel.classList.remove('open');
        settingsOverlay.classList.remove('open');
    }

    if (settingsToggleBtn) {
        settingsToggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (settingsPanel.classList.contains('open')) {
                closeSettings();
            } else {
                openSettings();
            }
        });
    }

    if (settingsCloseBtn) {
        settingsCloseBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            closeSettings();
        });
    }

    if (settingsOverlay) {
        settingsOverlay.addEventListener('click', function () {
            closeSettings();
        });
    }

    // Close on outside click
    document.addEventListener('click', function (e) {
        // if click is outside panel and outside toggle button
        if (
            settingsPanel &&
            settingsPanel.classList.contains('open') &&
            !settingsPanel.contains(e.target) &&
            e.target !== settingsToggleBtn
        ) {
            closeSettings();
        }
    });

    // Close on ESC key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSettings();
        }
    });
});