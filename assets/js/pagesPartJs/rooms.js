// rooms.js

.profile - container {
    max - width: 1100px;
    margin: 0 auto;
}

.page - title {
    font - size: var(--fs - 2xl);
    text - align: center;
    margin - bottom: 30px;
    color: var(--primary);
}

.profile - header {
    position: relative;
    background: var(--bg - card);
    border - radius: 16px;
    padding: 40px 30px 30px;
    box - shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    margin - bottom: 30px;
}

.profile - pic - container {
    text - align: center;
    margin - bottom: 20px;
}

.profile - pic {
    width: 150px;
    height: 150px;
    border - radius: 50 %;
    overflow: hidden;
    border: 5px solid var(--bg - main);
    margin: 0 auto 15px;
    box - shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
}

.profile - pic img {
    width: 100 %;
    height: 100 %;
    object - fit: cover;
}

#full_name {
    font - size: var(--fs - xl);
    margin - bottom: 10px;
}

.info - grid {
    display: grid;
    grid - template - columns: 1fr 1fr;
    gap: 25px;
    margin - top: 20px;
}

.info - card {
    background: var(--bg - main);
    padding: 20px;
    border - radius: 12px;
    border: 1px solid var(--border);
}

.info - card h3 {
    font - size: var(--fs - lg);
    color: var(--primary);
    margin - bottom: 15px;
    padding - bottom: 8px;
    border - bottom: 1px solid var(--border);
}

.info - item {
    display: flex;
    justify - content: space - between;
    padding: 10px 0;
    border - bottom: 1px dashed var(--border);
    font - size: var(--fs - md);
}

.info - item: last - child {
    border - bottom: none;
}

.info - item span: first - child {
    color: var(--text - secondary);
    min - width: 140px;
}

.info - item.desc {
    display: block;
}

.info - item.desc strong {
    display: block;
    margin - top: 8px;
    line - height: 1.5;
}

.edit - btn {
    position: absolute;
    top: 20px;
    left: 30px;
    background: var(--primary);
    color: white;
    border: none;
    padding: 10px 20px;
    border - radius: 8px;
    font - size: var(--fs - md);
    cursor: pointer;
    transition: 0.3s;
}

.edit - btn:hover {
    background: var(--primary - hover);
    transform: translateY(-2px);
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z - index: 1000;
    left: 0;
    top: 0;
    width: 100 %;
    height: 100 %;
    background - color: rgba(0, 0, 0, 0.7);
    backdrop - filter: blur(5px);
}

.modal - content {
    background: var(--bg - card);
    margin: 5 % auto;
    padding: 30px;
    border - radius: 16px;
    width: 90 %;
    max - width: 900px;
    max - height: 85vh;
    overflow - y: auto;
    position: relative;
    box - shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
}

.close {
    position: absolute;
    top: 15px;
    right: 25px;
    font - size: 32px;
    cursor: pointer;
    color: var(--text - secondary);
}

.close:hover {
    color: var(--error);
}

.modal - content h3 {
    text - align: center;
    font - size: var(--fs - xl);
    margin - bottom: 25px;
    color: var(--primary);
}

.form - grid {
    display: grid;
    grid - template - columns: repeat(auto - fit, minmax(300px, 1fr));
    gap: 15px;
}

.form - group {
    display: flex;
    flex - direction: column;
}

.form - group label {
    margin - bottom: 6px;
    color: var(--text - secondary);
    font - size: var(--fs - sm);
}

.form - group input,
.form - group select,
.form - group textarea {
    padding: 12px;
    border - radius: 8px;
    border: 1px solid var(--border);
    background: rgba(15, 23, 42, 0.8);
    color: var(--text - main);
    font - size: var(--fs - md);
}

.form - group textarea {
    min - height: 100px;
    resize: vertical;
}

.admin - only {
    opacity: 0.6;
    pointer - events: none;
}

.modal - actions {
    text - align: center;
    margin - top: 30px;
    display: flex;
    gap: 15px;
    justify - content: center;
}

.modal - actions button {
    padding: 12px 30px;
    border: none;
    border - radius: 8px;
    font - size: var(--fs - md);
    cursor: pointer;
}

#cancelBtn {
    background: var(--accent);
    color: white;
}

.modal - actions button[type = "submit"] {
    background: var(--primary);
    color: white;
}

.modal - actions button:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

@media(max - width: 768px) {
  .info - grid {
        grid - template - columns: 1fr;
    }
  
  .form - grid {
        grid - template - columns: 1fr;
    }
  
  .edit - btn {
        position: static;
        margin: 20px auto;
        display: block;
        width: fit - content;
    }
}



// ---------------------------------

