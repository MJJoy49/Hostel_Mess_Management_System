// View/assets/js/pagesPartJs/notices.js

document.addEventListener('DOMContentLoaded', function () {
    var noticesSubtitle = document.getElementById('noticesSubtitle');

    var statTotalAnns = document.getElementById('stat_total_announcements');
    var statActiveAds = document.getElementById('stat_active_ads');
    var statPendingReq = document.getElementById('stat_pending_requests');

    var announcementsTableBody = document.getElementById('announcementsTableBody');
    var seatAdsTableBody = document.getElementById('seatAdsTableBody');
    var requestSeatsTableBody = document.getElementById('requestSeatsTableBody');

    var announcementDateFilter = document.getElementById('announcementDateFilter');
    var clearDateFilterBtn = document.getElementById('clearDateFilterBtn');

    var addAnnouncementBtn = document.getElementById('addAnnouncementBtn');
    var addAnnouncementModal = document.getElementById('addAnnouncementModal');
    var addAnnouncementClose = document.getElementById('addAnnouncementClose');
    var addAnnouncementCancel = document.getElementById('addAnnouncementCancel');
    var addAnnouncementForm = document.getElementById('addAnnouncementForm');

    var addSeatAdBtn = document.getElementById('addSeatAdBtn');
    var addSeatAdModal = document.getElementById('addSeatAdModal');
    var addSeatAdClose = document.getElementById('addSeatAdClose');
    var addSeatAdCancel = document.getElementById('addSeatAdCancel');
    var addSeatAdForm = document.getElementById('addSeatAdForm');

    // NEW: Mess address input & cache
    var newMessAddressInput = document.getElementById('new_mess_address');
    var currentMessAddress = '';

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

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function loadNotices() {
        var url = '../Controller/pages/NoticesController.php?action=getData';

        if (announcementDateFilter.value) {
            url += '&filter_date=' + encodeURIComponent(announcementDateFilter.value);
        }

        getJSON(url, function (err, data) {
            if (err || !data.success) {
                console.error('Failed to load notices', err || data);
                return;
            }

            currentRole = data.role || 'member';

            // Mess address from backend → readonly input
            if (data.mess) {
                currentMessAddress = data.mess.address || '';
                if (newMessAddressInput) {
                    newMessAddressInput.value = currentMessAddress;
                }
            }

            noticesSubtitle.textContent = 'Mess announcements and seat ads';

            statTotalAnns.textContent = data.stats.total_announcements;
            statActiveAds.textContent = data.stats.active_ads;
            statPendingReq.textContent = data.stats.pending_requests;

            renderAnnouncements(data.announcements || []);
            renderSeatAds(data.seat_ads || []);
            renderRequests(data.requests || []);

            setupRoleUI();
        });
    }

    function setupRoleUI() {
        var adminOnlyEls = document.querySelectorAll('.admin-only');
        if (currentRole === 'admin') {
            adminOnlyEls.forEach(function (el) { el.classList.remove('hidden'); });
        } else {
            adminOnlyEls.forEach(function (el) { el.classList.add('hidden'); });
        }
    }

    function renderAnnouncements(rows) {
        announcementsTableBody.innerHTML = '';
        if (!rows.length) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 5;
            td.textContent = 'No announcements.';
            tr.appendChild(td);
            announcementsTableBody.appendChild(tr);
            return;
        }
        rows.forEach(function (a) {
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + escapeHtml(a.title) + '</td>' +
                '<td>' + escapeHtml(a.message) + '</td>' +
                '<td>' + escapeHtml(a.type_label) + '</td>' +
                '<td>' + escapeHtml(a.posted_by_name) + '</td>' +
                '<td>' + escapeHtml(a.created_at) + '</td>';
            announcementsTableBody.appendChild(tr);
        });
    }

    function renderSeatAds(rows) {
        seatAdsTableBody.innerHTML = '';
        if (!rows.length) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 6;
            td.textContent = 'No active seat ads.';
            tr.appendChild(td);
            seatAdsTableBody.appendChild(tr);
            return;
        }
        rows.forEach(function (ad) {
            var tr = document.createElement('tr');

            var actionCell;
            if (currentRole === 'admin') {
                actionCell =
                    '<td><button class="btn btn-secondary btn-xs seatad-delete" data-id="' +
                    ad.ad_id + '">x</button></td>';
            } else {
                actionCell = '<td></td>';
            }

            tr.innerHTML =
                '<td>' + escapeHtml(ad.ad_title) + '</td>' +
                '<td>' + escapeHtml(ad.room_label) + '</td>' +
                '<td>' + ad.vacant_seats + '</td>' +
                '<td>৳' + ad.rent_per_seat.toFixed(2) + '</td>' +
                '<td>' + escapeHtml(ad.contact_person) + ' (' +
                escapeHtml(ad.contact_number) + ')</td>' +
                actionCell;

            seatAdsTableBody.appendChild(tr);
        });
    }

    function renderRequests(rows) {
        requestSeatsTableBody.innerHTML = '';
        if (!rows.length) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 5;
            td.textContent = 'No seat requests.';
            tr.appendChild(td);
            requestSeatsTableBody.appendChild(tr);
            return;
        }
        rows.forEach(function (r) {
            var statusCls = r.status === 'accepted' ? 'badge badge-green' : 'badge badge-amber';
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + escapeHtml(r.name) + '</td>' +
                '<td>' + escapeHtml(r.contact_number) + '</td>' +
                '<td>' + escapeHtml(r.profession || '-') + '</td>' +
                '<td>' + escapeHtml(r.ad_title) + '</td>' +
                '<td><span class="' + statusCls + '">' +
                escapeHtml(r.status) + '</span></td>';
            requestSeatsTableBody.appendChild(tr);
        });
    }

    // Announcement modal
    function openAnnouncementModal() {
        addAnnouncementModal.classList.add('open');
    }
    function closeAnnouncementModal() {
        addAnnouncementModal.classList.remove('open');
        addAnnouncementForm.reset();
    }

    addAnnouncementBtn.addEventListener('click', openAnnouncementModal);
    addAnnouncementClose.addEventListener('click', closeAnnouncementModal);
    addAnnouncementCancel.addEventListener('click', closeAnnouncementModal);

    addAnnouncementForm.addEventListener('submit', function (e) {
        e.preventDefault();

        var title = document.getElementById('new_announce_title').value.trim();
        var message = document.getElementById('new_announce_message').value.trim();

        if (!title || !message) {
            alert('Please fill title and message.');
            return;
        }

        var fd = new FormData();
        fd.append('title', title);
        fd.append('message', message);

        postJSON('../Controller/pages/NoticesController.php?action=addAnnouncement', fd, function (err, data) {
            if (err || !data.success) {
                alert((data && data.message) || 'Failed to add announcement');
                return;
            }
            alert(data.message || 'Announcement saved.');
            closeAnnouncementModal();
            loadNotices();
        });
    });

    // Seat Ad modal (admin-only)
    function openSeatAdModal() {
        if (currentRole !== 'admin') {
            alert('Only admin can add seat ads.');
            return;
        }
        if (newMessAddressInput) {
            newMessAddressInput.value = currentMessAddress;
        }
        addSeatAdModal.classList.add('open');
    }
    function closeSeatAdModal() {
        addSeatAdModal.classList.remove('open');
        addSeatAdForm.reset();
        if (newMessAddressInput) {
            newMessAddressInput.value = currentMessAddress;
        }
    }

    addSeatAdBtn.addEventListener('click', openSeatAdModal);
    addSeatAdClose.addEventListener('click', closeSeatAdModal);
    addSeatAdCancel.addEventListener('click', closeSeatAdModal);

    addSeatAdForm.addEventListener('submit', function (e) {
        e.preventDefault();

        var title = document.getElementById('new_ad_title').value.trim();
        var room = document.getElementById('new_ad_room').value.trim();
        var vacant = parseInt(document.getElementById('new_vacant_seats').value, 10) || 0;
        var rent = parseFloat(document.getElementById('new_rent').value) || 0;
        var person = document.getElementById('new_contact_person').value.trim();
        var number = document.getElementById('new_contact_number').value.trim();
        var desc = document.getElementById('new_ad_description').value.trim();

        if (!title || vacant <= 0 || rent <= 0 || !person || !number) {
            alert('Please fill required fields.');
            return;
        }

        var fd = new FormData();
        fd.append('ad_title', title);
        fd.append('room_text', room); // Controller uses room_text
        fd.append('vacant_seats', vacant);
        fd.append('rent_per_seat', rent);
        fd.append('contact_person', person);
        fd.append('contact_number', number);
        fd.append('ad_description', desc);

        postJSON('../Controller/pages/NoticesController.php?action=addSeatAd', fd, function (err, data) {
            if (err || !data.success) {
                alert((data && data.message) || 'Failed to add seat ad');
                return;
            }
            alert(data.message || 'Seat ad saved.');
            closeSeatAdModal();
            loadNotices();
        });
    });

    // Date filter
    clearDateFilterBtn.addEventListener('click', function () {
        announcementDateFilter.value = '';
        loadNotices();
    });
    announcementDateFilter.addEventListener('change', loadNotices);

    // Delete seat ad (x) - admin only
    document.addEventListener('click', function (e) {
        var target = e.target;
        if (!target.classList.contains('seatad-delete')) return;

        if (currentRole !== 'admin') return;

        var id = target.getAttribute('data-id');
        if (!id) return;

        if (!confirm('Delete this seat ad?')) return;

        var fd = new FormData();
        fd.append('id', id);

        postJSON('../Controller/pages/NoticesController.php?action=deleteSeatAd', fd, function (err, data) {
            if (err || !data.success) {
                alert((data && data.message) || 'Failed to delete seat ad');
                return;
            }
            loadNotices();
        });
    });

    // initial load
    loadNotices();
});