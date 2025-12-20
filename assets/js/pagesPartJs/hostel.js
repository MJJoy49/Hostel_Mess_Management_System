// ডেমো ডাটা (ভবিষ্যতে ডাটাবেস থেকে আসবে)
const demoHostelData = {
    hostel_id: "HST-001",
    hostel_name: "Sunrise Boys Hostel",
    address: "123 Mirpur Road, Dhaka-1216",
    total_seats: 120,
    admin_id: "ADM-045"
};

// পেজ লোড হলে ডাটা দেখানো
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("hostel_id").textContent = demoHostelData.hostel_id;
    document.getElementById("hostel_name").textContent = demoHostelData.hostel_name;
    document.getElementById("address").textContent = demoHostelData.address;
    document.getElementById("total_seats").textContent = demoHostelData.total_seats;
    document.getElementById("admin_id").textContent = demoHostelData.admin_id;
});

// Edit বাটন ও মডাল কন্ট্রোল
const editBtn = document.getElementById("editBtn");
const modal = document.getElementById("editModal");
const cancelBtn = document.getElementById("cancelBtn");
const editForm = document.getElementById("editForm");

editBtn.addEventListener("click", () => {
    // মডালে বর্তমান ভ্যালু লোড করা
    document.getElementById("edit_hostel_id").value = demoHostelData.hostel_id;
    document.getElementById("edit_hostel_name").value = demoHostelData.hostel_name;
    document.getElementById("edit_address").value = demoHostelData.address;
    document.getElementById("edit_total_seats").value = demoHostelData.total_seats;
    document.getElementById("edit_admin_id").value = demoHostelData.admin_id;

    modal.classList.add("active");
});

cancelBtn.addEventListener("click", () => {
    modal.classList.remove("active");
});

// Save করলে (ফ্রন্টএন্ডে আপডেট, ডাটাবেসে না)
editForm.addEventListener("submit", (e) => {
    e.preventDefault();

    // নতুন ভ্যালু নেওয়া
    demoHostelData.hostel_name = document.getElementById("edit_hostel_name").value;
    demoHostelData.address = document.getElementById("edit_address").value;
    demoHostelData.total_seats = document.getElementById("edit_total_seats").value;

    // UI আপডেট করা
    document.getElementById("hostel_name").textContent = demoHostelData.hostel_name;
    document.getElementById("address").textContent = demoHostelData.address;
    document.getElementById("total_seats").textContent = demoHostelData.total_seats;

    modal.classList.remove("active");
    alert("Changes saved successfully! (Frontend only)");
});

// ভবিষ্যতে ডাটাবেস থেকে ডাটা লোড করার ফাঙ্কশন (খালি রাখা)
async function fetchHostelDataFromDB() {
    // TODO: AJAX/Fetch দিয়ে PHP থেকে ডাটা নেওয়া হবে
    // উদাহরণ: const response = await fetch('api/get_hostel.php');
    // return await response.json();
}

// ভবিষ্যতে সেভ করার ফাঙ্কশন
async function saveHostelDataToDB(data) {
    // TODO: POST রিকোয়েস্ট দিয়ে ডাটা সেভ করা
    // await fetch('api/update_hostel.php', { method: 'POST', body: JSON.stringify(data) });
}

