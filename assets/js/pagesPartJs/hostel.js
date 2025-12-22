// Demo: change to false to see non-admin behaviour for mess fields
const IS_ADMIN = true;

// Demo user data
const userData = {
    user_id: "U-2025-001",
    full_name: "John Doe",
    gender: "Male",
    contact_number: "+8801712345678",
    email_id: "john.doe@example.com",
    blood_group: "O+",
    role: "Student",
    photo: "https://m.media-amazon.com/images/M/MV5BMjAwMjk3NDUzN15BMl5BanBnXkFtZTcwNjI4MTY0NA@@._V1_FMjpg_UX1000_.jpg",
    address: "House 12, Road 5, Dhanmondi, Dhaka",
    religion: "Islam",
    profession: "Student",
    joined_date: "2024-01-15"
};

// Demo mess data
const messData = {
    mess_id: "M-001",
    mess_name: "Sunrise Hostel & Mess",
    address: "Road 10, Mirpur, Dhaka",
    capacity: 50,
    admin_name: "Abdullah Admin",
    admin_email: "admin@sunrise-hostel.com",
    admin_id: "A-1001",
    mess_email_id: "info@sunrise-hostel.com",
    created_at: "2020-06-01",
    mess_description:
        "Comfortable student mess with 24/7 water, high-speed Wi‑Fi, and hygienic meals."
};

// Utility: render profile photo with fallback initials
function renderProfilePhoto() {
    const img = document.getElementById("profile_photo");
    const initialsEl = document.getElementById("profile_initials");

    function showInitials() {
        const name = userData.full_name || "";
        const initials = name
            .split(" ")
            .filter(Boolean)
            .map(part => part[0])
            .join("")
            .slice(0, 2)
            .toUpperCase() || "U";

        initialsEl.textContent = initials;
        initialsEl.style.display = "flex";
        img.style.display = "none";
    }

    if (userData.photo && userData.photo.trim() !== "") {
        img.src = userData.photo;
        img.onload = () => {
            img.style.display = "block";
            initialsEl.style.display = "none";
        };
        img.onerror = showInitials;
    } else {
        showInitials();
    }
}

// Render main text fields
function renderProfile() {
    // Header
    document.getElementById("header_full_name").textContent = userData.full_name;
    document.getElementById("header_role").textContent = userData.role;
    document.getElementById("header_mess_name").textContent = messData.mess_name;
    document.getElementById("header_joined_date").textContent = userData.joined_date;

    // User section
    const userFieldIds = {
        user_id: "user_id",
        full_name: "full_name",
        gender: "gender",
        contact_number: "contact_number",
        email_id: "email_id",
        blood_group: "blood_group",
        role: "role",
        religion: "religion",
        profession: "profession",
        joined_date: "joined_date",
        address: "user_address"
    };

    Object.entries(userFieldIds).forEach(([dataKey, elementId]) => {
        const el = document.getElementById(elementId);
        if (!el) return;
        el.textContent = userData[dataKey] ?? "";
    });

    // Mess section
    const messFieldIds = {
        mess_id: "mess_id",
        mess_name: "mess_name",
        capacity: "capacity",
        mess_email_id: "mess_email_id",
        admin_name: "admin_name",
        admin_email: "admin_email",
        admin_id: "admin_id",
        created_at: "created_at",
        address: "mess_address",
        mess_description: "mess_description"
    };

    Object.entries(messFieldIds).forEach(([dataKey, elementId]) => {
        const el = document.getElementById(elementId);
        if (!el) return;
        el.textContent = messData[dataKey] ?? "";
    });

    // Profile photo
    renderProfilePhoto();
}

// Fill modal form with current data
function fillEditForm() {
    // Read-only user fields
    document.getElementById("edit_user_id").value = userData.user_id;
    document.getElementById("edit_role").value = userData.role;
    document.getElementById("edit_joined_date").value = userData.joined_date;

    // Editable user fields
    document.getElementById("edit_full_name").value = userData.full_name;
    document.getElementById("edit_gender").value = userData.gender || "";
    document.getElementById("edit_contact_number").value = userData.contact_number;
    document.getElementById("edit_email_id").value = userData.email_id;
    document.getElementById("edit_blood_group").value = userData.blood_group || "";
    document.getElementById("edit_religion").value = userData.religion;
    document.getElementById("edit_profession").value = userData.profession;
    document.getElementById("edit_user_address").value = userData.address;
    document.getElementById("edit_photo").value = userData.photo || "";

    // Read-only mess fields
    document.getElementById("edit_mess_id").value = messData.mess_id;
    document.getElementById("edit_admin_name").value = messData.admin_name;
    document.getElementById("edit_admin_email").value = messData.admin_email;
    document.getElementById("edit_admin_id").value = messData.admin_id;
    document.getElementById("edit_created_at").value = messData.created_at;

    // Admin-only editable mess fields
    document.getElementById("edit_mess_name").value = messData.mess_name;
    document.getElementById("edit_capacity").value = messData.capacity;
    document.getElementById("edit_mess_email_id").value = messData.mess_email_id;
    document.getElementById("edit_mess_address").value = messData.address;
    document.getElementById("edit_mess_description").value = messData.mess_description;
}

