
document.addEventListener("DOMContentLoaded", async function () {

    const hostelData = await fetchHostelDataFromDB();

    document.getElementById("hostel_id").textContent = hostelData.hostel_id;
    document.getElementById("hostel_name").textContent = hostelData.hostel_name;
    document.getElementById("address").textContent = hostelData.address;
    document.getElementById("total_seats").textContent = hostelData.total_seats;
    document.getElementById("admin_id").textContent = hostelData.admin_id;
});

// Edit বাটন ও মডাল কন্ট্রোল
const editBtn = document.getElementById("editBtn");
const modal = document.getElementById("editModal");
const cancelBtn = document.getElementById("cancelBtn");
const editForm = document.getElementById("editForm");

editBtn.addEventListener("click", () => {
    // মডালে বর্তমান ভ্যালু লোড করা
    // document.getElementById("edit_hostel_id").value = demoHostelData.hostel_id;
    // document.getElementById("edit_hostel_name").value = demoHostelData.hostel_name;
    // document.getElementById("edit_address").value = demoHostelData.address;
    // document.getElementById("edit_total_seats").value = demoHostelData.total_seats;
    // document.getElementById("edit_admin_id").value = demoHostelData.admin_id;

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

async function fetchHostelDataFromDB() {
    try {

        const response = await fetch('/Hostel_Mess_Management_System/api/get_hostel_info.php');


        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const hostel_info = await response.json();

        console.log(hostel_info);


        return hostel_info;

    } catch (error) {
        console.error(error);
    }
}

fetchHostelDataFromDB();




// ভবিষ্যতে সেভ করার ফাঙ্কশন
async function saveHostelDataToDB(data) {
    // TODO: POST রিকোয়েস্ট দিয়ে ডাটা সেভ করা
    // await fetch('api/update_hostel.php', { method: 'POST', body: JSON.stringify(data) });
}

