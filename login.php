<!DOCTYPE html>
<html lang="en" >
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

        <input type="email" placeholder="Email" required>
        <input type="password" placeholder="Password" required>

        <button type="submit">Login</button>
    </form>

    <!-- CREATE HOSTEL FORM -->
    <form id="createForm" class="auth-form hidden">
        <h2>Create Hostel</h2>

        <!-- ADMIN INFO -->
        <h3>Admin Information</h3>

        <input type="text" id="adminName" placeholder="Admin Name" required>
        <input type="email" id="adminEmail" placeholder="Admin Email" required>
        <input type="password" id="adminPassword" placeholder="Password" required>
        <input type="password" id="adminRePassword" placeholder="Re-enter Password" required>
        <input type="text" id="adminPhone" placeholder="Phone Number" required>

        <!-- HOSTEL INFO -->
        <h3>Hostel Information</h3>

        <input type="text" id="hostelName" placeholder="Hostel Name" required>
        <textarea id="hostelAddress" placeholder="Address" required></textarea>
        <input type="number" id="hostelSeats" placeholder="Total Seats" required>

        <button type="submit">Create Hostel</button>
    </form>


</div>

<script src="./assets/js/login.js"></script>

</body>
</html>
