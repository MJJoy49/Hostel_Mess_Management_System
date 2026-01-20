document.addEventListener('DOMContentLoaded', () => {
    const loginBtn = document.getElementById('loginBtn');
    const createBtn = document.getElementById('createBtn');
    const loginForm = document.getElementById('loginForm');
    const createForm = document.getElementById('createForm');

    const errorModal = document.getElementById('errorModal');
    const errorMessage = document.getElementById('errorMessage');
    const errorCloseBtn = document.querySelector('.error-close');

    function showError(msg) {
        if (!errorModal) {
            alert(msg);
            return;
        }
        errorMessage.textContent = msg;
        errorModal.style.display = 'flex';
    }

    function closeError() {
        if (errorModal) {
            errorModal.style.display = 'none';
        }
    }

    if (errorCloseBtn) {
        errorCloseBtn.addEventListener('click', closeError);
    }
    if (errorModal) {
        window.addEventListener('click', (e) => {
            if (e.target === errorModal) closeError();
        });
    }

    // Switch forms
    if (loginBtn && createBtn && loginForm && createForm) {
        loginBtn.addEventListener('click', () => {
            loginForm.classList.remove('hidden');
            createForm.classList.add('hidden');
        });

        createBtn.addEventListener('click', () => {
            createForm.classList.remove('hidden');
            loginForm.classList.add('hidden');
        });
    }

    // LOGIN
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const inputs = loginForm.querySelectorAll('input');
            const emailInput = inputs[0];
            const passInput = inputs[1];

            const email = emailInput.value.trim();
            const password = passInput.value;

            if (!email || !password) {
                showError('Please enter email and password');
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('Invalid email format');
                return;
            }

            try {
                const formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);

                const res = await fetch('../Controller/AuthController.php?action=login', {
                    method: 'POST',
                    body: formData
                });

                if (!res.ok) {
                    showError('Network error, please try again');
                    return;
                }

                const data = await res.json();

                if (data.success) {
                    window.location.href = data.redirect || 'main.php';
                } else {
                    showError(data.message || 'Login failed');
                }
            } catch (err) {
                showError('Unexpected error, please try again');
            }
        });
    }

    // CREATE HOSTEL
    if (createForm) {
        createForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const adminName = document.getElementById('adminName').value.trim();
            const adminEmail = document.getElementById('adminEmail').value.trim();
            const adminPassword = document.getElementById('adminPassword').value;
            const adminRePassword = document.getElementById('adminRePassword').value;
            const adminPhone = document.getElementById('adminPhone').value.trim();
            const adminBloodGroup = document.getElementById('adminBloodGroup').value.trim();
            const adminReligion = document.getElementById('adminReligion').value.trim();
            const adminProfession = document.getElementById('adminProfession').value.trim();
            const adminAddress = document.getElementById('adminAddress').value.trim();
            const adminGenderEl = document.querySelector('input[name="adminGender"]:checked');
            const adminGender = adminGenderEl ? adminGenderEl.value : 'Male';

            const hostelName = document.getElementById('hostelName').value.trim();
            const hostelAddress = document.getElementById('hostelAddress').value.trim();
            const hostelSeats = document.getElementById('hostelSeats').value;
            const hostelOfficialEmail = document.getElementById('hostelOfficialEmail').value.trim();
            const hostelDescription = document.getElementById('hostelDescription').value.trim();
            const adminPhoto = document.getElementById('adminPhoto');

            if (!adminName || !adminEmail || !adminPassword || !adminRePassword ||
                !adminPhone || !hostelName || !hostelAddress || !hostelSeats) {
                showError('Please fill all required fields');
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(adminEmail)) {
                showError('Invalid admin email');
                return;
            }

            if (hostelOfficialEmail && !emailRegex.test(hostelOfficialEmail)) {
                showError('Invalid hostel official email');
                return;
            }

            if (adminPassword.length < 6) {
                showError('Password must be at least 6 characters');
                return;
            }

            if (adminPassword !== adminRePassword) {
                showError('Password and confirm password do not match');
                return;
            }

            if (isNaN(Number(hostelSeats)) || Number(hostelSeats) <= 0) {
                showError('Total seats must be a positive number');
                return;
            }

            try {
                const formData = new FormData();
                formData.append('adminName', adminName);
                formData.append('adminGender', adminGender);
                formData.append('adminEmail', adminEmail);
                formData.append('adminPassword', adminPassword);
                formData.append('adminRePassword', adminRePassword);
                formData.append('adminPhone', adminPhone);
                formData.append('adminBloodGroup', adminBloodGroup);
                formData.append('adminReligion', adminReligion);
                formData.append('adminProfession', adminProfession);
                formData.append('adminAddress', adminAddress);

                formData.append('hostelName', hostelName);
                formData.append('hostelAddress', hostelAddress);
                formData.append('hostelSeats', hostelSeats);
                formData.append('hostelOfficialEmail', hostelOfficialEmail);
                formData.append('hostelDescription', hostelDescription);

                if (adminPhoto && adminPhoto.files[0]) {
                    formData.append('adminPhoto', adminPhoto.files[0]);
                }

                const res = await fetch('../Controller/AuthController.php?action=create_hostel', {
                    method: 'POST',
                    body: formData
                });

                if (!res.ok) {
                    showError('Network error, please try again');
                    return;
                }

                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Hostel created successfully. Now you can login.');
                    if (loginBtn) loginBtn.click();
                } else {
                    showError(data.message || 'Failed to create hostel');
                }
            } catch (err) {
                showError('Unexpected error, please try again');
            }
        });
    }
});