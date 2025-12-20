// meals.js

// ডেমো ডাটা (ভবিষ্যতে ডাটাবেস থেকে আসবে)
const demoMembers = [
    { id: "MEM-001", name: "John Doe" },
    { id: "MEM-002", name: "Jane Smith" },
    { id: "MEM-003", name: "Alice Johnson" },
    { id: "MEM-001", name: "John Doe" },
    { id: "MEM-002", name: "Jane Smith" },
    { id: "MEM-003", name: "Alice Johnson" },
    { id: "MEM-001", name: "John Doe" },
    { id: "MEM-002", name: "Jane Smith" },
    { id: "MEM-003", name: "Alice Johnson" },
    { id: "MEM-001", name: "John Doe" },
    { id: "MEM-002", name: "Jane Smith" },
    { id: "MEM-003", name: "Alice Johnson" },
    { id: "MEM-001", name: "John Doe" },
    { id: "MEM-002", name: "Jane Smith" },
    { id: "MEM-003", name: "Alice Johnson" },
    { id: "MEM-001", name: "John Doe" },
    { id: "MEM-002", name: "Jane Smith" },
    { id: "MEM-003", name: "Alice Johnson" },
    { id: "MEM-001", name: "John Doe" },
    { id: "MEM-002", name: "Jane Smith" },
    { id: "MEM-003", name: "Alice Johnson" },
    { id: "MEM-001", name: "John Doe" },
    { id: "MEM-002", name: "Jane Smith" },
    { id: "MEM-003", name: "Alice Johnson" },
    { id: "MEM-001", name: "John Doe" },
    { id: "MEM-002", name: "Jane Smith" },
    { id: "MEM-003", name: "Alice Johnson" },
    { id: "MEM-001", name: "John Doe" },
    { id: "MEM-002", name: "Jane Smith" },
    { id: "MEM-003", name: "Alice Johnson" },
    { id: "MEM-001", name: "John Doe" },
    { id: "MEM-002", name: "Jane Smith" },
    { id: "MEM-003", name: "Alice Johnson" },
];

let mealTypes = ["Breakfast", "Lunch", "Dinner"]; // ডিফল্ট 3টা, আরও অ্যাড করা যাবে

let mealSelections = {}; // মেম্বারদের সিলেকশন স্টোর করা {memberId: {mealType: true/false}}

// পেজ লোড হলে টেবিল রেন্ডার করা
document.addEventListener("DOMContentLoaded", function () {
    renderTable();
});

// টেবিল রেন্ডার ফাঙ্কশন
function renderTable() {
    const tableHeader = document.getElementById("tableHeader");
    const tableBody = document.getElementById("tableBody");
    const allSelectRow = document.getElementById("allSelectRow");

    // হেডার ক্লিয়ার এবং রেন্ডার
    tableHeader.innerHTML = '';
    const headerCells = ['Member ID', 'Member Name', ...mealTypes];
    headerCells.forEach(cell => {
        const div = document.createElement('div');
        div.classList.add('cell');
        div.textContent = cell;
        tableHeader.appendChild(div);
    });

    // বডি ক্লিয়ার এবং রেন্ডার
    tableBody.innerHTML = '';
    demoMembers.forEach(member => {
        if (!mealSelections[member.id]) {
            mealSelections[member.id] = {};
            mealTypes.forEach(type => {
                mealSelections[member.id][type] = false;
            });
        }

        const row = document.createElement('div');
        row.classList.add('table-row');

        // Member ID
        const idCell = document.createElement('div');
        idCell.classList.add('cell');
        idCell.textContent = member.id;
        row.appendChild(idCell);

        // Member Name
        const nameCell = document.createElement('div');
        nameCell.classList.add('cell');
        nameCell.textContent = member.name;
        row.appendChild(nameCell);

        // Meal Checkboxes
        mealTypes.forEach(type => {
            const cell = document.createElement('div');
            cell.classList.add('cell');
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = mealSelections[member.id][type];
            checkbox.addEventListener('change', (e) => {
                mealSelections[member.id][type] = e.target.checked;
            });
            cell.appendChild(checkbox);
            row.appendChild(cell);
        });

        tableBody.appendChild(row);
    });

    // All Select Row
    allSelectRow.innerHTML = '';
    const allSelectLabel = document.createElement('div');
    allSelectLabel.classList.add('cell');
    allSelectLabel.textContent = 'All Select';
    allSelectLabel.style.flex = '2'; // ID + Name এর জন্য মার্জ
    allSelectRow.appendChild(allSelectLabel);

    mealTypes.forEach(type => {
        const cell = document.createElement('div');
        cell.classList.add('cell');
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            demoMembers.forEach(member => {
                mealSelections[member.id][type] = isChecked;
            });
            renderTable(); // রি-রেন্ডার টেবিল চেকবক্স আপডেট করতে
        });
        cell.appendChild(checkbox);
        allSelectRow.appendChild(cell);
    });
}

// Add Meal Button
const addMealBtn = document.getElementById("addMealBtn");
const newMealType = document.getElementById("newMealType");

addMealBtn.addEventListener("click", () => {
    const type = newMealType.value.trim();
    if (type && !mealTypes.includes(type)) {
        mealTypes.push(type);
        // নতুন মিল টাইপ সব মেম্বারের জন্য অ্যাড
        demoMembers.forEach(member => {
            if (!mealSelections[member.id]) mealSelections[member.id] = {};
            mealSelections[member.id][type] = false;
        });
        renderTable();
        newMealType.value = '';
    } else {
        alert("Enter a valid and unique meal type!");
    }
});

// ভবিষ্যতে ডাটাবেস থেকে ডাটা লোড করার ফাঙ্কশন (খালি রাখা)
async function fetchMealsDataFromDB() {
    // TODO: AJAX/Fetch দিয়ে PHP থেকে মেম্বারস, মিল টাইপস, সিলেকশনস নেওয়া
    // উদাহরণ: const response = await fetch('api/get_meals.php');
    // const data = await response.json();
    // demoMembers = data.members;
    // mealTypes = data.mealTypes;
    // mealSelections = data.selections;
}

// ভবিষ্যতে সেভ করার ফাঙ্কশন
async function saveMealsDataToDB(data) {
    // TODO: POST রিকোয়েস্ট দিয়ে ডাটা সেভ করা
    // await fetch('api/update_meals.php', { method: 'POST', body: JSON.stringify(data) });
}
