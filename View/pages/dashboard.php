<div class="dashboard">
    <!-- Header -->
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="dashboard-subtitle" id="dashboardSubtitle">
                Overview
            </p>
        </div>

        <div class="dashboard-header-right">
            <div class="dashboard-period">
                <span id="dashPeriodLabel">This month</span>
            </div>
        </div>
    </div>

    <!-- Top stats -->
    <section class="dashboard-stat-row">
        <article class="stat-card">
            <div class="stat-card-label" id="dash_stat1_label">Stat 1</div>
            <div class="stat-card-value" id="dash_stat1_value">0</div>
            <p class="stat-card-sub" id="dash_stat1_sub"></p>
        </article>

        <article class="stat-card">
            <div class="stat-card-label" id="dash_stat2_label">Stat 2</div>
            <div class="stat-card-value" id="dash_stat2_value">0</div>
            <p class="stat-card-sub" id="dash_stat2_sub"></p>
        </article>

        <article class="stat-card">
            <div class="stat-card-label" id="dash_stat3_label">Stat 3</div>
            <div class="stat-card-value" id="dash_stat3_value">0</div>
            <p class="stat-card-sub" id="dash_stat3_sub"></p>
        </article>

        <article class="stat-card">
            <div class="stat-card-label" id="dash_stat4_label">Stat 4</div>
            <div class="stat-card-value" id="dash_stat4_value">0</div>
            <p class="stat-card-sub" id="dash_stat4_sub"></p>
        </article>
    </section>

    <!-- Main layout: 2 columns on desktop, stack on mobile -->
    <section class="dashboard-layout">
        <!-- Left column -->
        <div class="dashboard-column">
            <!-- My / Admin Overview -->
            <article class="card-block" data-section="overview">
                <div class="card-block-header">
                    <div>
                        <h2 class="card-title" id="overviewTitle">Overview</h2>
                        <span class="card-meta" id="overviewMeta">Today &amp; this month</span>
                    </div>
                </div>
                <div class="overview-grid" id="overviewGrid">
                    <!-- JS will render small info chips -->
                </div>
            </article>

            <!-- Today's Meals -->
            <article class="card-block" data-section="todayMeals">
                <div class="card-block-header">
                    <div>
                        <h2 class="card-title" id="todayMealsTitle">Today’s Meals</h2>
                        <span class="card-meta" id="todayMealsMeta">Meals &amp; your status</span>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Type</th>
                            <th>Menu</th>
                            <th id="todayMealsColStatus">Status</th>
                        </tr>
                        </thead>
                        <tbody id="todayMealsTableBody">
                        <!-- JS will insert rows -->
                        </tbody>
                    </table>
                </div>
            </article>
        </div>

        <!-- Right column -->
        <div class="dashboard-column">
            <!-- Admin-only financial overview -->
            <article class="card-block admin-only" data-section="adminFinances">
                <div class="card-block-header">
                    <div>
                        <h2 class="card-title" id="finTitle">Mess Financial Overview</h2>
                        <span class="card-meta" id="finMeta">This month</span>
                    </div>
                </div>
                <div class="overview-grid" id="finGrid">
                    <!-- JS will render short financial cards -->
                </div>
            </article>

            <!-- Recent Activity -->
            <article class="card-block" data-section="recentActivity">
                <div class="card-block-header">
                    <div>
                        <h2 class="card-title">Recent Activity</h2>
                        <span class="card-meta" id="recentMeta">Announcements • Payments • Bazar</span>
                    </div>
                </div>
                <div class="activity-grid">
                    <div class="activity-column">
                        <h4 class="activity-title">Announcements</h4>
                        <ul class="activity-list" id="recentAnnouncements">
                            <!-- JS -->
                        </ul>
                    </div>
                    <div class="activity-column">
                        <h4 class="activity-title">Payments</h4>
                        <ul class="activity-list" id="recentPayments">
                            <!-- JS -->
                        </ul>
                    </div>
                    <div class="activity-column">
                        <h4 class="activity-title">Bazar</h4>
                        <ul class="activity-list" id="recentBazar">
                            <!-- JS -->
                        </ul>
                    </div>
                </div>
            </article>
        </div>
    </section>

    
</div>