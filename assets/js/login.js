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

loginForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const email = loginForm.querySelector('input[type="email"]').value;
    const password = loginForm.querySelector('input[type="password"]').value;

    if (email === "" || password === "") {
        alert("All fields are required");
        return;
    }

    alert("Login OK\nEmail: " + email);

    loginForm.reset();

    window.location.href = "../../main.php";
});


// ================== CREATE HOSTEL VALIDATION ==================

createForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const adminName = document.getElementById("adminName").value.trim();
    const adminEmail = document.getElementById("adminEmail").value.trim();
    const adminPassword = document.getElementById("adminPassword").value;
    const adminRePassword = document.getElementById("adminRePassword").value;
    const adminPhone = document.getElementById("adminPhone").value.trim();

    const hostelName = document.getElementById("hostelName").value.trim();
    const hostelAddress = document.getElementById("hostelAddress").value.trim();
    const hostelSeats = document.getElementById("hostelSeats").value;

    // ===== EMPTY CHECK =====
    if (
        adminName === "" || adminEmail === "" || adminPassword === "" ||
        adminRePassword === "" || adminPhone === "" ||
        hostelName === "" || hostelAddress === "" || hostelSeats === ""
    ) {
        alert("Please fill all fields");
        return;
    }

    // ===== PASSWORD MATCH CHECK =====
    if (adminPassword !== adminRePassword) {
        alert("Password and Re-password do not match");
        return;
    }

    // ===== PASSWORD LENGTH =====
    if (adminPassword.length < 6) {
        alert("Password must be at least 6 characters");
        return;
    }

    // ===== PHONE NUMBER BASIC CHECK =====
    if (adminPhone.length < 10) {
        alert("Enter a valid phone number");
        return;
    }

    alert(
        "Create Hostel OK\n" +
        "Admin: " + adminName + "\n" +
        "Hostel: " + hostelName
    );

    createForm.reset();

    window.location.href = "../../../Hostel_Mess_Management_System/main.php";

    //Next step: fetch() → PHP → MySQL
});