<div class="profile-container">
    <div class="profile-header">
      <div class="profile-pic-container">
        <div class="profile-pic" id="profilePic">
          <img src="https://m.media-amazon.com/images/M/MV5BMjAwMjk3NDUzN15BMl5BanBnXkFtZTcwNjI4MTY0NA@@._V1_FMjpg_UX1000_.jpg" alt="Profile Picture" id="picImg">
        </div>
        <h1 id="full_name">John Doe</h1>
      </div>

      <div class="info-grid">
        <!-- User Information Card -->
        <div class="info-card">
          <h3>User Information</h3>
          <div class="info-item"><span>User ID:</span> <strong id="user_id">U12345</strong></div>
          <div class="info-item"><span>Full Name:</span> <strong id="display_full_name">John Doe</strong></div>
          <div class="info-item"><span>Gender:</span> <strong id="gender">Male</strong></div>
          <div class="info-item"><span>Contact Number:</span> <strong id="contact_number">+880 17xx xxxxxx</strong></div>
          <div class="info-item"><span>Email:</span> <strong id="email_id">john@example.com</strong></div>
          <div class="info-item"><span>Blood Group:</span> <strong id="blood_group">O+</strong></div>
          <div class="info-item"><span>Role:</span> <strong id="role">Member</strong></div>
          <div class="info-item"><span>Address:</span> <strong id="address">Dhaka, Bangladesh</strong></div>
          <div class="info-item"><span>Religion:</span> <strong id="religion">Islam</strong></div>
          <div class="info-item"><span>Profession:</span> <strong id="profession">Student</strong></div>
          <div class="info-item"><span>Joined Date:</span> <strong id="joined_date">15 March 2024</strong></div>
        </div>

        <!-- Mess Information Card -->
        <div class="info-card">
          <h3>Mess Information</h3>
          <div class="info-item"><span>Mess ID:</span> <strong id="mess_id">M001</strong></div>
          <div class="info-item"><span>Mess Name:</span> <strong id="mess_name">Sunrise Mess</strong></div>
          <div class="info-item"><span>Address:</span> <strong id="mess_address">Mirpur 10, Dhaka</strong></div>
          <div class="info-item"><span>Capacity:</span> <strong id="capacity">20 Members</strong></div>
          <div class="info-item"><span>Admin Name:</span> <strong id="admin_name">Admin Rahman</strong></div>
          <div class="info-item"><span>Admin Email:</span> <strong id="admin_email">admin@mess.com</strong></div>
          <div class="info-item"><span>Admin ID:</span> <strong id="admin_id">A001</strong></div>
          <div class="info-item"><span>Mess Email:</span> <strong id="mess_email_id">sunrise@mess.com</strong></div>
          <div class="info-item"><span>Created At:</span> <strong id="created_at">01 January 2023</strong></div>
          <div class="info-item desc"><span>Description:</span> <strong id="mess_description">A friendly and affordable mess for students and professionals in Mirpur area.</strong></div>
        </div>
      </div>

      <button class="edit-btn" id="editBtn">Edit Profile</button>
    </div>
  </div>

  <!--Edit Modal-- >
    <div class="modal" id="editModal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h3>Edit Profile Information</h3>
            <form id="editForm">
                <div class="form-grid">
                    <!-- User Fields -->
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="edit_full_name" value="John Doe">
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select id="edit_gender">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" id="edit_contact_number" value="+880 17xx xxxxxx">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="edit_email_id" value="john@example.com">
                    </div>
                    <div class="form-group">
                        <label>Blood Group</label>
                        <input type="text" id="edit_blood_group" value="O+">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" id="edit_address" value="Dhaka, Bangladesh">
                    </div>
                    <div class="form-group">
                        <label>Religion</label>
                        <input type="text" id="edit_religion" value="Islam">
                    </div>
                    <div class="form-group">
                        <label>Profession</label>
                        <input type="text" id="edit_profession" value="Student">
                    </div>

                    <!-- Mess Fields (Admin Only) -->
                    <div class="form-group admin-only">
                        <label>Mess Name</label>
                        <input type="text" id="edit_mess_name" value="Sunrise Mess">
                    </div>
                    <div class="form-group admin-only">
                        <label>Mess Address</label>
                        <input type="text" id="edit_mess_address" value="Mirpur 10, Dhaka">
                    </div>
                    <div class="form-group admin-only">
                        <label>Capacity</label>
                        <input type="text" id="edit_capacity" value="20 Members">
                    </div>
                    <div class="form-group admin-only">
                        <label>Mess Email</label>
                        <input type="email" id="edit_mess_email_id" value="sunrise@mess.com">
                    </div>
                    <div class="form-group admin-only">
                        <label>Mess Description</label>
                        <textarea id="edit_mess_description">A friendly and affordable mess for students and professionals in Mirpur area.</textarea>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" id="cancelBtn">Cancel</button>
                    <button type="submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
