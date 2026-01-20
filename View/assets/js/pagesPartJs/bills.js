// View/assets/js/pagesPartJs/bills.js

document.addEventListener('DOMContentLoaded', function () {
    var billMonthSelect = document.getElementById('billMonthSelect');
    var billYearSelect = document.getElementById('billYearSelect');
    var billReloadBtn = document.getElementById('billReloadBtn');
    var billsSubtitle = document.getElementById('billsSubtitle');

    // Stats
    var statBill1 = document.getElementById('stat_bill_1');
    var statBill2 = document.getElementById('stat_bill_2');
    var statBill3 = document.getElementById('stat_bill_3');

    // Tables
    var bazarTableBody = document.getElementById('bazarTableBody');
    var bazarMeta = document.getElementById('bazarMeta');

    var expenseTableBody = document.getElementById('expenseTableBody');
    var expenseMeta = document.getElementById('expenseMeta');

    var myBillsTableBody = document.getElementById('myBillsTableBody');
    var allBillsTableBody = document.getElementById('allBillsTableBody');
    var paymentsTableBody = document.getElementById('paymentsTableBody');

    // Inputs for Admin
    var bazarAddDate = document.getElementById('bazarAddDate');
    var bazarAddItems = document.getElementById('bazarAddItems');
    var bazarAddAmount = document.getElementById('bazarAddAmount');
    var bazarAddBy = document.getElementById('bazarAddBy');
    var bazarAddBtn = document.getElementById('bazarAddBtn');

    var expenseAddDate = document.getElementById('expenseAddDate');
    var expenseAddCategory = document.getElementById('expenseAddCategory');
    var expenseAddAmount = document.getElementById('expenseAddAmount');
    var expenseAddDesc = document.getElementById('expenseAddDesc');
    var expenseAddBtn = document.getElementById('expenseAddBtn');

    // Room Rent Elements
    var rentUserSelect = document.getElementById('rentUserSelect');
    var rentAmount = document.getElementById('rentAmount');
    var rentAddBtn = document.getElementById('rentAddBtn');

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

    function monthName(m) {
        var names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return names[m - 1] || m;
    }

    function initYearSelect() {
        var now = new Date();
        var y = now.getFullYear();
        billYearSelect.innerHTML = '';
        for (var i = y; i >= y - 2; i--) {
            var opt = document.createElement('option');
            opt.value = i;
            opt.textContent = i;
            billYearSelect.appendChild(opt);
        }
        billMonthSelect.value = now.getMonth() + 1;
    }

    // Auto-fill rent when user selected
    if (rentUserSelect) {
        rentUserSelect.addEventListener('change', function () {
            var selectedOption = this.options[this.selectedIndex];
            var rent = selectedOption.getAttribute('data-rent');
            if (rent && rentAmount) {
                rentAmount.value = rent;
            } else if (rentAmount) {
                rentAmount.value = '';
            }
        });
    }

    function loadUsers() {
        if (!rentUserSelect) return;

        rentUserSelect.innerHTML = '<option value="">Loading...</option>';

        getJSON('../Controller/pages/BillsController.php?action=getUsers', function (err, data) {
            rentUserSelect.innerHTML = '';

            if (err || !data || !data.success) {
                console.error('Error loading users:', err || data);
                var opt = document.createElement('option');
                opt.textContent = "Error loading list";
                rentUserSelect.appendChild(opt);
                return;
            }

            var users = data.users || [];

            var defaultOpt = document.createElement('option');
            defaultOpt.value = "";
            defaultOpt.textContent = "-- Select Member --";
            rentUserSelect.appendChild(defaultOpt);

            if (users.length === 0) {
                var emptyOpt = document.createElement('option');
                emptyOpt.textContent = "No active members found";
                emptyOpt.disabled = true;
                rentUserSelect.appendChild(emptyOpt);
            } else {
                users.forEach(function (u) {
                    var opt = document.createElement('option');
                    opt.value = u.user_id;
                    opt.textContent = u.full_name;
                    // Store rent in dataset
                    opt.dataset.rent = u.rent_per_seat || 0;
                    rentUserSelect.appendChild(opt);
                });
            }
        });
    }

    function loadBills() {
        var m = parseInt(billMonthSelect.value, 10) || (new Date().getMonth() + 1);
        var y = parseInt(billYearSelect.value, 10) || (new Date().getFullYear());

        var url = '../Controller/pages/BillsController.php?action=getData&month=' +
            encodeURIComponent(m) + '&year=' + encodeURIComponent(y);

        getJSON(url, function (err, data) {
            if (err || !data.success) {
                console.error('Failed to load bills data', err || data);
                return;
            }

            currentRole = data.role || 'member';

            var adminElements = document.querySelectorAll('.admin-only');
            if (currentRole === 'admin') {
                adminElements.forEach(function (el) { el.classList.remove('hidden-force'); });
            } else {
                adminElements.forEach(function (el) { el.classList.add('hidden-force'); });
            }

            billsSubtitle.textContent = 'Bills overview for ' + monthName(data.month) + ', ' + data.year;

            // Stats
            statBill1.textContent = '৳ ' + parseFloat(data.stats.top_total).toFixed(2);
            statBill2.textContent = '৳ ' + parseFloat(data.stats.top_paid).toFixed(2);
            statBill3.textContent = '৳ ' + parseFloat(data.stats.top_due).toFixed(2);

            bazarMeta.textContent = 'Total: ৳ ' + parseFloat(data.stats.bazar_total).toFixed(2);
            expenseMeta.textContent = 'Total: ৳ ' + parseFloat(data.stats.expense_total).toFixed(2);

            renderBazar(data.bazar || []);
            renderExpenses(data.expenses || []);
            renderMyBills(data.myBills || []);
            renderAllBills(data.allBills || []);
            renderPayments(data.payments || []);
        });
    }

    function renderBazar(rows) {
        bazarTableBody.innerHTML = '';
        if (!rows.length) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="5" style="text-align:center;">No bazar data.</td>';
            bazarTableBody.appendChild(tr);
            return;
        }
        rows.forEach(function (r) {
            var tr = document.createElement('tr');

            var actionHtml = '';
            if (currentRole === 'admin') {
                actionHtml = '<button class="table-action-btn" data-type="bazar" data-id="' +
                    r.bazar_id + '">x</button>';
            }

            var html = '<td>' + escapeHtml(r.bazar_date) + '</td>' +
                '<td>' + escapeHtml(r.items) + '</td>' +
                '<td>' + parseFloat(r.total_amount).toFixed(2) + '</td>' +
                '<td>' + escapeHtml(r.bazaar_by) + '</td>';

            if (currentRole === 'admin') {
                html += '<td class="admin-only">' + actionHtml + '</td>';
            }

            tr.innerHTML = html;
            bazarTableBody.appendChild(tr);
        });
    }

    function renderExpenses(rows) {
        expenseTableBody.innerHTML = '';
        if (!rows.length) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="6" style="text-align:center;">No expenses data.</td>';
            expenseTableBody.appendChild(tr);
            return;
        }
        rows.forEach(function (r) {
            var tr = document.createElement('tr');
            var actionHtml = '';
            if (currentRole === 'admin') {
                actionHtml = '<button class="table-action-btn" data-type="expense" data-id="' +
                    r.expense_id + '">x</button>';
            }

            var html = '<td>' + escapeHtml(r.expense_date) + '</td>' +
                '<td>' + escapeHtml(r.category) + '</td>' +
                '<td>' + escapeHtml(r.description) + '</td>' +
                '<td>' + parseFloat(r.amount).toFixed(2) + '</td>' +
                '<td>' + escapeHtml(r.added_by || '-') + '</td>';

            if (currentRole === 'admin') {
                html += '<td class="admin-only">' + actionHtml + '</td>';
            }

            tr.innerHTML = html;
            expenseTableBody.appendChild(tr);
        });
    }

    function renderMyBills(rows) {
        myBillsTableBody.innerHTML = '';
        if (!rows.length) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="6">No bill data.</td>';
            myBillsTableBody.appendChild(tr);
            return;
        }
        rows.forEach(function (b) {
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + escapeHtml(b.month_label) + '</td>' +
                '<td>' + b.total_meals.toFixed(1) + '</td>' +
                '<td>৳ ' + b.total_amount.toFixed(2) + '</td>' +
                '<td>৳ ' + b.paid_amount.toFixed(2) + '</td>' +
                '<td>৳ ' + b.due_amount.toFixed(2) + '</td>' +
                '<td>' + getBadge(b.status) + '</td>';
            myBillsTableBody.appendChild(tr);
        });
    }

    function renderAllBills(rows) {
        allBillsTableBody.innerHTML = '';
        if (!rows.length) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="6">No bill data for this month.</td>';
            allBillsTableBody.appendChild(tr);
            return;
        }
        rows.forEach(function (b) {
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + escapeHtml(b.member_name) + '</td>' +
                '<td>' + b.total_meals.toFixed(1) + '</td>' +
                '<td>৳ ' + b.total_amount.toFixed(2) + '</td>' +
                '<td>৳ ' + b.paid_amount.toFixed(2) + '</td>' +
                '<td>৳ ' + b.due_amount.toFixed(2) + '</td>' +
                '<td>' + getBadge(b.status) + '</td>';
            allBillsTableBody.appendChild(tr);
        });
    }

    function renderPayments(rows) {
        paymentsTableBody.innerHTML = '';
        if (!rows.length) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="7">No payments.</td>';
            paymentsTableBody.appendChild(tr);
            return;
        }
        rows.forEach(function (p) {
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + escapeHtml(p.member_name) + '</td>' +
                '<td>৳ ' + p.amount.toFixed(2) + '</td>' +
                '<td>' + escapeHtml(p.payment_for) + '</td>' +
                '<td>' + escapeHtml(p.month_label) + '</td>' +
                '<td>' + escapeHtml(p.payment_method) + '</td>' +
                '<td>' + escapeHtml(p.transaction_id || '-') + '</td>' +
                '<td>' + escapeHtml(p.paid_at) + '</td>';
            paymentsTableBody.appendChild(tr);
        });
    }

    function getBadge(status) {
        var cls = 'badge-gray';
        if (status === 'paid') cls = 'badge-green';
        else if (status === 'partial') cls = 'badge-amber';
        return '<span class="badge ' + cls + '">' + status + '</span>';
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // --- Actions ---

    // Add Room Rent
    if (rentAddBtn) {
        rentAddBtn.addEventListener('click', function () {
            if (currentRole !== 'admin') return;

            var uid = rentUserSelect.value;
            var amt = parseFloat(rentAmount.value) || 0;

            if (!uid || amt <= 0) {
                alert('Please select a member and enter a valid amount.');
                return;
            }

            var fd = new FormData();
            fd.append('user_id', uid);
            fd.append('amount', amt);

            postJSON('../Controller/pages/BillsController.php?action=addRoomRent', fd, function (err, data) {
                if (err || !data.success) {
                    alert((data && data.message) || 'Failed to add rent');
                    return;
                }
                alert(data.message);
                rentAmount.value = '';
                loadBills();
            });
        });
    }

    // Add Bazar
    bazarAddBtn.addEventListener('click', function () {
        if (currentRole !== 'admin') return;
        var date = bazarAddDate.value;
        var items = bazarAddItems.value.trim();
        var amt = parseFloat(bazarAddAmount.value) || 0;
        var by = bazarAddBy.value.trim();

        if (!date || !items || amt <= 0) {
            alert('Please fill date, items and amount.');
            return;
        }

        var fd = new FormData();
        fd.append('bazar_date', date);
        fd.append('items', items);
        fd.append('total_amount', amt);
        fd.append('bazar_by', by);

        postJSON('../Controller/pages/BillsController.php?action=addBazar', fd, function (err, data) {
            if (err || !data.success) {
                alert((data && data.message) || 'Failed to add bazar');
                return;
            }
            alert(data.message);
            bazarAddAmount.value = '0';
            bazarAddItems.value = '';
            loadBills();
        });
    });

    // Add Expense
    expenseAddBtn.addEventListener('click', function () {
        if (currentRole !== 'admin') return;
        var date = expenseAddDate.value;
        var cat = expenseAddCategory.value.trim();
        var amt = parseFloat(expenseAddAmount.value) || 0;
        var desc = expenseAddDesc.value.trim();

        if (!date || !cat || amt <= 0) {
            alert('Please fill date, category and amount.');
            return;
        }

        var fd = new FormData();
        fd.append('expense_date', date);
        fd.append('category', cat);
        fd.append('amount', amt);
        fd.append('description', desc);

        postJSON('../Controller/pages/BillsController.php?action=addExpense', fd, function (err, data) {
            if (err || !data.success) {
                alert((data && data.message) || 'Failed to add expense');
                return;
            }
            alert(data.message);
            expenseAddAmount.value = '0';
            expenseAddCategory.value = '';
            loadBills();
        });
    });

    // Delete Actions
    document.addEventListener('click', function (e) {
        if (currentRole !== 'admin') return;
        if (!e.target.classList.contains('table-action-btn')) return;

        var type = e.target.getAttribute('data-type');
        var id = e.target.getAttribute('data-id');

        if (!type || !id) return;

        if (!confirm('Are you sure you want to delete this? It will adjust member bills.')) {
            return;
        }

        var fd = new FormData();
        fd.append('id', id);

        var action = (type === 'bazar') ? 'deleteBazar' : 'deleteExpense';

        postJSON('../Controller/pages/BillsController.php?action=' + action, fd, function (err, data) {
            if (err || !data.success) {
                alert((data && data.message) || 'Failed to delete');
                return;
            }
            loadBills();
        });
    });

    billReloadBtn.addEventListener('click', loadBills);

    // Initialize
    initYearSelect();
    loadUsers();
    loadBills();
});