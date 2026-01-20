<div class="bills">
  <!-- Header -->
  <div class="bills-header">
    <div>
      <h1 class="page-title">Bills &amp; Payments</h1>
      <p class="bills-subtitle" id="billsSubtitle">
        Bills overview
      </p>
    </div>

    <div class="bills-header-right">
      <div class="bills-filters">
        <label for="billMonthSelect" class="bills-filter-label">Month</label>
        <select id="billMonthSelect" class="bills-filter-select">
          <option value="1">Jan</option>
          <option value="2">Feb</option>
          <option value="3">Mar</option>
          <option value="4">Apr</option>
          <option value="5">May</option>
          <option value="6">Jun</option>
          <option value="7">Jul</option>
          <option value="8">Aug</option>
          <option value="9">Sep</option>
          <option value="10">Oct</option>
          <option value="11">Nov</option>
          <option value="12">Dec</option>
        </select>

        <label for="billYearSelect" class="bills-filter-label">Year</label>
        <select id="billYearSelect" class="bills-filter-select">
          <!-- JS will populate years -->
        </select>

        <button type="button" class="btn btn-secondary btn-xs" id="billReloadBtn">
          Load
        </button>
      </div>
    </div>
  </div>

  <!-- Top Stats -->
  <section class="bills-stat-row">
    <div class="stat-pill-block">
      <div class="stat-pill-label" id="label_stat_bill_1">Total Bill</div>
      <div class="stat-pill-value" id="stat_bill_1">0</div>
    </div>
    <div class="stat-pill-block">
      <div class="stat-pill-label" id="label_stat_bill_2">Paid</div>
      <div class="stat-pill-value" id="stat_bill_2">0</div>
    </div>
    <div class="stat-pill-block">
      <div class="stat-pill-label" id="label_stat_bill_3">Due</div>
      <div class="stat-pill-value" id="stat_bill_3">0</div>
    </div>
  </section>

  <!-- Main Sections: 1 column -->
  <section class="bills-sections">

    <!-- 1) Room Rent / Individual (NEW SECTION) -->
    <!-- visible only to admin via JS logic check, but container exists -->
    <article class="card-block admin-only" data-section="roomRentSection">
      <div class="card-block-header">
        <div>
          <h2 class="card-title">Room Rent / Individual Charge</h2>
          <span class="card-meta">Add bill to specific member</span>
        </div>
      </div>

      <!-- Add Rent form -->
      <div class="bills-form-grid">
        <div class="bills-form-group">
          <label for="rentUserSelect">Select Member</label>
          <select id="rentUserSelect" class="bills-form-control">
             <option value="">Loading...</option>
          </select>
        </div>
        
        <div class="bills-form-group">
          <label for="rentAmount">Amount (Tk)</label>
          <input type="number" id="rentAmount" class="bills-form-control" min="0" step="10" placeholder="e.g. 3000">
        </div>

        <div class="bills-form-group bills-form-group--action">
          <label>&nbsp;</label>
          <button type="button" class="btn btn-primary" id="rentAddBtn">
            Add to Bill
          </button>
        </div>
      </div>
    </article>


    <!-- 2) Bazar (daily_bazar) -->
    <article class="card-block" data-section="bazarSection">
      <div class="card-block-header">
        <div>
          <h2 class="card-title">Bazar (Food)</h2>
          <span class="card-meta" id="bazarMeta">Selected month bazar</span>
          <br>
          <span class="card-meta" style="font-size: 10px; color: var(--text-secondary);">(Divided equally among all)</span>
        </div>
      </div>

      <!-- Add bazar form (admin only) -->
      <div class="bills-form-grid admin-only">
        <div class="bills-form-group">
          <label for="bazarAddDate">Date</label>
          <input type="date" id="bazarAddDate" class="bills-form-control">
        </div>
        <div class="bills-form-group">
          <label for="bazarAddItems">Items</label>
          <input type="text" id="bazarAddItems" class="bills-form-control"
                 placeholder="e.g. Rice 20kg, Oil 5L">
        </div>
        <div class="bills-form-group">
          <label for="bazarAddAmount">Total Amount (Tk)</label>
          <input type="number" id="bazarAddAmount" class="bills-form-control" min="0" step="10" value="0">
        </div>
        <div class="bills-form-group">
          <label for="bazarAddBy">Bazaar By</label>
          <input type="text" id="bazarAddBy" class="bills-form-control" placeholder="Name">
        </div>
        <div class="bills-form-group bills-form-group--action">
          <label>&nbsp;</label>
          <button type="button" class="btn btn-primary" id="bazarAddBtn">
            Add Bazar
          </button>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Date</th>
              <th>Items</th>
              <th>Total Amount</th>
              <th>Bazaar By</th>
              <th class="admin-only">Action</th>
            </tr>
          </thead>
          <tbody id="bazarTableBody">
            <!-- JS will insert rows -->
          </tbody>
        </table>
      </div>
    </article>

    <!-- 3) Other Expenses (expenses) -->
    <article class="card-block" data-section="expenseSection">
      <div class="card-block-header">
        <div>
          <h2 class="card-title">Other Expenses</h2>
          <span class="card-meta" id="expenseMeta">Light, gas, wifi, house rent etc.</span>
          <br>
          <span class="card-meta" style="font-size: 10px; color: var(--text-secondary);">(Divided equally among all)</span>
        </div>
      </div>

      <!-- Add expense form (admin only) -->
      <div class="bills-form-grid admin-only">
        <div class="bills-form-group">
          <label for="expenseAddDate">Date</label>
          <input type="date" id="expenseAddDate" class="bills-form-control">
        </div>
        <div class="bills-form-group">
          <label for="expenseAddCategory">Category</label>
          <input type="text" id="expenseAddCategory" class="bills-form-control"
                 placeholder="e.g. Electricity, Gas, Wifi">
        </div>
        <div class="bills-form-group">
          <label for="expenseAddAmount">Amount (Tk)</label>
          <input type="number" id="expenseAddAmount" class="bills-form-control" min="0" step="10" value="0">
        </div>
        <div class="bills-form-group">
          <label for="expenseAddDesc">Description</label>
          <input type="text" id="expenseAddDesc" class="bills-form-control"
                 placeholder="Optional short note">
        </div>
        <div class="bills-form-group bills-form-group--action">
          <label>&nbsp;</label>
          <button type="button" class="btn btn-primary" id="expenseAddBtn">
            Add Expense
          </button>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Date</th>
              <th>Category</th>
              <th>Description</th>
              <th>Amount</th>
              <th>Added By</th>
              <th class="admin-only">Action</th>
            </tr>
          </thead>
          <tbody id="expenseTableBody">
            <!-- JS will insert rows -->
          </tbody>
        </table>
      </div>
    </article>

    <!-- 4) My Bills -->
    <article class="card-block" data-section="myBills">
      <div class="card-block-header">
        <div>
          <h2 class="card-title" id="myBillsTitle">My Bills</h2>
          <span class="card-meta" id="myBillsMeta">Current month + history</span>
        </div>
      </div>
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Month</th>
              <th>Total Meals</th>
              <th>Total Bill</th>
              <th>Paid</th>
              <th>Due</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="myBillsTableBody">
            <!-- JS will insert rows -->
          </tbody>
        </table>
      </div>
    </article>

    <!-- 5) All Members Bills -->
    <article class="card-block" data-section="allBills">
      <div class="card-block-header">
        <div>
          <h2 class="card-title">All Members (This Month)</h2>
          <span class="card-meta" id="allBillsMeta">Monthly bills overview</span>
        </div>
      </div>
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Member</th>
              <th>Total Meals</th>
              <th>Total Bill</th>
              <th>Paid</th>
              <th>Due</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="allBillsTableBody">
            <!-- JS will insert rows -->
          </tbody>
        </table>
      </div>
    </article>

    <!-- 6) Payments History -->
    <article class="card-block" data-section="paymentsHistory">
      <div class="card-block-header">
        <div>
          <h2 class="card-title" id="paymentsTitle">Payments History</h2>
          <span class="card-meta" id="paymentsMeta">Last transactions</span>
        </div>
      </div>
      <div class="table-wrapper">
        <table class="table table-sm">
          <thead>
            <tr>
              <th id="paymentsColMember">Member</th>
              <th>Amount</th>
              <th>For</th>
              <th>Month</th>
              <th>Method</th>
              <th>Txn ID</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody id="paymentsTableBody">
            <!-- JS will insert rows -->
          </tbody>
        </table>
      </div>
    </article>

  </section>
</div>