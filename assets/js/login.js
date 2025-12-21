// ================== THEME APPLY ==================
const savedTheme = sessionStorage.getItem("theme");
if (savedTheme) {
    document.documentElement.dataset.theme = savedTheme;
} else {
    document.documentElement.dataset.theme = "dark";
}

// ================== ELEMENTS ==================
const loginBtn = document.getElementById("loginBtn");
const createBtn = document.getElementById("createBtn");
const loginForm = document.getElementById("loginForm");
const createForm = document.getElementById("createForm");

// ================== ERROR MODAL FUNCTION ==================
function showError(message) {
    const modal = document.getElementById("errorModal");
    const msgElement = document.getElementById("errorMessage");
    msgElement.textContent = message;
    modal.style.display = "flex"; // show modal
}

// Close modal when clicking × or outside
document.querySelector(".error-close").addEventListener("click", function () {
    document.getElementById("errorModal").style.display = "none";
});

window.addEventListener("click", function (e) {
    const modal = document.getElementById("errorModal");
    if (e.target === modal) {
        modal.style.display = "none";
    }
});

// ================== FORM TOGGLE ==================
loginBtn.addEventListener("click", function () {
    loginForm.classList.remove("hidden");
    createForm.classList.add("hidden");
});

createBtn.addEventListener("click", function () {
    loginForm.classList.add("hidden");
    createForm.classList.remove("hidden");
});

// ================== LOGIN VALIDATION ==================
loginForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    //email and password input
    var emailInput = loginForm.querySelector('input[type="email"]');
    var passwordInput = loginForm.querySelector('input[type="password"]');
    var email = emailInput.value.trim();
    var password = passwordInput.value;

    // check value not null
    if (email === "" || password === "") {
        showError("Please fill Email and Password. Empty value is not allowed.");
        return;
    }

    console.log("Login data is ready:", email, password);

    // Check if email already exists (for login we allow if exists)
    const emailAvailable = await valueCheckExistOrNot("email", email);
    if (emailAvailable) {
        showError("Email not found. Please register first or check your email.");
        return;
    }

    // Send login data to server (you can create separate login.php later)
    const loginData = { email: email, password: password };

    try {
        const response = await fetch("/Hostel_Mess_Management_System/api/authenticationCheck.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(loginData)
        });

        const result = await response.json();

        if (response.ok && result.success) {
            showError("Login successful! Redirecting...");
            createForm.reset();
            window.location.href = "/Hostel_Mess_Management_System/main.php";
        } else {
            showError(result.msg || "Invalid email or password.");
        }
    } catch (err) {
        console.error(err);
        showError("Network error. Please try again.");
    }
});


