// View/assets/js/pagesPartJs/payments.js

document.addEventListener('DOMContentLoaded', function () {
    // Top Filters
    var payMonthSelect = document.getElementById('payMonthSelect');
    var payYearSelect = document.getElementById('payYearSelect');
    var payReloadBtn = document.getElementById('payReloadBtn');

    var paymentsSubtitle = document.getElementById('paymentsSubtitle');

    // Stats Cards
    var statPay1 = document.getElementById('stat_pay_1'); // Deposit
    var statPay2 = document.getElementById('stat_pay_2'); // Bill
    var statPay3 = document.getElementById('stat_pay_3'); // Net

    // Form Elements
    var payMemberSelect = document.getElementById('payMemberSelect'); // Dropdown
    var payAmountInput = document.getElementById('payAmount');
    var payForSelect = document.getElementById('payFor');
    var payMethodSelect = document.getElementById('payMethod');
    var payTxnInput = document.getElementById('payTxn');
    var payMonthFor = document.getElementById('payMonthFor');
    var payYearFor = document.getElementById('payYearFor');
    var payAddBtn = document.getElementById('payAddBtn');

    // Tables
    var summaryTableBody = document.getElementById('summaryTableBody');
    var historyTableBody = document.getElementById('historyTableBody');

    // Sections for hiding/showing
    var addPaymentSection = document.querySelector('[data-section="addPayment"]');
    var paymentFormGrid = document.querySelector('.payments-form-grid'); // This needs to be hidden for members

    var currentRole = 'member';
    var currentUserId = null;

    // --- Helpers ---
    function getJSON(url, cb) {
        fetch(url, { credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => cb(null, data))
            .catch(err => cb(err));
    }

    function postJSON(url, formData, cb) {
        fetch(url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(res => res.json())
            .then(data => cb(null, data))
            .catch(err => cb(err));
    }

    function getMonthName(m) {
        var names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return names[m - 1] || '';
    }

    function initYearSelects() {
        var now = new Date();
        var y = now.getFullYear();

        // Populate Year Dropdowns (Filter & Form)
        for (var i = y - 2; i <= y + 2; i++) {
            var opt1 = document.createElement('option');
            opt1.value = i;
            opt1.textContent = i;
            payYearSelect.appendChild(opt1);

            var opt2 = document.createElement('option');
            opt2.value = i;
            opt2.textContent = i;
            payYearFor.appendChild(opt2);
        }

        // Set Defaults
        payYearSelect.value = y;
        payYearFor.value = y;
        payMonthSelect.value = now.getMonth() + 1;
        payMonthFor.value = now.getMonth() + 1;
    }

    // --- Core Logic ---

    function loadPayments() {
        var m = parseInt(payMonthSelect.value, 10);
        var y = parseInt(payYearSelect.value, 10);

        var url = '../Controller/pages/PaymentsController.php?action=getData&month=' + m + '&year=' + y;

        getJSON(url, function (err, data) {
            if (err) {
                console.error('Network Error', err);
                return;
            }
            if (!data.success) {
                console.error('API Error', data.message);
                return;
            }

            // Set Global State
            currentRole = data.user_role;
            currentUserId = data.current_user;

            // Update UI based on Role
            setupRoleUI();

            // Update Header
            paymentsSubtitle.textContent = 'Payments overview for ' + getMonthName(m) + ' ' + y;

            // Update Stats
            var st = data.stats || {};
            statPay1.textContent = '৳ ' + (st.deposit_all || 0).toFixed(2);
            statPay2.textContent = '৳ ' + (st.bill_user || 0).toFixed(2);

            var netVal = parseFloat(st.net_user || 0);
            var sign = netVal >= 0 ? '+' : '';
            statPay3.textContent = '৳ ' + sign + netVal.toFixed(2);

            // Note: No manual color setting here.

            // Populate Dropdown (Admin Only)
            if (currentRole === 'admin' && data.members) {
                fillMemberSelect(data.members);
            }

            // Render Tables
            renderSummary(data.summary || []);
            renderHistory(data.history || []);
        });
    }

    function setupRoleUI() {
        // Logic: Admin can see add form, Member cannot.
        if (currentRole === 'admin') {
            if (paymentFormGrid) paymentFormGrid.style.display = 'grid';
            if (addPaymentSection) addPaymentSection.style.display = 'block';

            // Show admin-only columns
            var adminHeaders = document.querySelectorAll('.admin-only');
            adminHeaders.forEach(el => el.style.display = '');

        } else {
            // Member
            if (paymentFormGrid) paymentFormGrid.style.display = 'none';
            if (addPaymentSection) addPaymentSection.style.display = 'none';

            // Hide admin-only columns
            var adminHeaders = document.querySelectorAll('.admin-only');
            adminHeaders.forEach(el => el.style.display = 'none');
        }
    }

    function fillMemberSelect(members) {
        payMemberSelect.innerHTML = '';
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = '-- Select Member --';
        payMemberSelect.appendChild(placeholder);

        members.forEach(function (m) {
            var opt = document.createElement('option');
            opt.value = m.user_id;
            opt.textContent = m.full_name;
            payMemberSelect.appendChild(opt);
        });
    }

    function renderSummary(rows) {
        summaryTableBody.innerHTML = '';
        if (rows.length === 0) {
            summaryTableBody.innerHTML = '<tr><td colspan="4">No data found.</td></tr>';
            return;
        }

        rows.forEach(function (r) {
            var tr = document.createElement('tr');

            // Highlight myself if using CSS class logic (optional, removed inline style)
            if (r.user_id == currentUserId) {
                tr.classList.add('row-highlight'); // Only class based, no inline color
            }

            var netVal = parseFloat(r.net);
            var badgeClass = netVal >= 0 ? 'badge-green' : 'badge-red'; // Using existing badges
            var sign = netVal >= 0 ? '+' : '';

            tr.innerHTML =
                '<td>' + escapeHtml(r.full_name) + '</td>' +
                '<td>৳ ' + r.bill.toFixed(2) + '</td>' +
                '<td>৳ ' + r.deposit.toFixed(2) + '</td>' +
                '<td><span class="badge ' + badgeClass + '">' + sign + netVal.toFixed(2) + '</span></td>';

            summaryTableBody.appendChild(tr);
        });
    }

    function renderHistory(rows) {
        historyTableBody.innerHTML = '';
        if (rows.length === 0) {
            var cols = (currentRole === 'admin') ? 7 : 6;
            historyTableBody.innerHTML = '<tr><td colspan="' + cols + '">No recent payments.</td></tr>';
            return;
        }

        rows.forEach(function (p) {
            var tr = document.createElement('tr');

            // Action Button
            var actionHtml = '';
            if (currentRole === 'admin') {
                actionHtml = '<button type="button" class="btn btn-secondary btn-xs table-action-btn" data-id="' + p.payment_id + '">&times;</button>';
            }

            var html =
                '<td>' + escapeHtml(p.member_name) + '</td>' +
                '<td>৳ ' + p.amount.toFixed(2) + '</td>' +
                '<td>' + escapeHtml(p.payment_for) + '</td>' +
                '<td>' + getMonthName(payMonthSelect.value) + '</td>' +
                '<td>' + escapeHtml(p.payment_method) + '</td>' +
                '<td>' + escapeHtml(p.transaction_id || '-') + '</td>' +
                '<td>' + p.date + '</td>';

            if (currentRole === 'admin') {
                html += '<td class="admin-only">' + actionHtml + '</td>';
            }

            tr.innerHTML = html;
            historyTableBody.appendChild(tr);
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // --- Event Listeners ---

    // 1. Load Data Button
    if (payReloadBtn) {
        payReloadBtn.addEventListener('click', loadPayments);
    }

    // 2. Add Payment (Admin Only)
    if (payAddBtn) {
        payAddBtn.addEventListener('click', function () {
            if (currentRole !== 'admin') {
                alert('Access Denied');
                return;
            }

            var amount = parseFloat(payAmountInput.value) || 0;
            var userId = payMemberSelect.value;

            if (!userId) {
                alert('Please select a member.');
                return;
            }
            if (amount <= 0) {
                alert('Amount must be positive.');
                return;
            }

            var fd = new FormData();
            fd.append('user_id', userId);
            fd.append('amount', amount);
            fd.append('payment_for', payForSelect.value);
            fd.append('payment_method', payMethodSelect.value);
            fd.append('transaction_id', payTxnInput.value);
            fd.append('month', payMonthFor.value);
            fd.append('year', payYearFor.value);

            postJSON('../Controller/pages/PaymentsController.php?action=addPayment', fd, function (err, data) {
                if (err || !data.success) {
                    alert((data && data.message) || 'Failed to add payment');
                    return;
                }
                alert('Payment added successfully!');
                payAmountInput.value = '';
                payTxnInput.value = '';
                loadPayments();
            });
        });
    }

    // 3. Delete Payment (Admin Only)
    historyTableBody.addEventListener('click', function (e) {
        if (e.target.classList.contains('table-action-btn')) {
            if (currentRole !== 'admin') return;

            var pid = e.target.getAttribute('data-id');
            if (!pid) return;

            if (!confirm('Are you sure you want to delete (undo) this payment? This will adjust the balance.')) {
                return;
            }

            var fd = new FormData();
            fd.append('payment_id', pid);

            postJSON('../Controller/pages/PaymentsController.php?action=deletePayment', fd, function (err, data) {
                if (err || !data.success) {
                    alert((data && data.message) || 'Failed to delete payment');
                    return;
                }
                alert('Payment undone successfully.');
                loadPayments();
            });
        }
    });

    // --- Init ---
    initYearSelects();
    loadPayments();
});