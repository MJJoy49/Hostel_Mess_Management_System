// View/assets/js/pagesPartJs/profile.js

document.addEventListener('DOMContentLoaded', function () {
    // Header
    var profilePhoto = document.getElementById('profile_photo');
    var profileInitials = document.getElementById('profile_initials');
    var headerFullName = document.getElementById('header_full_name');
    var headerRole = document.getElementById('header_role');
    var headerMessName = document.getElementById('header_mess_name');
    var headerJoinedDate = document.getElementById('header_joined_date');

    // User info
    var userIdEl = document.getElementById('user_id');
    var fullNameEl = document.getElementById('full_name');
    var genderEl = document.getElementById('gender');
    var contactEl = document.getElementById('contact_number');
    var emailEl = document.getElementById('email_id');
    var bloodEl = document.getElementById('blood_group');
    var roleEl = document.getElementById('role');
    var religionEl = document.getElementById('religion');
    var professionEl = document.getElementById('profession');
    var joinedDateEl = document.getElementById('joined_date');
    var userAddressEl = document.getElementById('user_address');

    // Mess info
    var messIdEl = document.getElementById('mess_id');
    var messNameEl = document.getElementById('mess_name');
    var capacityEl = document.getElementById('capacity');
    var messEmailEl = document.getElementById('mess_email_id');
    var adminNameEl = document.getElementById('admin_name');
    var adminEmailEl = document.getElementById('admin_email');
    var adminIdEl = document.getElementById('admin_id');
    var createdAtEl = document.getElementById('created_at');
    var messAddressEl = document.getElementById('mess_address');
    var messDescEl = document.getElementById('mess_description');

    // Modal
    var editBtn = document.getElementById('editBtn');
    var editModal = document.getElementById('editModal');
    var closeModalBtn = document.getElementById('closeModal');
    var cancelEditBtn = document.getElementById('cancelEdit');
    var editForm = document.getElementById('editForm');

    // Modal fields
    var editUserId = document.getElementById('edit_user_id');
    var editRole = document.getElementById('edit_role');
    var editJoined = document.getElementById('edit_joined_date');
    var editFullName = document.getElementById('edit_full_name');
    var editGender = document.getElementById('edit_gender');
    var editContact = document.getElementById('edit_contact_number');
    var editEmail = document.getElementById('edit_email_id');
    var editBlood = document.getElementById('edit_blood_group');
    var editReligion = document.getElementById('edit_religion');
    var editProfession = document.getElementById('edit_profession');
    var editAddress = document.getElementById('edit_user_address');
    var editPhotoFile = document.getElementById('edit_photo_file');

    var editMessId = document.getElementById('edit_mess_id');
    var editAdminName = document.getElementById('edit_admin_name');
    var editAdminEmail = document.getElementById('edit_admin_email');
    var editAdminId = document.getElementById('edit_admin_id');
    var editCreatedAt = document.getElementById('edit_created_at');
    var editMessName = document.getElementById('edit_mess_name');
    var editCapacity = document.getElementById('edit_capacity');
    var editMessEmail = document.getElementById('edit_mess_email_id');
    var editMessAddress = document.getElementById('edit_mess_address');
    var editMessDesc = document.getElementById('edit_mess_description');

    var currentRole = 'member';
    var currentData = null;

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
            .replace(/>/g, '&gt;');
    }

    function showPhotoOrInitials(fullName, photoBase64) {
        if (photoBase64) {
            profilePhoto.src = 'data:image/jpeg;base64,' + photoBase64;
            profilePhoto.style.display = 'block';
            profileInitials.style.display = 'none';
        } else {
            profilePhoto.style.display = 'none';
            profileInitials.style.display = 'flex';
            var initials = '';
            if (fullName) {
                var parts = fullName.trim().split(/\s+/);
                initials = parts[0].charAt(0);
                if (parts.length > 1) {
                    initials += parts[parts.length - 1].charAt(0);
                }
            }
            profileInitials.textContent = initials.toUpperCase() || '?';
        }
    }

    function loadProfile() {
        getJSON('../Controller/pages/ProfileController.php?action=getProfile', function (err, data) {
            if (err || !data.success) {
                console.error('Failed to load profile', err || data);
                return;
            }
            currentData = data;
            currentRole = data.role || 'member';

            var u = data.user;
            var m = data.mess;

            // header
            headerFullName.textContent = u.full_name || '';
            headerRole.textContent = u.role || '';
            headerMessName.textContent = m.mess_name || '';
            headerJoinedDate.textContent = u.joined_date || '';

            showPhotoOrInitials(u.full_name, u.photo_base64 || '');

            // user section
            userIdEl.textContent = u.user_id || '';
            fullNameEl.textContent = u.full_name || '';
            genderEl.textContent = u.gender || '';
            contactEl.textContent = u.contact_number || '';
            emailEl.textContent = u.email_id || '';
            bloodEl.textContent = u.blood_group || '';
            roleEl.textContent = u.role || '';
            religionEl.textContent = u.religion || '';
            professionEl.textContent = u.profession || '';
            joinedDateEl.textContent = u.joined_date || '';
            userAddressEl.textContent = u.address || '';

            // mess section
            messIdEl.textContent = m.mess_id || '';
            messNameEl.textContent = m.mess_name || '';
            capacityEl.textContent = m.capacity || '';
            messEmailEl.textContent = m.email_id || '';
            adminNameEl.textContent = m.admin_name || '';
            adminEmailEl.textContent = m.admin_email || '';
            adminIdEl.textContent = m.admin_id || '';
            createdAtEl.textContent = m.created_at || '';
            messAddressEl.textContent = m.address || '';
            messDescEl.textContent = m.mess_description || '';
        });
    }

    function openModal() {
        if (!currentData) return;

        var u = currentData.user;
        var m = currentData.mess;

        editUserId.value = u.user_id || '';
        editRole.value = u.role || '';
        editJoined.value = u.joined_date || '';
        editFullName.value = u.full_name || '';
        editGender.value = u.gender || '';
        editContact.value = u.contact_number || '';
        editEmail.value = u.email_id || '';
        editBlood.value = u.blood_group || '';
        editReligion.value = u.religion || '';
        editProfession.value = u.profession || '';
        editAddress.value = u.address || '';

        editMessId.value = m.mess_id || '';
        editAdminName.value = m.admin_name || '';
        editAdminEmail.value = m.admin_email || '';
        editAdminId.value = m.admin_id || '';
        editCreatedAt.value = m.created_at || '';
        editMessName.value = m.mess_name || '';
        editCapacity.value = m.capacity || '';
        editMessEmail.value = m.email_id || '';
        editMessAddress.value = m.address || '';
        editMessDesc.value = m.mess_description || '';

        if (currentRole !== 'admin') {
            editMessName.disabled = true;
            editCapacity.disabled = true;
            editMessEmail.disabled = true;
            editMessAddress.disabled = true;
            editMessDesc.disabled = true;
        } else {
            editMessName.disabled = false;
            editCapacity.disabled = false;
            editMessEmail.disabled = false;
            editMessAddress.disabled = false;
            editMessDesc.disabled = false;
        }

        editModal.classList.add('open');
        document.body.classList.add('modal-open');
    }

    function closeModal() {
        editModal.classList.remove('open');
        document.body.classList.remove('modal-open');
        editForm.reset();
    }

    editBtn.addEventListener('click', openModal);
    closeModalBtn.addEventListener('click', closeModal);
    cancelEditBtn.addEventListener('click', closeModal);

    editForm.addEventListener('submit', function (e) {
        e.preventDefault();

        var fd = new FormData();
        fd.append('full_name', editFullName.value.trim());
        fd.append('gender', editGender.value);
        fd.append('contact_number', editContact.value.trim());
        fd.append('email_id', editEmail.value.trim());
        fd.append('blood_group', editBlood.value);
        fd.append('religion', editReligion.value.trim());
        fd.append('profession', editProfession.value.trim());
        fd.append('address', editAddress.value.trim());

        if (editPhotoFile.files[0]) {
            fd.append('photo_file', editPhotoFile.files[0]);
        }

        if (currentRole === 'admin') {
            fd.append('mess_name', editMessName.value.trim());
            fd.append('capacity', editCapacity.value);
            fd.append('mess_email_id', editMessEmail.value.trim());
            fd.append('mess_address', editMessAddress.value.trim());
            fd.append('mess_description', editMessDesc.value.trim());
        }

        postJSON('../Controller/pages/ProfileController.php?action=updateProfile', fd, function (err, data) {
            if (err || !data.success) {
                alert((data && data.message) || 'Failed to update profile');
                return;
            }
            alert(data.message || 'Profile updated.');
            closeModal();
            loadProfile();
        });
    });

    loadProfile();
});