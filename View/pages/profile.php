<div class="page-wrapper">
  <div class="profile-container">

    <!-- Header: photo + name + basic info -->
    <div class="profile-header">
      <div class="profile-main">
        <div class="profile-pic-container">
          <img id="profile_photo" src="" alt="Profile photo">
          <div class="profile-pic-initials" id="profile_initials"></div>
        </div>

        <div class="profile-basic">
          <h1 class="profile-name" id="header_full_name">John Doe</h1>
          <p class="profile-role" id="header_role">Student</p>
          <p class="profile-meta">
            <span id="header_mess_name">Demo Mess</span>
            <span class="meta-dot">•</span>
            <span>Joined: <span id="header_joined_date">2024-01-01</span></span>
          </p>
        </div>
      </div>

      <button type="button" class="edit-btn" id="editBtn">
        <span>Edit info</span>
      </button>
    </div>

    <!-- Main info sections -->
    <div class="profile-sections">
      <!-- Personal / User Info -->
      <section class="section-card">
        <h3 class="section-title">Personal Information</h3>
        <div class="info-grid">
          <div class="info-item">
            <div class="info-label">User ID</div>
            <div class="info-value" id="user_id"></div>
          </div>

          <div class="info-item">
            <div class="info-label">Full Name</div>
            <div class="info-value" id="full_name"></div>
          </div>

          <div class="info-item">
            <div class="info-label">Gender</div>
            <div class="info-value" id="gender"></div>
          </div>

          <div class="info-item">
            <div class="info-label">Contact Number</div>
            <div class="info-value" id="contact_number"></div>
          </div>

          <div class="info-item">
            <div class="info-label">Email</div>
            <div class="info-value" id="email_id"></div>
          </div>

          <div class="info-item">
            <div class="info-label">Blood Group</div>
            <div class="info-value" id="blood_group"></div>
          </div>

          <div class="info-item">
            <div class="info-label">Role</div>
            <div class="info-value" id="role"></div>
          </div>

          <div class="info-item">
            <div class="info-label">Religion</div>
            <div class="info-value" id="religion"></div>
          </div>

          <div class="info-item">
            <div class="info-label">Profession</div>
            <div class="info-value" id="profession"></div>
          </div>

          <div class="info-item">
            <div class="info-label">Joined Date</div>
            <div class="info-value" id="joined_date"></div>
          </div>

          <div class="info-item info-item--wide">
            <div class="info-label">Address</div>
            <div class="info-value" id="user_address"></div>
          </div>
        </div>
      </section>

      <!-- Mess Info -->
      <section class="section-card">
        <h3 class="section-title">Mess / Hostel Information</h3>
        <div class="info-grid">
          <div class="info-item">
            <div class="info-label">Mess ID</div>
            <div class="info-value" id="mess_id"></div>
          </div>

          <div class="info-item">
            <div class="info-label">Mess Name</div>
            <div class="info-value" id="mess_name"></div>
          </div>

          <div class="info-item">
            <div class="info-label">Capacity</div>
            <div class="info-value" id="capacity"></div>
          </div>

          <div class="info-item">
            <div class="info-label">Mess Email</div>
            <div class="info-value" id="mess_email_id"></div>
          </div>

          <div class="info-item">
            <div class="info-label">Admin Name</div>
            <div class="info-value" id="admin_name"></div>
          </div>

          <div class="info-item">
            <div class="info-label">Admin Email</div>
            <div class="info-value" id="admin_email"></div>
          </div>

          <div class="info-item">
            <div class="info-label">Admin ID</div>
            <div class="info-value" id="admin_id"></div>
          </div>

          <div class="info-item">
            <div class="info-label">Created At</div>
            <div class="info-value" id="created_at"></div>
          </div>

          <div class="info-item info-item--wide">
            <div class="info-label">Mess Address</div>
            <div class="info-value" id="mess_address"></div>
          </div>

          <div class="info-item info-item--wide">
            <div class="info-label">Mess Description</div>
            <div class="info-value" id="mess_description"></div>
          </div>
        </div>
      </section>
    </div>
  </div>

  <!-- Edit Modal -->
  <div class="modal" id="editModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Edit Information</h3>
        <button type="button" class="close-btn" id="closeModal">&times;</button>
      </div>

      <!-- form direct child -->
      <form id="editForm" enctype="multipart/form-data">
        <div class="modal-body">
          <h4 class="modal-section-title">User Details</h4>

          <div class="form-grid">
            <!-- Read-only fields -->
            <div class="form-group">
              <label for="edit_user_id">User ID (read only)</label>
              <input type="text" id="edit_user_id" class="form-control" disabled>
            </div>

            <div class="form-group">
              <label for="edit_role">Role (read only)</label>
              <input type="text" id="edit_role" class="form-control" disabled>
            </div>

            <div class="form-group">
              <label for="edit_joined_date">Joined Date (read only)</label>
              <input type="text" id="edit_joined_date" class="form-control" disabled>
            </div>

            <!-- Editable user fields -->
            <div class="form-group">
              <label for="edit_full_name">Full Name</label>
              <input type="text" id="edit_full_name" class="form-control">
            </div>

            <div class="form-group">
              <label for="edit_gender">Gender</label>
              <select id="edit_gender" class="form-control">
                <option value="">Select</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div class="form-group">
              <label for="edit_contact_number">Contact Number</label>
              <input type="text" id="edit_contact_number" class="form-control">
            </div>

            <div class="form-group">
              <label for="edit_email_id">Email</label>
              <input type="email" id="edit_email_id" class="form-control">
            </div>

            <div class="form-group">
              <label for="edit_blood_group">Blood Group</label>
              <select id="edit_blood_group" class="form-control">
                <option value="">Select</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
              </select>
            </div>

            <div class="form-group">
              <label for="edit_religion">Religion</label>
              <input type="text" id="edit_religion" class="form-control">
            </div>

            <div class="form-group">
              <label for="edit_profession">Profession</label>
              <input type="text" id="edit_profession" class="form-control">
            </div>

            <div class="form-group form-group--wide">
              <label for="edit_user_address">Address</label>
              <textarea id="edit_user_address" class="form-control"></textarea>
            </div>

            <div class="form-group form-group--wide">
              <label for="edit_photo_file">Profile Photo</label>
              <input type="file" id="edit_photo_file" class="form-control" accept="image/*">
              <span class="input-inline-help">Choose an image to upload. Leave empty to keep current.</span>
            </div>
          </div>

          <h4 class="modal-section-title">Mess Details</h4>

          <div class="form-grid">
            <!-- Read-only mess fields -->
            <div class="form-group">
              <label for="edit_mess_id">Mess ID (read only)</label>
              <input type="text" id="edit_mess_id" class="form-control" disabled>
            </div>

            <div class="form-group">
              <label for="edit_admin_name">Admin Name (read only)</label>
              <input type="text" id="edit_admin_name" class="form-control" disabled>
            </div>

            <div class="form-group">
              <label for="edit_admin_email">Admin Email (read only)</label>
              <input type="email" id="edit_admin_email" class="form-control" disabled>
            </div>

            <div class="form-group">
              <label for="edit_admin_id">Admin ID (read only)</label>
              <input type="text" id="edit_admin_id" class="form-control" disabled>
            </div>

            <div class="form-group">
              <label for="edit_created_at">Created At (read only)</label>
              <input type="text" id="edit_created_at" class="form-control" disabled>
            </div>

            <!-- Admin-only editable fields -->
            <div class="form-group">
              <label for="edit_mess_name">
                Mess Name
                <span class="badge-admin">Admin only</span>
              </label>
              <input type="text" id="edit_mess_name" class="form-control">
            </div>

            <div class="form-group">
              <label for="edit_capacity">
                Capacity
                <span class="badge-admin">Admin only</span>
              </label>
              <input type="number" id="edit_capacity" class="form-control" min="0">
            </div>

            <div class="form-group">
              <label for="edit_mess_email_id">
                Mess Email
                <span class="badge-admin">Admin only</span>
              </label>
              <input type="email" id="edit_mess_email_id" class="form-control">
            </div>

            <div class="form-group form-group--wide">
              <label for="edit_mess_address">
                Mess Address
                <span class="badge-admin">Admin only</span>
              </label>
              <textarea id="edit_mess_address" class="form-control"></textarea>
            </div>

            <div class="form-group form-group--wide">
              <label for="edit_mess_description">
                Mess Description
                <span class="badge-admin">Admin only</span>
              </label>
              <textarea id="edit_mess_description" class="form-control"></textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="cancelEdit">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>