<!-- <div class="hostel-profile">
    ai file hostel a kaj korte hobe------
    asset/css/pagesPartCss/hostel.css ar kaj
    asset/css/pagesPartJs/hostel.js ar kaj
</div> -->

<div class="hostel-profile">
    <h2 class="page-title">Hostel Profile</h2>

    <div class="info-grid">
        <div class="info-box">
            <span class="label">Hostel ID</span>
            <span class="value" id="hostel_id">Loading...</span>
        </div>

        <div class="info-box">
            <span class="label">Hostel Name</span>
            <span class="value" id="hostel_name">Loading...</span>
        </div>

        <div class="info-box">
            <span class="label">Address</span>
            <span class="value" id="address">Loading...</span>
        </div>

        <div class="info-box">
            <span class="label">Total Seats</span>
            <span class="value" id="total_seats">Loading...</span>
        </div>

        <div class="info-box">
            <span class="label">Admin ID</span>
            <span class="value" id="admin_id">Loading...</span>
        </div>
    </div>

    <button class="edit-btn" id="editBtn">Edit Profile</button>
</div>

<!-- Edit Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <h3>Edit Hostel Information</h3>
        <form id="editForm">
            <label>Hostel ID</label>
            <input type="text" id="edit_hostel_id" disabled>

            <label>Hostel Name</label>
            <input type="text" id="edit_hostel_name">

            <label>Address</label>
            <input type="text" id="edit_address">

            <label>Total Seats</label>
            <input type="number" id="edit_total_seats">

            <label>Admin ID</label>
            <input type="text" id="edit_admin_id" disabled>

            <div class="modal-actions">
                <button type="button" id="cancelBtn">Cancel</button>
                <button type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>