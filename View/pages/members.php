<div class="members">
    <!-- Header -->
    <div class="members-header">
        <div>
            <h1 class="page-title">Members</h1>
            <p class="members-subtitle" id="membersSubtitle">
                Members overview
            </p>
        </div>
        <div class="members-header-right">
            <!-- JS + PHP role check diye admin holei Add Member button visible hobe -->
            <button type="button" class="btn btn-primary" id="addMemberBtn">
                Add Member
            </button>
        </div>
    </div>

    <!-- Top stats -->
    <section class="members-stat-grid">
        <article class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Total Members</span>
                <span class="stat-pill stat-pill--green">All</span>
            </div>
            <div class="stat-value" id="stat_total_members">0</div>
            <p class="stat-subtext">All members in this mess</p>
        </article>

        <article class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Active</span>
                <span class="stat-pill stat-pill--blue">Active</span>
            </div>
            <div class="stat-value" id="stat_active_members">0</div>
            <p class="stat-subtext">Currently staying in rooms</p>
        </article>

        <article class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">On Leave</span>
                <span class="stat-pill stat-pill--amber">Leave</span>
            </div>
            <div class="stat-value" id="stat_on_leave">0</div>
            <p class="stat-subtext">Temporarily not staying</p>
        </article>

        <article class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Admins</span>
                <span class="stat-pill stat-pill--red">Admin</span>
            </div>
            <div class="stat-value" id="stat_admins">0</div>
            <p class="stat-subtext">Total admins of this mess</p>
        </article>
    </section>

    <!-- Main layout -->
    <section class="members-layout">
        <!-- Left column: members table -->
        <div class="members-column">
            <article class="card-block" data-section="membersTable">
                <div class="card-block-header">
                    <h2 class="card-title">All Members</h2>
                    <div class="card-header-right">
                        <span class="card-meta">Select a member to manage</span>
                        <div class="members-search">
                            <input type="text" id="memberSearchInput" class="members-search-input" placeholder="Search member...">
                        </div>
                    </div>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Room</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody id="membersTableBody">
                        <!-- JS will insert rows -->
                    </tbody>
                </table>
            </article>
        </div>

        <!-- Right column: selected member details -->
        <div class="members-column">
            <article class="card-block" data-section="memberDetails">
                <div class="card-block-header">
                    <h2 class="card-title">Selected Member</h2>
                    <div class="member-actions">
                        <span class="card-meta" id="memberDetailsMeta">No member selected</span>
                        <!-- niche 3ta button admin holei JS diye show hobe -->
                        <button type="button" class="btn btn-secondary btn-xs hidden" id="toggleStatusBtn">
                            Toggle Status
                        </button>
                        <button type="button" class="btn btn-secondary btn-xs hidden" id="toggleRoleBtn">
                            Toggle Role
                        </button>
                        <button type="button" class="btn btn-secondary btn-xs hidden" id="removeMemberBtn">
                            Remove
                        </button>
                    </div>
                </div>
                <div class="member-details">
                    <div class="member-details-row">
                        <span class="member-details-label">Name</span>
                        <span class="member-details-value" id="detail_name">-</span>
                    </div>
                    <div class="member-details-row">
                        <span class="member-details-label">User ID</span>
                        <span class="member-details-value" id="detail_user_id">-</span>
                    </div>
                    <div class="member-details-row">
                        <span class="member-details-label">Role</span>
                        <span class="member-details-value" id="detail_role">-</span>
                    </div>
                    <div class="member-details-row">
                        <span class="member-details-label">Status</span>
                        <span class="member-details-value" id="detail_status">-</span>
                    </div>
                    <div class="member-details-row">
                        <span class="member-details-label">Room</span>
                        <span class="member-details-value" id="detail_room">-</span>
                    </div>

                    <!-- Change room control (only admin via JS) -->
                    <div class="member-details-row" id="changeRoomRow">
                        <span class="member-details-label">Change Room</span>
                        <span class="member-details-value">
                            <select id="change_room_select" class="members-form-control members-form-control--inline"></select>
                            <button type="button" class="btn btn-secondary btn-xs" id="changeRoomBtn">
                                Change
                            </button>
                        </span>
                    </div>

                    <div class="member-details-row">
                        <span class="member-details-label">Contact</span>
                        <span class="member-details-value" id="detail_contact">-</span>
                    </div>
                    <div class="member-details-row">
                        <span class="member-details-label">Email</span>
                        <span class="member-details-value" id="detail_email">-</span>
                    </div>
                    <div class="member-details-row">
                        <span class="member-details-label">Blood Group</span>
                        <span class="member-details-value" id="detail_blood">-</span>
                    </div>
                    <div class="member-details-row">
                        <span class="member-details-label">Profession</span>
                        <span class="member-details-value" id="detail_profession">-</span>
                    </div>
                    <div class="member-details-row">
                        <span class="member-details-label">Joined</span>
                        <span class="member-details-value" id="detail_joined">-</span>
                    </div>
                    <div class="member-details-row">
                        <span class="member-details-label">Address</span>
                        <span class="member-details-value" id="detail_address">-</span>
                    </div>
                </div>
            </article>

            <article class="card-block" data-section="memberNotes">
                <div class="card-block-header">
                    <h2 class="card-title">Member Notes</h2>
                    <span class="card-meta">Quick info</span>
                </div>
                <ul class="member-notes-list" id="memberNotesList">
                    <!-- JS will insert notes -->
                </ul>
            </article>
        </div>
    </section>

    <!-- Add Member Modal (Admin only) -->
    <div class="members-modal" id="addMemberModal">
        <div class="members-modal-content">
            <div class="members-modal-header">
                <h3>Add New Member</h3>
                <button type="button" class="members-modal-close" id="addMemberClose">&times;</button>
            </div>
            <form id="addMemberForm">
                <div class="members-modal-body">
                    <div class="members-form-grid">
                        <div class="members-form-group">
                            <label for="new_full_name">Full Name</label>
                            <input type="text" id="new_full_name" class="members-form-control" required>
                        </div>
                        <div class="members-form-group">
                            <label for="new_contact">Contact</label>
                            <input type="text" id="new_contact" class="members-form-control" required>
                        </div>
                        <div class="members-form-group">
                            <label for="new_email">Email</label>
                            <input type="email" id="new_email" class="members-form-control" required>
                        </div>
                        <div class="members-form-group">
                            <label for="new_password">Initial Password</label>
                            <input type="text" id="new_password" class="members-form-control" placeholder="e.g. Abcd@1234" required>
                        </div>
                        <div class="members-form-group">
                            <label for="new_blood">Blood Group</label>
                            <select id="new_blood" class="members-form-control">
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
                        <div class="members-form-group">
                            <label for="new_role">Role</label>
                            <select id="new_role" class="members-form-control">
                                <option value="member" selected>Member</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="members-form-group">
                            <label for="new_room_id">Room</label>
                            <select id="new_room_id" class="members-form-control" required>
                                <!-- JS will fill with rooms that have vacancy -->
                            </select>
                        </div>
                        <div class="members-form-group members-form-group--full">
                            <label for="new_profession">Profession</label>
                            <input type="text" id="new_profession" class="members-form-control" placeholder="Student / Job / Business">
                        </div>
                        <div class="members-form-group members-form-group--full">
                            <label for="new_address">Address</label>
                            <textarea id="new_address" class="members-form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <p class="members-modal-note" id="addMemberRoomNote">
                        Member must be assigned to a room that has free seats. Otherwise member cannot be created.
                    </p>
                </div>
                <div class="members-modal-footer">
                    <button type="button" class="btn btn-secondary" id="addMemberCancel">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addMemberSave">Save Member</button>
                </div>
            </form>
        </div>
    </div>

    <!-- New Member Info Modal (show ID/email/password/room and download) -->
    <div class="members-modal" id="memberInfoModal">
        <div class="members-modal-content members-modal-content--small">
            <div class="members-modal-header">
                <h3>New Member Information</h3>
                <button type="button" class="members-modal-close" id="memberInfoClose">&times;</button>
            </div>
            <div class="members-modal-body">
                <p class="members-modal-note">
                    Share this information securely with the member. In a real system, the member should change this password after first login.
                </p>
                <div class="member-info-grid">
                    <div class="member-info-row">
                        <span class="member-info-label">Name</span>
                        <span class="member-info-value" id="info_name">-</span>
                    </div>
                    <div class="member-info-row">
                        <span class="member-info-label">User ID</span>
                        <span class="member-info-value" id="info_user_id">-</span>
                    </div>
                    <div class="member-info-row">
                        <span class="member-info-label">Email</span>
                        <span class="member-info-value" id="info_email">-</span>
                    </div>
                    <div class="member-info-row">
                        <span class="member-info-label">Initial Password</span>
                        <span class="member-info-value" id="info_password">-</span>
                    </div>
                    <div class="member-info-row">
                        <span class="member-info-label">Room</span>
                        <span class="member-info-value" id="info_room">-</span>
                    </div>
                    <div class="member-info-row">
                        <span class="member-info-label">Mess</span>
                        <span class="member-info-value" id="info_mess">-</span>
                    </div>
                </div>
            </div>
            <div class="members-modal-footer">
                <button type="button" class="btn btn-secondary" id="memberInfoDownload">Download</button>
                <button type="button" class="btn btn-primary" id="memberInfoPrint">Print</button>
            </div>
        </div>
    </div>
</div>