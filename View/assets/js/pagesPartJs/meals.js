// View/assets/js/pagesPartJs/meals.js

document.addEventListener('DOMContentLoaded', function () {
    var mealsRateLabel = document.getElementById('mealsRateLabel');
    var mealsSubtitle = document.getElementById('mealsSubtitle');

    var statTodayCount = document.getElementById('meals_stat_today_count');
    var statMyMonth = document.getElementById('meals_stat_my_month');
    var statMyDue = document.getElementById('meals_stat_my_due');

    var summaryTotalMeals = document.getElementById('summary_total_meals');
    var summaryMealRate = document.getElementById('summary_meal_rate');
    var summaryEstimatedCost = document.getElementById('summary_estimated_cost');

    var attendanceDateLabel = document.getElementById('attendanceDateLabel');
    var attendanceDateFilter = document.getElementById('attendanceDateFilter');
    var attendanceTableBody = document.getElementById('attendanceTableBody');

    var todayMealsTitle = document.getElementById('todayMealsTitle');
    var todayMealsDate = document.getElementById('todayMealsDate');
    var todayMealsTableBody = document.getElementById('todayMealsTableBody');

    var bazarHistoryMeta = document.getElementById('bazarHistoryMeta');
    var bazarDateFilter = document.getElementById('bazarDateFilter');
    var bazarClearFilterBtn = document.getElementById('bazarClearFilterBtn');
    var bazarHistoryTableBody = document.getElementById('bazarHistoryTableBody');

    var mealsViewFilter = document.getElementById('mealsViewFilter');
    var addMealBtn = document.getElementById('addMealBtn');

    var currentDate = new Date().toISOString().slice(0, 10); // YYYY-MM-DD
    var currentRole = 'member';

    function getJSON(url, cb) {
        fetch(url, { credentials: 'same-origin' })
            .then(function (res) { return res.json(); })
            .then(function (data) { cb(null, data); })
            .catch(function (err) { cb(err); });
    }

    function postJSON(url, formData, cb) {
        fetch(url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(function (res) { return res.json(); })
            .then(function (data) { cb(null, data); })
            .catch(function (err) { cb(err); });
    }

    function loadMealsData(date, bazarDate) {
        date = date || currentDate;
        var url = '../Controller/pages/MealsController.php?action=getData&date=' + encodeURIComponent(date);

        if (bazarDate) {
            url += '&bazar_date=' + encodeURIComponent(bazarDate);
        }

        getJSON(url, function (err, data) {
            if (err || !data.success) {
                console.error('Failed to load meals data', err || data);
                return;
            }

            currentDate = data.attendance_date || date;
            currentRole = data.role || 'member';

            // Header
            mealsRateLabel.textContent = '৳ ' + parseFloat(data.summary.meal_rate).toFixed(2);
            mealsSubtitle.textContent = 'Meals overview for ' + currentDate;

            // Stats
            statTodayCount.textContent = data.stats.today_meals;
            statMyMonth.textContent = data.stats.my_meals_month;
            statMyDue.textContent = '৳ ' + parseFloat(data.stats.my_meal_due).toFixed(1);

            // Summary
            summaryTotalMeals.textContent = data.summary.total_meals;
            summaryMealRate.textContent = '৳ ' + parseFloat(data.summary.meal_rate).toFixed(2);
            summaryEstimatedCost.textContent = '৳ ' + parseFloat(data.summary.estimated_cost).toFixed(2);

            // Attendance
            attendanceDateLabel.textContent = data.attendance_date;
            if (!attendanceDateFilter.value) {
                attendanceDateFilter.value = data.attendance_date;
            }
            renderAttendanceTable(data.attendance_rows || []);

            // Today meals
            todayMealsTitle.textContent = 'Today Meals';
            todayMealsDate.textContent = data.attendance_date;
            renderTodayMeals(data.todayMeals || []);

            // Bazar history
            bazarHistoryMeta.textContent = data.bazar.rows.length + ' records';
            renderBazarHistory(data.bazar.rows || []);

            // Role-based UI: Hide/Show Add Meal Button
            if (currentRole !== 'admin') {
                if (addMealBtn) addMealBtn.style.display = 'none';
            } else {
                if (addMealBtn) addMealBtn.style.display = 'inline-flex';
            }
        });
    }

    function renderAttendanceTable(rows) {
        attendanceTableBody.innerHTML = '';

        rows.forEach(function (row) {
            var tr = document.createElement('tr');

            var tdName = document.createElement('td');
            tdName.textContent = row.full_name;
            tr.appendChild(tdName);

            ['breakfast', 'lunch', 'dinner'].forEach(function (type) {
                var td = document.createElement('td');
                var info = row[type];

                if (info.meal_id === null) {
                    td.textContent = '-'; // No meal scheduled
                } else {
                    var label = info.attended ? 'Ate' : 'Skipped';
                    var cls = info.attended ? 'badge badge-green badge-att' : 'badge badge-gray badge-att';

                    if (currentRole === 'admin') {
                        // Admin gets a toggle button
                        var btn = document.createElement('button');
                        btn.className = 'att-toggle-btn'; // We can style this to look like a badge or button
                        // To reuse existing badge styles in a button:
                        btn.innerHTML = '<span class="' + cls + '">' + label + '</span>';
                        // Button style reset
                        btn.style.background = 'transparent';
                        btn.style.border = 'none';
                        btn.style.cursor = 'pointer';
                        btn.style.padding = '0';

                        btn.dataset.userId = row.user_id;
                        btn.dataset.type = type;
                        btn.dataset.date = currentDate;

                        // Add click listener directly here or delegate later
                        btn.addEventListener('click', function (e) {
                            e.stopPropagation(); // prevent row click if any
                            handleToggle(row.user_id, type, currentDate);
                        });

                        td.appendChild(btn);
                    } else {
                        // Member gets just text/badge
                        var span = document.createElement('span');
                        span.className = cls;
                        span.textContent = label;
                        td.appendChild(span);
                    }
                }
                tr.appendChild(td);
            });

            attendanceTableBody.appendChild(tr);
        });
    }

    function handleToggle(userId, type, date) {
        if (currentRole !== 'admin') return;

        var fd = new FormData();
        fd.append('user_id', userId);
        fd.append('type', type);
        fd.append('date', date);

        postJSON('../Controller/pages/MealsController.php?action=toggleAttendance', fd, function (err, data) {
            if (err || !data.success) {
                alert((data && data.message) || 'Failed to update attendance');
                return;
            }
            // Reload data to reflect stats and changes
            loadMealsData(date, bazarDateFilter.value || '');
        });
    }

    function renderTodayMeals(meals) {
        todayMealsTableBody.innerHTML = '';
        if (!meals.length) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 3;
            td.textContent = 'No meals defined for today.';
            tr.appendChild(td);
            todayMealsTableBody.appendChild(tr);
            return;
        }

        meals.forEach(function (m) {
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + escapeHtml(m.meal_type) + '</td>' +
                '<td>' + escapeHtml(m.menu || '-') + '</td>' +
                '<td><span class="badge badge-green">Yes</span></td>';
            todayMealsTableBody.appendChild(tr);
        });
    }

    function renderBazarHistory(rows) {
        bazarHistoryTableBody.innerHTML = '';
        if (!rows.length) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 4;
            td.textContent = 'No bazar data.';
            tr.appendChild(td);
            bazarHistoryTableBody.appendChild(tr);
            return;
        }

        rows.forEach(function (r) {
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + escapeHtml(r.bazar_date) + '</td>' +
                '<td>' + escapeHtml(r.items) + '</td>' +
                '<td>৳ ' + parseFloat(r.total_amount).toFixed(2) + '</td>' +
                '<td>' + escapeHtml(r.bazaar_by || '-') + '</td>';
            bazarHistoryTableBody.appendChild(tr);
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Events

    attendanceDateFilter.addEventListener('change', function () {
        if (attendanceDateFilter.value) {
            loadMealsData(attendanceDateFilter.value, bazarDateFilter.value || '');
        }
    });

    bazarDateFilter.addEventListener('change', function () {
        loadMealsData(attendanceDateFilter.value || currentDate, bazarDateFilter.value);
    });

    bazarClearFilterBtn.addEventListener('click', function () {
        bazarDateFilter.value = '';
        loadMealsData(attendanceDateFilter.value || currentDate, '');
    });

    // Add Meal (admin only)
    if (addMealBtn) {
        addMealBtn.addEventListener('click', function () {
            if (currentRole !== 'admin') {
                alert('Only admin can add meal.');
                return;
            }

            var type = prompt('Enter meal type (breakfast / lunch / dinner):', 'breakfast');
            if (!type) return;
            type = type.toLowerCase();
            if (['breakfast', 'lunch', 'dinner'].indexOf(type) === -1) {
                alert('Invalid meal type.');
                return;
            }

            var menu = prompt('Enter menu (short description):', '');
            if (menu === null) return;

            var fd = new FormData();
            fd.append('meal_type', type);
            fd.append('menu', menu);
            fd.append('date', attendanceDateFilter.value || currentDate);

            postJSON('../Controller/pages/MealsController.php?action=addMeal', fd, function (err, data) {
                if (err || !data.success) {
                    alert((data && data.message) || 'Failed to add meal');
                    return;
                }
                alert(data.message || 'Meal saved.');
                loadMealsData(attendanceDateFilter.value || currentDate, bazarDateFilter.value || '');
            });
        });
    }

    // Initial Load
    attendanceDateFilter.value = currentDate;
    loadMealsData(currentDate, '');
});