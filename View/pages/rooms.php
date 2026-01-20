<div class="rooms">
    <!-- Header -->
    <div class="rooms-header">
        <div>
            <h1 class="page-title">Rooms &amp; Beds</h1>
            <p class="rooms-subtitle" id="roomsSubtitle">
                Rooms overview
            </p>
        </div>
        <div class="rooms-header-right">
            <button type="button" class="btn btn-primary" id="addRoomBtn">
                Add Room
            </button>
        </div>
    </div>

    <!-- Top stats -->
    <section class="rooms-stat-grid">
        <article class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Total Rooms</span>
                <span class="stat-pill stat-pill--blue">Rooms</span>
            </div>
            <div class="stat-value" id="stat_total_rooms">0</div>
            <p class="stat-subtext">All active rooms in this mess</p>
        </article>

        <article class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Total Beds</span>
                <span class="stat-pill stat-pill--green">Seats</span>
            </div>
            <div class="stat-value" id="stat_total_beds">0</div>
            <p class="stat-subtext">Sum of all room capacities</p>
        </article>

        <article class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Occupied</span>
                <span class="stat-pill stat-pill--amber">Used</span>
            </div>
            <div class="stat-value" id="stat_occupied">0</div>
            <p class="stat-subtext">Current members staying</p>
        </article>

        <article class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Available Seats</span>
                <span class="stat-pill stat-pill--red">Vacant</span>
            </div>
            <div class="stat-value" id="stat_vacant">0</div>
            <p class="stat-subtext">Free seats you can allocate</p>
        </article>
    </section>

    <!-- Main layout -->
    <section class="rooms-layout">
        <!-- Left column: rooms table -->
        <div class="rooms-column">
            <article class="card-block" data-section="roomsTable">
                <div class="card-block-header">
                    <h2 class="card-title">All Rooms</h2>
                    <span class="card-meta">Click a room to see details</span>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Capacity</th>
                            <th>Occupied</th>
                            <th>Vacant</th>
                            <th>Rent/Seat</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="roomsTableBody">
                        <!-- JS will insert rows -->
                    </tbody>
                </table>
            </article>
        </div>

        <!-- Right column: selected room details -->
        <div class="rooms-column">
            <article class="card-block" data-section="roomDetails">
                <div class="card-block-header">
                    <h2 class="card-title">Selected Room</h2>
                    <div class="room-actions">
                        <!-- admin hole JS diye enable hobe; member hole JS e hide -->
                        <button type="button" class="btn btn-secondary btn-xs" id="editRoomBtn">
                            Update
                        </button>
                        <button type="button" class="btn btn-secondary btn-xs" id="removeRoomBtn">
                            Remove
                        </button>
                    </div>
                </div>
                <span class="card-meta" id="roomDetailsMeta">No room selected</span>
                <div class="room-details">
                    <div class="room-details-row">
                        <span class="room-details-label">Room Number</span>
                        <span class="room-details-value" id="detail_room_number">-</span>
                    </div>
                    <div class="room-details-row">
                        <span class="room-details-label">Capacity</span>
                        <span class="room-details-value" id="detail_capacity">-</span>
                    </div>
                    <div class="room-details-row">
                        <span class="room-details-label">Current Occupancy</span>
                        <span class="room-details-value" id="detail_occupancy">-</span>
                    </div>
                    <div class="room-details-row">
                        <span class="room-details-label">Vacant Seats</span>
                        <span class="room-details-value" id="detail_vacant">-</span>
                    </div>
                    <div class="room-details-row">
                        <span class="room-details-label">Rent per Seat</span>
                        <span class="room-details-value" id="detail_rent">-</span>
                    </div>
                    <div class="room-details-row">
                        <span class="room-details-label">Status</span>
                        <span class="room-details-value" id="detail_status">-</span>
                    </div>
                    <div class="room-details-row">
                        <span class="room-details-label">Members</span>
                        <span class="room-details-value" id="detail_members">-</span>
                    </div>
                    <div class="room-details-row">
                        <span class="room-details-label">Facilities</span>
                        <span class="room-details-value" id="detail_facilities">-</span>
                    </div>
                </div>
            </article>

            <article class="card-block" data-section="roomNotes">
                <div class="card-block-header">
                    <h2 class="card-title">Room Notes</h2>
                    <span class="card-meta">Quick info</span>
                </div>
                <ul class="room-notes-list" id="roomNotesList">
                    <!-- JS will insert hints -->
                </ul>
            </article>
        </div>
    </section>

    <!-- Add / Edit Room Modal -->
    <div class="rooms-modal" id="addRoomModal">
        <div class="rooms-modal-content">
            <div class="rooms-modal-header">
                <!-- editMode true hole JS change kore "Update Room" korbe -->
                <h3 id="roomModalTitle">Add New Room</h3>
                <button type="button" class="rooms-modal-close" id="addRoomClose">&times;</button>
            </div>
            <form id="addRoomForm">
                <div class="rooms-modal-body">
                    <div class="rooms-form-grid">
                        <div class="rooms-form-group">
                            <label for="new_room_number">Room Number</label>
                            <input type="text" id="new_room_number" class="rooms-form-control" placeholder="e.g. 101, A-1" required>
                        </div>
                        <div class="rooms-form-group">
                            <label for="new_capacity">Capacity</label>
                            <input type="number" id="new_capacity" class="rooms-form-control" min="1" value="4" required>
                        </div>
                        <div class="rooms-form-group">
                            <label for="new_rent">Rent per Seat (৳)</label>
                            <input type="number" id="new_rent" class="rooms-form-control" min="0" step="100" value="3500" required>
                        </div>
                        <div class="rooms-form-group">
                            <label for="new_is_active">Status</label>
                            <select id="new_is_active" class="rooms-form-control">
                                <option value="1" selected>Active</option>
                                <option value="0">Closed</option>
                            </select>
                        </div>
                        <div class="rooms-form-group rooms-form-group--full">
                            <label for="new_facilities">Facilities</label>
                            <textarea id="new_facilities" class="rooms-form-control" rows="3" placeholder="e.g. AC, Balcony, WiFi"></textarea>
                        </div>
                    </div>
                </div>
                <div class="rooms-modal-footer">
                    <button type="button" class="btn btn-secondary" id="addRoomCancel">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="roomModalSaveBtn">Save Room</button>
                </div>
            </form>
        </div>
    </div>
</div>