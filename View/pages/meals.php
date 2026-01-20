<div class="meals-management">
  <!-- Header -->
  <div class="meals-header">
    <div>
      <h1 class="page-title">Meals &amp; Attendance</h1>
      <p class="meals-subtitle" id="mealsSubtitle">
        Meals overview
      </p>
    </div>

    <div class="meals-header-right">
      <div class="meals-rate">
        Meal rate: <span id="mealsRateLabel">৳0</span>
      </div>
      <div class="meals-filters">
        <!-- Add Meal Button: Visibility controlled by JS based on Role -->
        <button type="button" class="btn btn-primary" id="addMealBtn" style="display: none;">
          Add Meal
        </button>
      </div>
    </div>
  </div>

  <!-- Top short stats -->
  <section class="meals-stat-row">
    <div class="stat-pill-block">
      <div class="stat-pill-label" id="label_stat_meals_today">Meals today</div>
      <div class="stat-pill-value" id="meals_stat_today_count">0</div>
    </div>
    <div class="stat-pill-block">
      <div class="stat-pill-label" id="label_stat_my_meals_month">My meals this month</div>
      <div class="stat-pill-value" id="meals_stat_my_month">0</div>
    </div>
    <div class="stat-pill-block">
      <div class="stat-pill-label" id="label_stat_my_due">My meal due</div>
      <div class="stat-pill-value" id="meals_stat_my_due">৳0</div>
    </div>
  </section>

  <!-- Main sections: 1 column, full width, ordered boxes -->
  <section class="meals-sections">

    <!-- 1) Meal Summary (This Month) -->
    <article class="card-block" data-section="summaryMonth">
      <div class="card-block-header">
        <div>
          <h2 class="card-title" id="summaryMonthTitle">Meal Summary (This Month)</h2>
          <span class="card-meta" id="summaryMonthMeta">Overview</span>
        </div>
      </div>
      <div class="meals-summary-grid">
        <div class="summary-item">
          <div class="summary-label">Total meals (this month)</div>
          <div class="summary-value" id="summary_total_meals">0</div>
        </div>
        <div class="summary-item">
          <div class="summary-label">Meal rate</div>
          <div class="summary-value" id="summary_meal_rate">৳0</div>
        </div>
        <div class="summary-item">
          <div class="summary-label">Total Bazar Cost (Est.)</div>
          <div class="summary-value" id="summary_estimated_cost">৳0</div>
        </div>
      </div>
    </article>

    <!-- 2) Member Attendance (date filter) -->
    <article class="card-block" data-section="todayAttendance">
      <div class="card-block-header">
        <div>
          <h2 class="card-title">Member Attendance</h2>
          <span class="card-meta" id="attendanceDateLabel">Today</span>
        </div>
        <div class="attendance-filter">
          <label for="attendanceDateFilter" class="attendance-filter-label">
            Date
          </label>
          <input type="date" id="attendanceDateFilter" class="attendance-date-input">
        </div>
      </div>
      
      <div class="table-wrapper">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Member</th>
              <th>Breakfast</th>
              <th>Lunch</th>
              <th>Dinner</th>
            </tr>
          </thead>
          <tbody id="attendanceTableBody">
            <!-- JS will insert rows -->
          </tbody>
        </table>
      </div>
      <p class="table-footer-hint">
        * Admin can click on Ate/Skipped to toggle attendance.
      </p>
    </article>

    <!-- 3) Today Meals (type + available toggle) -->
    <article class="card-block" data-section="todayMeals">
      <div class="card-block-header">
        <div>
          <h2 class="card-title" id="todayMealsTitle">Today Meals</h2>
          <span class="card-meta" id="todayMealsDate">0000-00-00</span>
        </div>
      </div>
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Type</th>
              <th>Menu</th>
              <th>Available</th>
            </tr>
          </thead>
          <tbody id="todayMealsTableBody">
            <!-- JS will insert rows -->
          </tbody>
        </table>
      </div>
    </article>

    <!-- 4) Recent Bazar History -->
    <article class="card-block" data-section="bazarHistory">
      <div class="card-block-header">
        <div>
          <h2 class="card-title" id="bazarHistoryTitle">Recent Bazar History</h2>
          <span class="card-meta" id="bazarHistoryMeta">Last 7 days</span>
        </div>
        <div class="bazar-filter">
          <label for="bazarDateFilter" class="bazar-filter-label">
            Filter by date
          </label>
          <input type="date" id="bazarDateFilter" class="bazar-date-input">
          <button type="button" class="btn btn-secondary btn-xs" id="bazarClearFilterBtn">
            Last 7 days
          </button>
        </div>
      </div>
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Items</th>
              <th>Total Amount</th>
              <th>Bazaar By</th>
            </tr>
          </thead>
          <tbody id="bazarHistoryTableBody">
            <!-- JS will insert rows -->
          </tbody>
        </table>
      </div>
    </article>

  </section>
</div>