// ================== CREATE HOSTEL VALIDATION ==================
createForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    // Admin Name
    const adminName = document.getElementById("adminName").value.trim();
    if (adminName === "") {
        showError("Please enter Admin Name.");
        document.getElementById("adminName").focus();
        return;
    }

    // Gender
    const adminGender = document.querySelector('input[name="adminGender"]:checked');
    if (!adminGender) {
        showError("Please select Gender.");
        document.querySelector('input[name="adminGender"]').focus();
        return;
    }
    const genderValue = adminGender.value;

    // Admin Email
    const adminEmail = document.getElementById("adminEmail").value.trim();
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(adminEmail)) {
        showError("Please enter a valid Admin Email address.");
        document.getElementById("adminEmail").focus();
        return;
    }

    // Check if email already exists
    const emailAvailable = await valueCheckExistOrNot("email", adminEmail);
    if (!emailAvailable) {
        showError("This email is already registered. Please use another email.");
        document.getElementById("adminEmail").focus();
        return;
    }

    // Hostel Name
    const hostelName = document.getElementById("hostelName").value.trim();
    if (hostelName === "") {
        showError("Please enter Hostel Name.");
        document.getElementById("hostelName").focus();
        return;
    }

    // Check if hostel name already exists
    const hostelNameAvailable = await valueCheckExistOrNot("hostel_name", hostelName);
    if (!hostelNameAvailable) {
        showError("This hostel name is already taken. Please choose another.");
        document.getElementById("hostelName").focus();
        return;
    }

    // Password validation
    const adminPassword = document.getElementById("adminPassword").value;
    const adminRePassword = document.getElementById("adminRePassword").value;
    if (adminPassword.length < 6) {
        showError("Password must be at least 6 characters long.");
        document.getElementById("adminPassword").focus();
        return;
    }
    if (adminPassword !== adminRePassword) {
        showError("Password and Re-enter Password do not match.");
        document.getElementById("adminRePassword").focus();
        return;
    }

    // Phone Number
    const adminPhone = document.getElementById("adminPhone").value.trim();
    const phonePattern = /^01[3-9]\d{8}$/;
    if (!phonePattern.test(adminPhone)) {
        showError("Please enter a valid Bangladeshi phone number (e.g., 017XXXXXXXX).");
        document.getElementById("adminPhone").focus();
        return;
    }

    // Blood Group
    const adminBloodGroup = document.getElementById("adminBloodGroup").value.trim().toUpperCase();
    const validBloodGroups = ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"];
    if (!validBloodGroups.includes(adminBloodGroup)) {
        showError("Please enter a valid Blood Group (A+, A-, B+, B-, AB+, AB-, O+, O-).");
        document.getElementById("adminBloodGroup").focus();
        return;
    }

    // Religion
    const adminReligion = document.getElementById("adminReligion").value.trim();
    if (adminReligion === "") {
        showError("Please enter Religion.");
        document.getElementById("adminReligion").focus();
        return;
    }

    // Profession
    const adminProfession = document.getElementById("adminProfession").value.trim();
    if (adminProfession === "") {
        showError("Please enter Profession (Job / Student).");
        document.getElementById("adminProfession").focus();
        return;
    }

    // Admin Address
    const adminAddress = document.getElementById("adminAddress").value.trim();
    if (adminAddress === "") {
        showError("Please enter Admin Address.");
        document.getElementById("adminAddress").focus();
        return;
    }

    // Hostel Address
    const hostelAddress = document.getElementById("hostelAddress").value.trim();
    if (hostelAddress === "") {
        showError("Please enter Hostel Address.");
        document.getElementById("hostelAddress").focus();
        return;
    }

    // Total Seats
    const hostelSeatsInput = document.getElementById("hostelSeats");
    const hostelSeats = Number(hostelSeatsInput.value);
    if (isNaN(hostelSeats) || hostelSeats < 1 || hostelSeats > 50) {
        showError("Total Seats must be a number between 1 and 50.");
        hostelSeatsInput.focus();
        return;
    }

    // Hostel Description
    const hostelDescription = document.getElementById("hostelDescription").value.trim();
    if (hostelDescription === "") {
        showError("Please enter Hostel Description (facilities, rules, etc.).");
        document.getElementById("hostelDescription").focus();
        return;
    }

    // Admin Photo File
    const adminPhotoInput = document.getElementById("adminPhoto");
    const adminPhotoFile = adminPhotoInput.files[0];
    if (!adminPhotoFile) {
        showError("Please select an Admin Profile Picture.");
        adminPhotoInput.focus();
        return;
    }

    // File type & size check
    const allowedTypes = ["image/jpeg", "image/jpg", "image/png"];
    if (!allowedTypes.includes(adminPhotoFile.type)) {
        showError("Profile picture must be in .jpg, .jpeg, or .png format.");
        adminPhotoInput.focus();
        return;
    }
    if (adminPhotoFile.size > 2 * 1024 * 1024) {
        showError("Profile picture size must be less than 2MB.");
        adminPhotoInput.focus();
        return;
    }

    // Optional hostel official email
    const hostelOfficialEmail = document.getElementById("hostelOfficialEmail").value.trim();

    // ---------- Get current year ----------
    const today = new Date();
    const year = today.getFullYear(); // example: 2025

    // ---------- Generate Admin ID and Mess ID ----------
    let adminId = "";
    let messId = "";

    try {
        // Get Admin ID (type: ADMIN)
        const adminResponse = await fetch("/Hostel_Mess_Management_System/api/idGenerator.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ type: "ADMIN", year: year })
        });
        const adminResult = await adminResponse.json();
        if (adminResult.success) {
            adminId = adminResult.id; // example: AD01-001-25
        } else {
            showError(adminResult.msg || "Cannot create Admin ID");
            return;
        }

        // Get Mess ID (type: MESS)
        const messResponse = await fetch("/Hostel_Mess_Management_System/api/idGenerator.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ type: "MESS", year: year })
        });
        const messResult = await messResponse.json();
        if (messResult.success) {
            messId = messResult.id; // example: MES01-0001-25
        } else {
            showError(messResult.msg || "Cannot create Mess ID");
            return;
        }
    } catch (err) {
        showError("Cannot connect to server for ID");
        return;
    }

    // ---------- All validations passed – Now send to server ----------

    // photo → Base64 convert
    function fileToBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    let adminPhotoBase64;
    try {
        adminPhotoBase64 = await fileToBase64(adminPhotoFile);
    } catch (e) {
        showError("Cannot read photo file.");
        return;
    }

    try {
        showError("Creating hostel... Please wait.");

        const response = await fetch("/Hostel_Mess_Management_System/api/createMess.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                adminName,
                adminGender: genderValue,
                adminEmail,
                adminPassword,
                adminPhone,
                adminBloodGroup,
                adminReligion,
                adminProfession,
                adminAddress,
                hostelName,
                hostelAddress,
                hostelSeats,
                hostelOfficialEmail,
                hostelDescription,
                adminId,
                messId,
                adminPhotoBase64
            })
        });

        const result = await response.json();

        if (response.ok && result.success) {
            showError("Hostel created successfully! You can now log in.");
            createForm.reset();
            loginBtn.click();
        } else {
            showError(result.msg || "Failed to create hostel.");
        }

    } catch (err) {
        console.error(err);
        showError("Network error. Please try again.");
    }

});

// CHECK IF VALUE EXISTS (email, hostel_name, admin_name)
async function valueCheckExistOrNot(field, value) {
    const Data = {
        field: field,    // "email" or "hostel_name" or "admin_name"
        value: value
    };

    try {
        const response = await fetch("/Hostel_Mess_Management_System/api/valueCheckExistOrNot.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(Data)
        });

        const result = await response.json();

        return result.success !== "exist";

    } catch (err) {
        console.error("Check existence error:", err);
        showError("Server connection error while checking availability.");
        return false;
    }
}