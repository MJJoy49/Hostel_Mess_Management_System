<?php
$ad_id = isset($_GET['ad_id']) ? (int)$_GET['ad_id'] : 0;
if ($ad_id <= 0) {
    die('Invalid ad');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply for Seat</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .apply-card {
            max-width: 480px;
            margin: 30px auto;
            padding: 20px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
        }
        .apply-card h2 {
            margin-bottom: 12px;
        }
        .apply-form-group {
            margin-bottom: 10px;
        }
        .apply-form-group label {
            display: block;
            font-size: 12px;
            margin-bottom: 4px;
            color: var(--text-secondary);
        }
        .apply-form-group input,
        .apply-form-group textarea {
            width: 100%;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--bg-main);
            color: var(--text-main);
        }
        .apply-btn {
            margin-top: 10px;
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            background: var(--primary);
            color: #111827;
            cursor: pointer;
        }
    </style>
</head>
<body>
<header class="header">
    <div class="logo">Apply for Seat</div>
    <div class="header-actions">
        <a href="../index1.php" class="login-btn">Home</a>
    </div>
</header>

<main class="main">
    <div class="apply-card">
        <h2>Seat Request</h2>
        <form id="applyForm">
            <input type="hidden" id="ad_id" value="<?= $ad_id ?>">

            <div class="apply-form-group">
                <label for="name">Your Name *</label>
                <input type="text" id="name" required>
            </div>

            <div class="apply-form-group">
                <label for="contact_number">Contact Number *</label>
                <input type="text" id="contact_number" required>
            </div>

            <div class="apply-form-group">
                <label for="profession">Profession</label>
                <input type="text" id="profession" placeholder="Student / Job / Business">
            </div>

            <div class="apply-form-group">
                <label for="description">Short Description</label>
                <textarea id="description" rows="3"></textarea>
            </div>

            <button type="submit" class="apply-btn">Submit Request</button>
        </form>
        <p id="applyMessage" style="margin-top:8px;font-size:13px;"></p>
    </div>
</main>

<script>
document.getElementById('applyForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const ad_id       = document.getElementById('ad_id').value;
    const name        = document.getElementById('name').value.trim();
    const contact     = document.getElementById('contact_number').value.trim();
    const profession  = document.getElementById('profession').value.trim();
    const description = document.getElementById('description').value.trim();
    const msgEl       = document.getElementById('applyMessage');

    if (!name || !contact) {
        msgEl.textContent = 'Name and contact are required.';
        msgEl.style.color = 'red';
        return;
    }

    const formData = new FormData();
    formData.append('ad_id', ad_id);
    formData.append('name', name);
    formData.append('contact_number', contact);
    formData.append('profession', profession);
    formData.append('description', description);

    try {
        const res = await fetch('../../Controller/visitor/ApplyController.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();
        msgEl.textContent = data.message || 'Done.';
        msgEl.style.color = data.success ? 'lightgreen' : 'red';

        if (data.success) {
            this.reset();
        }
    } catch (err) {
        msgEl.textContent = 'Error, please try again.';
        msgEl.style.color = 'red';
    }
});
</script>
</body>
</html>