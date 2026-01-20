// Dark / Light theme toggle for index/main pages
(function () {
    const root = document.documentElement;
    const btn = document.getElementById('dayNightMoodBtn');

    if (!btn) return;

    function setTheme(theme) {
        root.setAttribute('data-theme', theme);
        localStorage.setItem('hm_theme', theme);
        btn.textContent = theme === 'light' ? '🌙' : '☀️';
    }

    const saved = localStorage.getItem('hm_theme');
    if (saved === 'light' || saved === 'dark') {
        setTheme(saved);
    } else {
        setTheme('dark');
    }

    btn.addEventListener('click', () => {
        const current = root.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
        setTheme(current === 'light' ? 'dark' : 'light');
    });
})();