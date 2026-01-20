<div class="payments">
    <!-- Header -->
    <div class="payments-header">
        <div>
            <h1 class="page-title">Payments &amp; Balances</h1>
            <p class="payments-subtitle" id="paymentsSubtitle">
                Payments overview
            </p>
        </div>

        <div class="payments-header-right">
            <div class="payments-filters">
                <label for="payMonthSelect" class="payments-filter-label">Month</label>
                <select id="payMonthSelect" class="payments-filter-select">
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

                <label for="payYearSelect" class="payments-filter-label">Year</label>
                <select id="payYearSelect" class="payments-filter-select">
                    <!-- JS will populate -->
                </select>

                <button type="button" class="btn btn-secondary btn-xs" id="payReloadBtn">
                    Load
                </button>
            </div>
        </div>
    </div>

    <!-- Top stats -->
    <section class="payments-stat-row">
        <div class="stat-pill-block">
            <div class="stat-pill-label" id="label_stat_pay_1">Deposit (this month)</div>
            <div class="stat-pill-value" id="stat_pay_1">৳0</div>
        </div>
        <div class="stat-pill-block">
            <div class="stat-pill-label" id="label_stat_pay_2">Bill (this month)</div>
            <div class="stat-pill-value" id="stat_pay_2">৳0</div>
        </div>
        <div class="stat-pill-block">
            <div class="stat-pill-label" id="label_stat_pay_3">Net (+/-)</div>
            <div class="stat-pill-value" id="stat_pay_3">৳0</div>
        </div>
    </section>

    <!-- Main sections: 1 column -->
    <section class="payments-sections">

        <!-- 1) Add Payment (admin & member) -->
        <article class="card-block" data-section="addPayment">
            <div class="card-block-header">
                <div>
                    <h2 class="card-title" id="addPayTitle">Add Payment</h2>
                    <span class="card-meta" id="addPayMeta">Admin: any member • Member: own payment</span>
                </div>
            </div>

            <div class="payments-form-grid">
                <!-- Admin: member select; Member: label "You" -->
                <div class="payments-form-group admin-only" id="payMemberSelectGroup">
                    <label for="payMemberSelect">Member</label>
                    <select id="payMemberSelect" class="payments-form-control">
                        <!-- JS will insert members list -->
                    </select>
                </div>
                <div class="payments-form-group member-only" id="payMemberLabelGroup">
                    <label>Member</label>
                    <div class="payments-form-control payments-form-control--static" id="payMemberLabel">You</div>
                </div>

                <div class="payments-form-group">
                    <label for="payAmount">Amount (৳)</label>
                    <input type="number" id="payAmount" class="payments-form-control" min="1" step="10" value="0">
                </div>

                <div class="payments-form-group">
                    <label for="payFor">Payment For</label>
                    <select id="payFor" class="payments-form-control">
                        <option value="meal_bill">Meal Bill</option>
                        <option value="seat_rent">Seat Rent</option>
                        <option value="both">Both</option>
                    </select>
                </div>

                <div class="payments-form-group">
                    <label for="payMethod">Method</label>
                    <select id="payMethod" class="payments-form-control">
                        <option value="cash">Cash</option>
                        <option value="bkash">bKash</option>
                        <option value="nagad">Nagad</option>
                        <option value="bank">Bank</option>
                    </select>
                </div>

                <div class="payments-form-group">
                    <label for="payTxn">Txn ID / Note</label>
                    <input type="text" id="payTxn" class="payments-form-control" placeholder="Transaction ID or note">
                </div>

                <div class="payments-form-group">
                    <label for="payMonthFor">For Month</label>
                    <select id="payMonthFor" class="payments-form-control">
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
                </div>

                <div class="payments-form-group">
                    <label for="payYearFor">For Year</label>
                    <select id="payYearFor" class="payments-form-control">
                        <!-- JS will fill -->
                    </select>
                </div>

                <div class="payments-form-group payments-form-group--action">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-primary" id="payAddBtn">
                        Save Payment
                    </button>
                </div>
            </div>
        </article>

        <!-- 2) This Month Summary (by member) -->
        <article class="card-block" data-section="summarySection">
            <div class="card-block-header">
                <div>
                    <h2 class="card-title" id="summaryTitle">This Month Summary</h2>
                    <span class="card-meta" id="summaryMeta">Deposit vs Bill (Net)</span>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                    <tr>
                        <th id="summaryColMember">Member</th>
                        <th>Total Bill</th>
                        <th>Total Deposit</th>
                        <th>Net (+/-)</th>
                    </tr>
                    </thead>
                    <tbody id="summaryTableBody">
                    <!-- JS will insert rows -->
                    </tbody>
                </table>
            </div>
        </article>

        <!-- 3) Payments History -->
        <article class="card-block" data-section="historySection">
            <div class="card-block-header">
                <div>
                    <h2 class="card-title" id="historyTitle">Payments History</h2>
                    <span class="card-meta" id="historyMeta">Recent transactions</span>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th id="historyColMember">Member</th>
                        <th>Amount</th>
                        <th>For</th>
                        <th>For Month</th>
                        <th>Method</th>
                        <th>Txn ID</th>
                        <th>Date</th>
                        <th class="admin-only">Action</th>
                    </tr>
                    </thead>
                    <tbody id="historyTableBody">
                    <!-- JS will insert rows -->
                    </tbody>
                </table>
            </div>
        </article>

    </section>
</div>