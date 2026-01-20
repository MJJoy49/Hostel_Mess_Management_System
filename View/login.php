<?php
require_once __DIR__ . '/../Controller/MainController.php';
redirectIfLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Create Hostel</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/login.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-switch">
            <button id="loginBtn">Login</button>
            <button id="createBtn">Create Hostel</button>
        </div>

        <!-- LOGIN FORM -->
        <form id="loginForm" class="auth-form">
            <h2>Login</h2>
            <input type="email" placeholder="Email">
            <input type="password" placeholder="Password">
            <a href="#forgot-pass" class="forgot-pass-link">FORGOT-PASS</a>
            <button type="submit">Login</button>
        </form>

        <!-- CREATE HOSTEL FORM -->
        <form id="createForm" class="auth-form hidden" enctype="multipart/form-data">
            <h2>Create Hostel</h2>
            
            <!-- ADMIN INFO -->
            <h3>Admin Information</h3>
            <input type="text" id="adminName" placeholder="Admin Name">

            <div class="gender-container">
                <label>Gender</label>
                <div class="radio-group">
                    <label><input type="radio" name="adminGender" value="Male" checked> Male</label>
                    <label><input type="radio" name="adminGender" value="Female"> Female</label>
                    <label><input type="radio" name="adminGender" value="Other"> Other</label>
                </div>
            </div>

            <input type="email" id="adminEmail" placeholder="Admin Email">
            <input type="password" id="adminPassword" placeholder="Password">
            <input type="password" id="adminRePassword" placeholder="Re-enter Password">
            <input type="text" id="adminPhone" placeholder="Phone Number">
            <input type="text" id="adminBloodGroup" placeholder="Blood Group">
            <input type="text" id="adminReligion" placeholder="Religion">
            <input type="text" id="adminProfession" placeholder="Profession (Job / Student)">
            <textarea id="adminAddress" placeholder="Admin Address"></textarea>
            
            <h6>Profile Pic (in .jpg, .jpeg, .png)</h6>
            <input type="file" id="adminPhoto" name="adminPhoto" accept=".jpg, .jpeg, .png">

            <!-- HOSTEL INFO -->
            <h3>Hostel Information</h3>
            <input type="text" id="hostelName" placeholder="Hostel Name">
            <textarea id="hostelAddress" placeholder="Address"></textarea>
            <input type="number" id="hostelSeats" placeholder="Total Seats">
            <input type="email" id="hostelOfficialEmail" placeholder="Hostel Official Email">
            <textarea id="hostelDescription" placeholder="Hostel Description (facilities, rules, etc.)"></textarea>
            
            <button type="submit">Create Hostel</button>
        </form>
    </div>

    <!-- ERROR MODAL -->
    <div id="errorModal" class="error-modal">
        <div class="error-modal-content">
            <span class="error-close">&times;</span>
            <p id="errorMessage"></p>
        </div>
    </div>

    <script src="./assets/js/login.js"></script>
</body>
</html>