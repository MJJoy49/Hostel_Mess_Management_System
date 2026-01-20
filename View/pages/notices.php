<div class="notices">
    <!-- Header -->
    <div class="notices-header">
        <div>
            <h1 class="page-title">Notices &amp; Seat Ads</h1>
            <p class="notices-subtitle" id="noticesSubtitle">
                Mess announcements and seat ads
            </p>
        </div>
        <div class="notices-header-right">
            <!-- Admin & Member: dujon e announcement dite parbe -->
            <button type="button" class="btn btn-primary" id="addAnnouncementBtn">
                Add Announcement
            </button>
            <!-- sudhu admin seat ad dite parbe (JS diye hide/show) -->
            <button type="button" class="btn btn-secondary admin-only" id="addSeatAdBtn">
                Add Seat Ad
            </button>
        </div>
    </div>

    <!-- Top stats -->
    <section class="notices-stat-grid">
        <article class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Announcements</span>
                <span class="stat-pill stat-pill--blue">Weekly</span>
            </div>
            <div class="stat-value" id="stat_total_announcements">0</div>
            <p class="stat-subtext">Announcements from last 10 days</p>
        </article>

        <article class="stat-card admin-only">
            <div class="stat-card-header">
                <span class="stat-label">Active Seat Ads</span>
                <span class="stat-pill stat-pill--green">Ads</span>
            </div>
            <div class="stat-value" id="stat_active_ads">0</div>
            <p class="stat-subtext">Currently visible seat ads</p>
        </article>

        <article class="stat-card admin-only">
            <div class="stat-card-header">
                <span class="stat-label">Pending Requests</span>
                <span class="stat-pill stat-pill--amber">Requests</span>
            </div>
            <div class="stat-value" id="stat_pending_requests">0</div>
            <p class="stat-subtext">Seat requests waiting for review</p>
        </article>
    </section>

    <!-- 3 full-width boxes: Announcements, Seat Ads, Seat Requests -->
    <section class="notice-sections">
        <!-- Box 1: Announcements (everyone sees) -->
        <article class="card-block" id="announcementsSection">
            <div class="card-block-header">
                <div>
                    <h2 class="card-title">Announcements</h2>
                    <span class="card-meta">Latest messages from mess admin &amp; members</span>
                </div>
                <div class="announcement-filter">
                    <label for="announcementDateFilter" class="announcement-filter-label">
                        Filter by date
                    </label>
                    <input type="date" id="announcementDateFilter" class="announcement-date-input">
                    <button type="button" class="btn btn-secondary btn-xs" id="clearDateFilterBtn">
                        Last 10 days
                    </button>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Type</th>
                        <th>Posted By</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody id="announcementsTableBody">
                    <!-- JS will insert rows -->
                    </tbody>
                </table>
            </div>
        </article>

        <!-- Box 2: Seat Ads (admin-only) -->
        <article class="card-block admin-only" id="seatAdsSection">
            <div class="card-block-header">
                <h2 class="card-title">Seat Ads</h2>
                <span class="card-meta">Current seat availability (Admin)</span>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Room</th>
                        <th>Vacant</th>
                        <th>Rent</th>
                        <th>Contact</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody id="seatAdsTableBody">
                    <!-- JS will insert rows -->
                    </tbody>
                </table>
            </div>
        </article>

        <!-- Box 3: Seat Requests (admin-only) -->
        <article class="card-block admin-only" id="requestSeatsSection">
            <div class="card-block-header">
                <h2 class="card-title">Seat Requests</h2>
                <span class="card-meta">Seat requests from visitors (Admin)</span>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Profession</th>
                        <th>For Ad</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody id="requestSeatsTableBody">
                    <!-- JS will insert rows -->
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <!-- Add Announcement Modal -->
    <div class="notices-modal" id="addAnnouncementModal">
        <div class="notices-modal-content">
            <div class="notices-modal-header">
                <h3>Add Announcement</h3>
                <button type="button" class="notices-modal-close" id="addAnnouncementClose">&times;</button>
            </div>
            <form id="addAnnouncementForm">
                <div class="notices-modal-body">
                    <div class="notices-form-group">
                        <label for="new_announce_title">Title</label>
                        <input type="text" id="new_announce_title" class="notices-form-control" required>
                    </div>
                    <div class="notices-form-group">
                        <label for="new_announce_message">Message</label>
                        <textarea id="new_announce_message" class="notices-form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="notices-modal-footer">
                    <button type="button" class="btn btn-secondary" id="addAnnouncementCancel">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Announcement</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Seat Ad Modal -->
    <div class="notices-modal" id="addSeatAdModal">
        <div class="notices-modal-content">
            <div class="notices-modal-header">
                <h3>Add Seat Ad</h3>
                <button type="button" class="notices-modal-close" id="addSeatAdClose">&times;</button>
            </div>

            <form id="addSeatAdForm">
                <div class="notices-modal-body">
                    <div class="notices-form-grid">

                        <!-- Title -->
                        <div class="notices-form-group">
                            <label for="new_ad_title">Title</label>
                            <input
                                type="text"
                                id="new_ad_title"
                                class="notices-form-control"
                                placeholder="e.g. 1 seat in 101 (AC)"
                                required
                            >
                        </div>

                        <!-- Location = Mess Address (read-only) -->
                        <div class="notices-form-group">
                            <label for="new_mess_address">Location (Mess Address)</label>
                            <input
                                type="text"
                                id="new_mess_address"
                                class="notices-form-control"
                                placeholder="Mess address"
                                readonly
                            >
                        </div>

                        <!-- Room -->
                        <div class="notices-form-group">
                            <label for="new_ad_room">Room</label>
                            <input
                                type="text"
                                id="new_ad_room"
                                class="notices-form-control"
                                placeholder="e.g. Room 101, 2nd floor"
                            >
                        </div>

                        <!-- Vacant seats -->
                        <div class="notices-form-group">
                            <label for="new_vacant_seats">Vacant Seats</label>
                            <input
                                type="number"
                                id="new_vacant_seats"
                                class="notices-form-control"
                                min="1"
                                value="1"
                                required
                            >
                        </div>

                        <!-- Rent per seat -->
                        <div class="notices-form-group">
                            <label for="new_rent">Rent per Seat (৳)</label>
                            <input
                                type="number"
                                id="new_rent"
                                class="notices-form-control"
                                min="0"
                                step="100"
                                value="3500"
                                required
                            >
                        </div>

                        <!-- Contact person -->
                        <div class="notices-form-group">
                            <label for="new_contact_person">Contact Person</label>
                            <input
                                type="text"
                                id="new_contact_person"
                                class="notices-form-control"
                                required
                            >
                        </div>

                        <!-- Contact number -->
                        <div class="notices-form-group">
                            <label for="new_contact_number">Contact Number</label>
                            <input
                                type="text"
                                id="new_contact_number"
                                class="notices-form-control"
                                required
                            >
                        </div>

                        <!-- Description -->
                        <div class="notices-form-group notices-form-group--full">
                            <label for="new_ad_description">Description</label>
                            <textarea
                                id="new_ad_description"
                                class="notices-form-control"
                                rows="3"
                            ></textarea>
                        </div>

                    </div>
                </div>

                <div class="notices-modal-footer">
                    <button type="button" class="btn btn-secondary" id="addSeatAdCancel">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Save Seat Ad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>