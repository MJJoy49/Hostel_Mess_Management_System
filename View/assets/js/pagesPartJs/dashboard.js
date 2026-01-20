// View/assets/js/pagesPartJs/dashboard.js

document.addEventListener('DOMContentLoaded', function () {
    var dashboardSubtitle = document.getElementById('dashboardSubtitle');
    var dashPeriodLabel = document.getElementById('dashPeriodLabel');

    // Top stats
    var stat1Label = document.getElementById('dash_stat1_label');
    var stat1Value = document.getElementById('dash_stat1_value');
    var stat1Sub = document.getElementById('dash_stat1_sub');

    var stat2Label = document.getElementById('dash_stat2_label');
    var stat2Value = document.getElementById('dash_stat2_value');
    var stat2Sub = document.getElementById('dash_stat2_sub');

    var stat3Label = document.getElementById('dash_stat3_label');
    var stat3Value = document.getElementById('dash_stat3_value');
    var stat3Sub = document.getElementById('dash_stat3_sub');

    var stat4Label = document.getElementById('dash_stat4_label');
    var stat4Value = document.getElementById('dash_stat4_value');
    var stat4Sub = document.getElementById('dash_stat4_sub');

    // Overview (left column)
    var overviewGrid = document.getElementById('overviewGrid');

    // Today meals
    var todayMealsColStatus = document.getElementById('todayMealsColStatus');
    var todayMealsTableBody = document.getElementById('todayMealsTableBody');

    // Admin finances
    var finTitle = document.getElementById('finTitle');
    var finMeta = document.getElementById('finMeta');
    var finGrid = document.getElementById('finGrid');

    // Recent activity lists
    var recentAnnouncements = document.getElementById('recentAnnouncements');
    var recentPayments = document.getElementById('recentPayments');
    var recentBazar = document.getElementById('recentBazar');

    var currentRole = 'member';

    function getJSON(url, cb) {
        fetch(url, { credentials: 'same-origin' })
            .then(function (res) { return res.json(); })
            .then(function (data) { cb(null, data); })
            .catch(function (err) { cb(err); });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function setStat(elLabel, elValue, elSub, stat) {
        if (!stat) {
            if (elLabel) elLabel.textContent = '';
            if (elValue) elValue.textContent = '0';
            if (elSub) elSub.textContent = '';
            return;
        }
        if (elLabel) elLabel.textContent = stat.label || '';
        if (elValue) elValue.textContent = stat.value !== undefined ? stat.value : '0';
        if (elSub) elSub.textContent = stat.sub || '';
    }

    function renderOverview(items) {
        if (!overviewGrid) return;
        overviewGrid.innerHTML = '';

        if (!items || !items.length) {
            var p = document.createElement('p');
            p.className = 'empty-text';
            p.textContent = 'No overview data.';
            overviewGrid.appendChild(p);
            return;
        }

        items.forEach(function (item) {
            var div = document.createElement('div');
            div.className = 'overview-item';

            var l = document.createElement('div');
            l.className = 'overview-label';
            l.textContent = item.label || '';

            var v = document.createElement('div');
            v.className = 'overview-value';
            v.textContent = item.value || '0';

            var s = document.createElement('div');
            s.className = 'overview-sub';
            s.textContent = item.sub || '';

            div.appendChild(l);
            div.appendChild(v);
            div.appendChild(s);

            overviewGrid.appendChild(div);
        });
    }

    function renderTodayMeals(rows, colLabel) {
        if (!todayMealsTableBody) return;
        todayMealsTableBody.innerHTML = '';

        if (todayMealsColStatus) {
            todayMealsColStatus.textContent = colLabel || 'Status';
        }

        if (!rows || !rows.length) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 3;
            td.textContent = 'No meals defined for today.';
            tr.appendChild(td);
            todayMealsTableBody.appendChild(tr);
            return;
        }

        rows.forEach(function (m) {
            var tr = document.createElement('tr');
            var statusText = m.status || 'Available';

            tr.innerHTML =
                '<td>' + escapeHtml(m.type || '') + '</td>' +
                '<td>' + escapeHtml(m.menu || '-') + '</td>' +
                '<td>' + escapeHtml(statusText) + '</td>';

            todayMealsTableBody.appendChild(tr);
        });
    }

    function renderFinances(items) {
        if (!finGrid) return;
        finGrid.innerHTML = '';

        if (!items || !items.length) {
            var p = document.createElement('p');
            p.className = 'empty-text';
            p.textContent = 'No financial data.';
            finGrid.appendChild(p);
            return;
        }

        items.forEach(function (item) {
            var div = document.createElement('div');
            div.className = 'overview-item';

            var l = document.createElement('div');
            l.className = 'overview-label';
            l.textContent = item.label || '';

            var v = document.createElement('div');
            v.className = 'overview-value';
            v.textContent = item.value || '0';

            var s = document.createElement('div');
            s.className = 'overview-sub';
            s.textContent = item.sub || '';

            div.appendChild(l);
            div.appendChild(v);
            div.appendChild(s);

            finGrid.appendChild(div);
        });
    }

    function renderRecent(listEl, items) {
        if (!listEl) return;
        listEl.innerHTML = '';

        if (!items || !items.length) {
            var li = document.createElement('li');
            li.className = 'activity-item empty';
            li.textContent = 'No records.';
            listEl.appendChild(li);
            return;
        }

        items.forEach(function (it) {
            var li = document.createElement('li');
            li.className = 'activity-item';
            li.innerHTML =
                '<div class="activity-item-title">' + escapeHtml(it.title || '') + '</div>' +
                '<div class="activity-item-meta">' + escapeHtml(it.subtitle || '') + '</div>';
            listEl.appendChild(li);
        });
    }

    function loadDashboard() {
        getJSON('../Controller/pages/DashboardController.php?action=getData', function (err, data) {
            if (err || !data || !data.success) {
                console.error('Failed to load dashboard', err || data);
                return;
            }

            currentRole = data.role || 'member';

            if (dashboardSubtitle) {
                dashboardSubtitle.textContent = data.period_label || 'Overview';
            }
            if (dashPeriodLabel) {
                dashPeriodLabel.textContent = data.period_label || 'This month';
            }

            // Top stats
            var stats = data.stats || [];
            setStat(stat1Label, stat1Value, stat1Sub, stats[0]);
            setStat(stat2Label, stat2Value, stat2Sub, stats[1]);
            setStat(stat3Label, stat3Value, stat3Sub, stats[2]);
            setStat(stat4Label, stat4Value, stat4Sub, stats[3]);

            // Overview
            renderOverview(data.overview || []);

            // Today meals
            renderTodayMeals(data.today_meals || [], data.today_meals_col || 'Status');

            // Finances (admin section)
            if (finTitle && finMeta) {
                finTitle.textContent = 'Mess Financial Overview';
                finMeta.textContent = 'This month';
            }
            renderFinances(data.finances || []);

            // Recent activity
            var recent = data.recent || {};
            renderRecent(recentAnnouncements, recent.announcements || []);
            renderRecent(recentPayments, recent.payments || []);
            renderRecent(recentBazar, recent.bazar || []);

            // Role-based: hide admin-only blocks for members
            var adminBlocks = document.querySelectorAll('.admin-only');
            if (currentRole !== 'admin') {
                adminBlocks.forEach(function (el) { el.classList.add('hidden'); });
            } else {
                adminBlocks.forEach(function (el) { el.classList.remove('hidden'); });
            }
        });
    }

    loadDashboard();
});