<!-- <div class="meals">
    ai file meals a kaj korte hobe------
    asset/css/pagesPartCss/meals.css ar kaj
    asset/css/pagesPartJs/meals.js ar kaj
</div> -->

<div class="meals-management">
    <h2 class="page-title">Meals Management</h2>

    <div class="add-meal-section">
        <input type="text" id="newMealType" placeholder="Enter new meal type (e.g., Evening Snack)">
        <button id="addMealBtn">Add Meal Type</button>
    </div>

    <!-- New wrapper for scroll -->
    <div class="meals-table-container">
        <div class="meals-table-wrapper">
            <div class="meals-table" id="mealsTable">
                <!-- Header will be generated dynamically -->
                <div class="table-header" id="tableHeader"></div>
                <!-- Rows will be generated dynamically -->
                <div class="table-body" id="tableBody"></div>
                <!-- All Select row -->
                <div class="all-select-row" id="allSelectRow"></div>
            </div>
        </div>
    </div>
</div>