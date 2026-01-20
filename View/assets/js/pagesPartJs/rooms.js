// view/assets/js/pages/parts/rooms.js

document.addEventListener('DOMContentLoaded', function () {
    var roomsTableBody = document.getElementById('roomsTableBody');
    var roomsSubtitle = document.getElementById('roomsSubtitle');

    var statTotalRoomsEl = document.getElementById('stat_total_rooms');
    var statTotalBedsEl = document.getElementById('stat_total_beds');
    var statOccupiedEl = document.getElementById('stat_occupied');
    var statVacantEl = document.getElementById('stat_vacant');

    var detailRoomMeta = document.getElementById('roomDetailsMeta');
    var detailRoomNumber = document.getElementById('detail_room_number');
    var detailCapacity = document.getElementById('detail_capacity');
    var detailOccupancy = document.getElementById('detail_occupancy');
    var detailVacant = document.getElementById('detail_vacant');
    var detailRent = document.getElementById('detail_rent');
    var detailStatus = document.getElementById('detail_status');
    var detailMembers = document.getElementById('detail_members');
    var detailFacilities = document.getElementById('detail_facilities');

    var roomNotesList = document.getElementById('roomNotesList');

    var addRoomBtn = document.getElementById('addRoomBtn');
    var editRoomBtn = document.getElementById('editRoomBtn');
    var removeRoomBtn = document.getElementById('removeRoomBtn');

    var addRoomModal = document.getElementById('addRoomModal');
    var roomModalTitle = document.getElementById('roomModalTitle');
    var addRoomClose = document.getElementById('addRoomClose');
    var addRoomCancel = document.getElementById('addRoomCancel');
    var addRoomForm = document.getElementById('addRoomForm');
    var roomModalSaveBtn = document.getElementById('roomModalSaveBtn');

    var newRoomNumber = document.getElementById('new_room_number');
    var newCapacity = document.getElementById('new_capacity');
    var newRent = document.getElementById('new_rent');
    var newIsActive = document.getElementById('new_is_active');
    var newFacilities = document.getElementById('new_facilities');

    var allRooms = [];
    var myRoomId = null;
    var selectedRoomId = null;
    var currentRoom = null;
    var editMode = false; // false = add, true = update
    var currentUserRole = 'member'; // Default to member

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

    function loadRoomsData() {
        getJSON('../controller/pages/RoomsController.php?action=getData', function (err, data) {
            if (err || !data.success) {
                console.error('Failed to load rooms', err || data);
                return;
            }

            allRooms = data.rooms || [];
            myRoomId = data.my_room_id;
            currentUserRole = data.user_role; // Capture role from backend

            // Handle UI based on role for 'Add Room' button
            if (currentUserRole !== 'admin') {
                if (addRoomBtn) addRoomBtn.style.display = 'none';
            } else {
                if (addRoomBtn) addRoomBtn.style.display = 'inline-block';
            }

            statTotalRoomsEl.textContent = data.stats.total_rooms;
            statTotalBedsEl.textContent = data.stats.total_beds;
            statOccupiedEl.textContent = data.stats.occupied;
            statVacantEl.textContent = data.stats.vacant;

            roomsSubtitle.textContent =
                'Rooms overview (' + data.stats.total_rooms + ' rooms, ' +
                data.stats.total_beds + ' beds)';

            renderRoomsTable();
        });
    }

    function renderRoomsTable() {
        roomsTableBody.innerHTML = '';

        allRooms.forEach(function (r) {
            var tr = document.createElement('tr');
            tr.dataset.roomId = r.room_id;

            if (myRoomId && myRoomId === r.room_id) {
                tr.classList.add('rooms-row-mine');
            }

            var statusHtml;
            if (r.is_active === 1) {
                statusHtml = '<span class="badge badge-green">Active</span>';
            } else {
                statusHtml = '<span class="badge badge-gray">Closed</span>';
            }

            tr.innerHTML =
                '<td>' + escapeHtml(r.room_number || ('Room ' + r.room_id)) + '</td>' +
                '<td>' + r.capacity + '</td>' +
                '<td>' + r.occupancy + '</td>' +
                '<td>' + r.vacant + '</td>' +
                '<td>' + r.rent.toFixed(2) + '</td>' +
                '<td>' + statusHtml + '</td>';

            tr.addEventListener('click', function () {
                setSelectedRow(tr);
                loadRoomDetails(r.room_id);
            });

            roomsTableBody.appendChild(tr);
        });
    }

    function setSelectedRow(row) {
        var rows = roomsTableBody.querySelectorAll('tr');
        rows.forEach(function (r) {
            r.classList.remove('rooms-row-selected');
        });
        row.classList.add('rooms-row-selected');
        selectedRoomId = parseInt(row.dataset.roomId, 10);
    }

    function loadRoomDetails(roomId) {
        var url = '../controller/pages/RoomsController.php?action=getRoom&room_id=' + encodeURIComponent(roomId);
        getJSON(url, function (err, data) {
            if (err || !data.success) {
                console.error('Failed to load room details', err || data);
                return;
            }
            currentRoom = data.room;

            var rn = currentRoom.room_number || ('Room ' + currentRoom.room_id);
            detailRoomMeta.textContent = 'Selected Room: ' + rn;

            detailRoomNumber.textContent = rn;
            detailCapacity.textContent = currentRoom.capacity;
            detailOccupancy.textContent = currentRoom.occupancy;
            detailVacant.textContent = currentRoom.vacant;
            detailRent.textContent = '৳' + currentRoom.rent.toFixed(2);
            detailStatus.textContent = (currentRoom.is_active === 1 ? 'Active' : 'Closed');
            detailFacilities.textContent = currentRoom.facilities || '-';

            if (currentRoom.members && currentRoom.members.length > 0) {
                var names = currentRoom.members.map(function (m) {
                    return m.full_name + ' (' + m.role + ')';
                });
                detailMembers.textContent = names.join(', ');
            } else {
                detailMembers.textContent = 'No members in this room.';
            }

            // Handle UI based on role for Edit/Remove buttons
            if (currentUserRole !== 'admin') {
                if (editRoomBtn) editRoomBtn.style.display = 'none';
                if (removeRoomBtn) removeRoomBtn.style.display = 'none';
            } else {
                if (editRoomBtn) editRoomBtn.style.display = 'inline-block';
                if (removeRoomBtn) removeRoomBtn.style.display = 'inline-block';
            }

            updateRoomNotes();
        });
    }

    function updateRoomNotes() {
        roomNotesList.innerHTML = '';
        if (!currentRoom) return;

        var notes = [];

        notes.push('Capacity: ' + currentRoom.capacity + ', Occupancy: ' +
            currentRoom.occupancy + ', Vacant: ' + currentRoom.vacant);

        if (currentRoom.is_active === 1) {
            notes.push('This room is currently active for new members.');
        } else {
            notes.push('This room is closed (not accepting new members).');
        }

        if (currentRoom.facilities) {
            notes.push('Facilities: ' + currentRoom.facilities);
        }

        notes.forEach(function (n) {
            var li = document.createElement('li');
            li.textContent = n;
            roomNotesList.appendChild(li);
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

    function openAddRoomModal() {
        editMode = false;
        selectedRoomId = null;
        currentRoom = null;

        roomModalTitle.textContent = 'Add New Room';
        addRoomForm.reset();
        addRoomModal.classList.add('open');
    }

    function openEditRoomModal() {
        if (!selectedRoomId || !currentRoom) {
            alert('Please select a room first.');
            return;
        }

        editMode = true;
        roomModalTitle.textContent = 'Update Room';

        newRoomNumber.value = currentRoom.room_number || ('Room ' + currentRoom.room_id);
        newCapacity.value = currentRoom.capacity;
        newRent.value = currentRoom.rent;
        newIsActive.value = currentRoom.is_active;
        newFacilities.value = currentRoom.facilities || '';

        addRoomModal.classList.add('open');
    }

    function closeRoomModal() {
        addRoomModal.classList.remove('open');
        addRoomForm.reset();
        editMode = false;
    }

    addRoomBtn.addEventListener('click', openAddRoomModal);
    addRoomClose.addEventListener('click', closeRoomModal);
    addRoomCancel.addEventListener('click', closeRoomModal);

    addRoomForm.addEventListener('submit', function (e) {
        e.preventDefault();

        var roomNumber = newRoomNumber.value.trim();
        var capacity = parseInt(newCapacity.value, 10) || 0;
        var rent = parseFloat(newRent.value) || 0;
        var isActive = parseInt(newIsActive.value, 10) || 0;
        var facilities = newFacilities.value.trim();

        if (!roomNumber || capacity <= 0) {
            alert('Please fill room number and capacity.');
            return;
        }

        var fd = new FormData();
        fd.append('new_room_number', roomNumber);
        fd.append('new_capacity', capacity);
        fd.append('new_rent', rent);
        fd.append('new_is_active', isActive);
        fd.append('new_facilities', facilities);

        var url;
        if (editMode && currentRoom && selectedRoomId) {
            url = '../controller/pages/RoomsController.php?action=updateRoom';
            fd.append('room_id', selectedRoomId);
        } else {
            url = '../controller/pages/RoomsController.php?action=addRoom';
        }

        postJSON(url, fd, function (err, data) {
            if (err || !data.success) {
                alert((data && data.message) || 'Failed to save room');
                return;
            }
            alert(data.message || 'Saved.');
            closeRoomModal();
            loadRoomsData();
        });
    });

    editRoomBtn.addEventListener('click', function () {
        openEditRoomModal();
    });

    removeRoomBtn.addEventListener('click', function () {
        if (!selectedRoomId) {
            alert('Please select a room first.');
            return;
        }

        if (!confirm('Are you sure you want to remove/close this room?')) {
            return;
        }

        var fd = new FormData();
        fd.append('room_id', selectedRoomId);

        postJSON('../controller/pages/RoomsController.php?action=removeRoom', fd, function (err, data) {
            if (err || !data.success) {
                alert((data && data.message) || 'Failed to remove room');
                return;
            }
            alert(data.message || 'Room removed.');
            selectedRoomId = null;
            currentRoom = null;
            roomDetailsMeta.textContent = 'No room selected';
            detailRoomNumber.textContent = '-';
            detailCapacity.textContent = '-';
            detailOccupancy.textContent = '-';
            detailVacant.textContent = '-';
            detailRent.textContent = '-';
            detailStatus.textContent = '-';
            detailMembers.textContent = '-';
            detailFacilities.textContent = '-';
            roomNotesList.innerHTML = '';

            loadRoomsData();
        });
    });

    loadRoomsData();
});