// Apply admin / non-admin permissions on fields
function applyAdminPermissions() {
    const adminOnlyIds = [
        "edit_mess_name",
        "edit_capacity",
        "edit_mess_email_id",
        "edit_mess_address",
        "edit_mess_description"
    ];

    if (!IS_ADMIN) {
        adminOnlyIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.disabled = true;
        });
    } else {
        adminOnlyIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.disabled = false;
        });
    }
}

// Open / close modal
function openModal() {
    const modal = document.getElementById("editModal");
    fillEditForm();
    applyAdminPermissions();
    modal.classList.add("open");
    document.body.classList.add("modal-open"); // background scroll off
}

function closeModal() {
    const modal = document.getElementById("editModal");
    modal.classList.remove("open");
    document.body.classList.remove("modal-open"); // background scroll on
}

// Initialize events
function initEvents() {
    const editBtn = document.getElementById("editBtn");
    const closeBtn = document.getElementById("closeModal");
    const cancelBtn = document.getElementById("cancelEdit");
    const modal = document.getElementById("editModal");
    const form = document.getElementById("editForm");

    if (editBtn) editBtn.addEventListener("click", openModal);
    if (closeBtn) closeBtn.addEventListener("click", closeModal);
    if (cancelBtn) cancelBtn.addEventListener("click", closeModal);

    // Close when clicking outside content
    if (modal) {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) closeModal();
        });
    }

    // Save form
    if (form) {
        form.addEventListener("submit", (e) => {
            e.preventDefault();

            const getVal = (id) => {
                const el = document.getElementById(id);
                if (!el || el.disabled) return null;
                return el.value;
            };

            // Update user data
            const vFullName = getVal("edit_full_name");
            if (vFullName !== null) userData.full_name = vFullName.trim();

            const vGender = getVal("edit_gender");
            if (vGender !== null) userData.gender = vGender;

            const vContact = getVal("edit_contact_number");
            if (vContact !== null) userData.contact_number = vContact.trim();

            const vEmail = getVal("edit_email_id");
            if (vEmail !== null) userData.email_id = vEmail.trim();

            const vBlood = getVal("edit_blood_group");
            if (vBlood !== null) userData.blood_group = vBlood;

            const vReligion = getVal("edit_religion");
            if (vReligion !== null) userData.religion = vReligion.trim();

            const vProfession = getVal("edit_profession");
            if (vProfession !== null) userData.profession = vProfession.trim();

            const vAddress = getVal("edit_user_address");
            if (vAddress !== null) userData.address = vAddress.trim();

            const vPhoto = getVal("edit_photo");
            if (vPhoto !== null) userData.photo = vPhoto.trim();

            // Update mess data (only where not disabled -> respects IS_ADMIN)
            const vMessName = getVal("edit_mess_name");
            if (vMessName !== null) messData.mess_name = vMessName.trim();

            const vCapacity = getVal("edit_capacity");
            if (vCapacity !== null) {
                const parsed = parseInt(vCapacity, 10);
                messData.capacity = Number.isNaN(parsed) ? 0 : parsed;
            }

            const vMessEmail = getVal("edit_mess_email_id");
            if (vMessEmail !== null) messData.mess_email_id = vMessEmail.trim();

            const vMessAddress = getVal("edit_mess_address");
            if (vMessAddress !== null) messData.address = vMessAddress.trim();

            const vMessDesc = getVal("edit_mess_description");
            if (vMessDesc !== null) messData.mess_description = vMessDesc.trim();

            // Re-render main view & close modal
            renderProfile();
            closeModal();
        });
    }
}

// Initial setup
document.addEventListener("DOMContentLoaded", () => {
    renderProfile();
    initEvents();
    applyAdminPermissions();
});