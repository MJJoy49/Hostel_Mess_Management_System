// View/assets/js/pagesPartJs/members.js

document.addEventListener('DOMContentLoaded', function () {
    var membersTableBody = document.getElementById('membersTableBody');
    var membersSubtitle = document.getElementById('membersSubtitle');

    var statTotalEl = document.getElementById('stat_total_members');
    var statActiveEl = document.getElementById('stat_active_members');
    var statOnLeaveEl = document.getElementById('stat_on_leave');
    var statAdminsEl = document.getElementById('stat_admins');

    var memberSearchInput = document.getElementById('memberSearchInput');

    var detailNameEl = document.getElementById('detail_name');
    var detailUserIdEl = document.getElementById('detail_user_id');
    var detailRoleEl = document.getElementById('detail_role');
    var detailStatusEl = document.getElementById('detail_status');
    var detailRoomEl = document.getElementById('detail_room');
    var detailContactEl = document.getElementById('detail_contact');
    var detailEmailEl = document.getElementById('detail_email');
    var detailBloodEl = document.getElementById('detail_blood');
    var detailProfessionEl = document.getElementById('detail_profession');
    var detailJoinedEl = document.getElementById('detail_joined');
    var detailAddressEl = document.getElementById('detail_address');
    var memberDetailsMeta = document.getElementById('memberDetailsMeta');

    var changeRoomSelect = document.getElementById('change_room_select');
    var changeRoomBtn = document.getElementById('changeRoomBtn');

    var toggleStatusBtn = document.getElementById('toggleStatusBtn');
    var toggleRoleBtn = document.getElementById('toggleRoleBtn');
    var removeMemberBtn = document.getElementById('removeMemberBtn');

    var addMemberBtn = document.getElementById('addMemberBtn');
    var addMemberModal = document.getElementById('addMemberModal');
    var addMemberClose = document.getElementById('addMemberClose');
    var addMemberCancel = document.getElementById('addMemberCancel');
    var addMemberForm = document.getElementById('addMemberForm');
    var addMemberRoomNote = document.getElementById('addMemberRoomNote');

    var memberInfoModal = document.getElementById('memberInfoModal');
    var memberInfoClose = document.getElementById('memberInfoClose');
    var memberInfoDownload = document.getElementById('memberInfoDownload');
    var memberInfoPrint = document.getElementById('memberInfoPrint');

    var infoNameEl = document.getElementById('info_name');
    var infoUserIdEl = document.getElementById('info_user_id');
    var infoEmailEl = document.getElementById('info_email');
    var infoPasswordEl = document.getElementById('info_password');
    var infoRoomEl = document.getElementById('info_room');
    var infoMessEl = document.getElementById('info_mess');

    var newRoomSelect = document.getElementById('new_room_id');

    var selectedUserId = null;
    var allMembers = [];
    var allRooms = [];

    function getJSON(url, callback) {
        fetch(url, { credentials: 'same-origin' })
            .then(function (res) { return res.json(); })
            .then(function (data) { callback(null, data); })
            .catch(function (err) { callback(err); });
    }

    function postJSON(url, formData, callback) {
        fetch(url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(function (res) { return res.json(); })
            .then(function (data) { callback(null, data); })
            .catch(function (err) { callback(err); });
    }

    function loadMembersData() {
        getJSON('../Controller/pages/MembersController.php?action=getData', function (err, data) {
            if (err || !data.success) {
                console.error('Failed to load members', err || data);
                return;
            }
            allMembers = data.members || [];
            allRooms = data.rooms || [];

            statTotalEl.textContent = data.stats.total;
            statActiveEl.textContent = data.stats.active;
            statOnLeaveEl.textContent = data.stats.on_leave;
            statAdminsEl.textContent = data.stats.admins;
            membersSubtitle.textContent = 'Members overview (' + data.stats.total + ' total)';

            renderMembersTable();
            fillRoomSelects();

            var role = data.current_role || null;
            if (role !== 'admin') {
                addMemberBtn.classList.add('hidden');
                toggleStatusBtn.classList.add('hidden');
                toggleRoleBtn.classList.add('hidden');
                removeMemberBtn.classList.add('hidden');
                document.getElementById('changeRoomRow').classList.add('hidden');
            } else {
                addMemberBtn.classList.remove('hidden');
                toggleStatusBtn.classList.remove('hidden');
                toggleRoleBtn.classList.remove('hidden');
                removeMemberBtn.classList.remove('hidden');
                document.getElementById('changeRoomRow').classList.remove('hidden');
            }

            // active + vacant room আছে কিনা, সেটা দেখে Add Member enable/disable
            var hasVacant = allRooms.some(function (r) {
                return r.is_active === 1 && r.vacant > 0;
            });

            if (!hasVacant) {
                addMemberBtn.disabled = true;
                addMemberRoomNote.textContent =
                    'No active room with free seats. Please create a room or free a seat before creating member.';
            } else {
                addMemberBtn.disabled = false;
                addMemberRoomNote.textContent =
                    'Member must be assigned to a room that has free seats. Otherwise member cannot be created.';
            }
        });
    }

    function renderMembersTable() {
        var search = memberSearchInput.value.toLowerCase();
        membersTableBody.innerHTML = '';

        allMembers.forEach(function (m) {
            var text = (m.full_name + ' ' + (m.email || '') + ' ' + (m.room_number || '')).toLowerCase();
            if (search && text.indexOf(search) === -1) {
                return;
            }

            var tr = document.createElement('tr');
            tr.dataset.userId = m.user_id;
            tr.dataset.roomId = m.room_id || '';

            if (m.is_me) {
                tr.classList.add('members-row-me');
            }

            var roomText = m.room_number ? m.room_number : '-';

            var statusHtml;
            if (m.status === 'active') {
                statusHtml = '<span class="badge badge-green">Active</span>';
            } else if (m.status === 'on_leave') {
                statusHtml = '<span class="badge badge-amber">On Leave</span>';
            } else {
                statusHtml = '<span class="badge badge-gray">' + escapeHtml(m.status) + '</span>';
            }

            tr.innerHTML =
                '<td>' + escapeHtml(m.full_name) + '</td>' +
                '<td>' + escapeHtml(roomText) + '</td>' +
                '<td>' + escapeHtml(m.role) + '</td>' +
                '<td>' + statusHtml + '</td>' +
                '<td>' + (m.joined_date ? escapeHtml(m.joined_date) : '-') + '</td>';

            tr.addEventListener('click', function () {
                setSelectedRow(tr);
                loadMemberDetails(m.user_id);
            });

            membersTableBody.appendChild(tr);
        });
    }

    function fillRoomSelects() {
        changeRoomSelect.innerHTML = '';
        newRoomSelect.innerHTML = '';

        // কেবল active + vacant থাকা room গুলো দেখাব
        allRooms.forEach(function (r) {
            if (r.is_active !== 1 || r.vacant <= 0) {
                return;
            }

            var vacantText = ' (vacant ' + r.vacant + ')';
            var label = r.room_number ? r.room_number : ('Room ' + r.room_id);
            label += vacantText;

            var opt1 = document.createElement('option');
            opt1.value = r.room_id;
            opt1.textContent = label;
            changeRoomSelect.appendChild(opt1);

            var opt2 = document.createElement('option');
            opt2.value = r.room_id;
            opt2.textContent = label;
            newRoomSelect.appendChild(opt2);
        });
    }

    function setSelectedRow(row) {
        var rows = membersTableBody.querySelectorAll('tr');
        rows.forEach(function (r) {
            r.classList.remove('members-row-selected');
        });
        row.classList.add('members-row-selected');
    }

    function loadMemberDetails(userId) {
        selectedUserId = userId;
        var url = '../Controller/pages/MembersController.php?action=getMember&user_id=' + encodeURIComponent(userId);

        getJSON(url, function (err, data) {
            if (err || !data.success) {
                console.error('Failed to load member details', err || data);
                alert((data && data.message) || 'Failed to load member details');
                return;
            }

            var m = data.member;
            memberDetailsMeta.textContent = 'Selected: ' + (m.full_name || m.user_id);

            detailNameEl.textContent = m.full_name || '-';
            detailUserIdEl.textContent = m.user_id || '-';
            detailRoleEl.textContent = m.role || '-';
            detailStatusEl.textContent = m.status || '-';
            detailRoomEl.textContent = m.room_number || '-';
            detailContactEl.textContent = m.contact || '-';
            detailEmailEl.textContent = m.email || '-';
            detailBloodEl.textContent = m.blood_group || '-';
            detailProfessionEl.textContent = m.profession || '-';
            detailJoinedEl.textContent = m.joined_date || '-';
            detailAddressEl.textContent = m.address || '-';

            if (m.room_id) {
                changeRoomSelect.value = m.room_id;
            }

            updateMemberNotes(m);
        });
    }

    function updateMemberNotes(m) {
        var list = document.getElementById('memberNotesList');
        list.innerHTML = '';

        var notes = [];

        if (m.status === 'active') {
            notes.push('This member is currently active in the mess.');
        } else if (m.status === 'on_leave') {
            notes.push('This member is currently on leave.');
        } else {
            notes.push('This member is inactive.');
        }

        if (m.room_number) {
            notes.push('Staying in room: ' + m.room_number + '.');
        }

        if (m.role === 'admin') {
            notes.push('This member has admin privileges.');
        }

        if (m.profession) {
            notes.push('Profession: ' + m.profession + '.');
        }

        notes.forEach(function (n) {
            var li = document.createElement('li');
            li.textContent = n;
            list.appendChild(li);
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    memberSearchInput.addEventListener('input', renderMembersTable);

    function openAddMemberModal() {
        addMemberModal.classList.add('open');
    }
    function closeAddMemberModal() {
        addMemberModal.classList.remove('open');
        addMemberForm.reset();
    }

    addMemberBtn.addEventListener('click', openAddMemberModal);
    addMemberClose.addEventListener('click', closeAddMemberModal);
    addMemberCancel.addEventListener('click', closeAddMemberModal);

    addMemberForm.addEventListener('submit', function (e) {
        e.preventDefault();

        var fullName = document.getElementById('new_full_name').value.trim();
        var contact = document.getElementById('new_contact').value.trim();
        var email = document.getElementById('new_email').value.trim();
        var password = document.getElementById('new_password').value.trim();
        var blood = document.getElementById('new_blood').value;
        var role = document.getElementById('new_role').value;
        var roomId = document.getElementById('new_room_id').value;
        var prof = document.getElementById('new_profession').value.trim();
        var address = document.getElementById('new_address').value.trim();

        if (!fullName || !contact || !email || !password || !roomId) {
            alert('Please fill required fields.');
            return;
        }

        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Invalid email.');
            return;
        }

        var formData = new FormData();
        formData.append('new_full_name', fullName);
        formData.append('new_contact', contact);
        formData.append('new_email', email);
        formData.append('new_password', password);
        formData.append('new_blood', blood);
        formData.append('new_role', role);
        formData.append('new_room_id', roomId);
        formData.append('new_profession', prof);
        formData.append('new_address', address);

        postJSON('../Controller/pages/MembersController.php?action=addMember', formData, function (err, data) {
            if (err || !data.success) {
                alert((data && data.message) || 'Failed to create member');
                return;
            }

            infoNameEl.textContent = data.member.full_name;
            infoUserIdEl.textContent = data.member.user_id;
            infoEmailEl.textContent = data.member.email;
            infoPasswordEl.textContent = data.member.password;
            infoRoomEl.textContent = data.member.room;
            infoMessEl.textContent = data.member.mess;

            closeAddMemberModal();
            openMemberInfoModal();

            loadMembersData();
        });
    });

    function openMemberInfoModal() {
        memberInfoModal.classList.add('open');
    }
    function closeMemberInfoModal() {
        memberInfoModal.classList.remove('open');
    }

    memberInfoClose.addEventListener('click', closeMemberInfoModal);

    memberInfoDownload.addEventListener('click', function () {
        var text = ''
            + 'Name: ' + infoNameEl.textContent + '\n'
            + 'User ID: ' + infoUserIdEl.textContent + '\n'
            + 'Email: ' + infoEmailEl.textContent + '\n'
            + 'Initial Password: ' + infoPasswordEl.textContent + '\n'
            + 'Room: ' + infoRoomEl.textContent + '\n'
            + 'Mess: ' + infoMessEl.textContent + '\n';

        var blob = new Blob([text], { type: 'text/plain' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'member_info_' + infoUserIdEl.textContent + '.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });

    memberInfoPrint.addEventListener('click', function () {
        window.print();
    });

    toggleStatusBtn.addEventListener('click', function () {
        if (!selectedUserId) {
            alert('Please select a member first.');
            return;
        }
        var fd = new FormData();
        fd.append('user_id', selectedUserId);

        postJSON('../Controller/pages/MembersController.php?action=toggleStatus', fd, function (err, data) {
            if (err || !data.success) {
                alert((data && data.message) || 'Failed to change status');
                return;
            }
            loadMembersData();
            loadMemberDetails(selectedUserId);
        });
    });

    toggleRoleBtn.addEventListener('click', function () {
        if (!selectedUserId) {
            alert('Please select a member first.');
            return;
        }
        var fd = new FormData();
        fd.append('user_id', selectedUserId);

        postJSON('../Controller/pages/MembersController.php?action=toggleRole', fd, function (err, data) {
            if (err || !data.success) {
                alert((data && data.message) || 'Failed to change role');
                return;
            }
            loadMembersData();
            loadMemberDetails(selectedUserId);
        });
    });

    removeMemberBtn.addEventListener('click', function () {
        if (!selectedUserId) {
            alert('Please select a member first.');
            return;
        }
        if (!confirm('Are you sure you want to remove this member?')) {
            return;
        }
        var fd = new FormData();
        fd.append('user_id', selectedUserId);

        postJSON('../Controller/pages/MembersController.php?action=removeMember', fd, function (err, data) {
            if (err || !data.success) {
                alert((data && data.message) || 'Failed to remove member');
                return;
            }
            selectedUserId = null;
            memberDetailsMeta.textContent = 'No member selected';
            detailNameEl.textContent = '-';
            detailUserIdEl.textContent = '-';
            detailRoleEl.textContent = '-';
            detailStatusEl.textContent = '-';
            detailRoomEl.textContent = '-';
            detailContactEl.textContent = '-';
            detailEmailEl.textContent = '-';
            detailBloodEl.textContent = '-';
            detailProfessionEl.textContent = '-';
            detailJoinedEl.textContent = '-';
            detailAddressEl.textContent = '-';
            document.getElementById('memberNotesList').innerHTML = '';
            loadMembersData();
        });
    });

    changeRoomBtn.addEventListener('click', function () {
        if (!selectedUserId) {
            alert('Please select a member first.');
            return;
        }
        var roomId = changeRoomSelect.value;
        if (!roomId) {
            alert('Please select a room.');
            return;
        }
        var fd = new FormData();
        fd.append('user_id', selectedUserId);
        fd.append('room_id', roomId);

        postJSON('../Controller/pages/MembersController.php?action=changeRoom', fd, function (err, data) {
            if (err || !data.success) {
                alert((data && data.message) || 'Failed to change room');
                return;
            }
            loadMembersData();
            loadMemberDetails(selectedUserId);
        });
    });

    loadMembersData();
});