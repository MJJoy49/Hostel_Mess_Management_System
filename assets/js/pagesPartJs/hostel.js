// hostel.js – Profile (User + Mess)
// Data আসবে api/profileInfo.php থেকে (mysqli + $mysqli based PHP)

let IS_ADMIN = false; // server থেকে role আসবে
let userData = null;
let messData = null;

// ============================
// 1) PHP থেকে প্রোফাইল ডাটা আনা
// ============================
async function loadProfileData() {
    try {
        // main.php / index.php root ধরে নিচ্ছি:
        // profileInfo.php রাখা আছে ./api/profileInfo.php এ
        const response = await fetch("./api/profileInfo.php", {
            method: "GET",
            headers: {
                "Accept": "application/json"
            }
        });

        if (!response.ok) {
            throw new Error("HTTP error " + response.status);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || "Profile load failed");
        }

        userData = data.user;
        messData = data.mess;
        IS_ADMIN = !!data.is_admin;

    } catch (error) {
        console.error("Profile load error:", error);
        alert("Could not load profile information. Please reload the page.");
        // যাতে script পুরো বন্ধ না হয়ে যায়
        userData = userData || {};
        messData = messData || {};
    }
}

// ============================
// 2) প্রোফাইল ফটো (initials fallback)
// ============================
function renderProfilePhoto() {
    const img = document.getElementById("profile_photo");
    const initialsEl = document.getElementById("profile_initials");
    if (!img || !initialsEl || !userData) return;

    function showInitials() {
        const name = userData.full_name || "";
        const initials =
            name
                .split(" ")
                .filter(Boolean)
                .map((part) => part[0])
                .join("")
                .slice(0, 2)
                .toUpperCase() || "U";

        initialsEl.textContent = initials;
        initialsEl.style.display = "flex";
        img.style.display = "none";
    }

    if (userData.photo && userData.photo.trim() !== "") {
        img.src = userData.photo;
        img.onload = function () {
            img.style.display = "block";
            initialsEl.style.display = "none";
        };
        img.onerror = showInitials;
    } else {
        showInitials();
    }
}

// ============================
// 3) Main Profile UI render
// ============================
function renderProfile() {
    if (!userData || !messData) return;

    // role label সুন্দর করে দেখানোর জন্য
    let roleLabel = userData.role || "";
    if (roleLabel === "admin") roleLabel = "Admin";
    if (roleLabel === "member") roleLabel = "Member";

    // Header অংশ
    const headerName = document.getElementById("header_full_name");
    const headerRole = document.getElementById("header_role");
    const headerMessName = document.getElementById("header_mess_name");
    const headerJoined = document.getElementById("header_joined_date");

    if (headerName) headerName.textContent = userData.full_name || "";
    if (headerRole) headerRole.textContent = roleLabel;
    if (headerMessName) headerMessName.textContent = messData.mess_name || "";

    // joined_date না থাকলে created_at ব্যবহার
    let joinedDate = userData.joined_date || userData.created_at || "";
    if (joinedDate && joinedDate.toString) {
        joinedDate = joinedDate.toString().slice(0, 10);
    }
    if (headerJoined) headerJoined.textContent = joinedDate;

    // ---------- User section ----------
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

    Object.entries(userFieldIds).forEach(function ([dataKey, elementId]) {
        const el = document.getElementById(elementId);
        if (!el) return;

        let value = userData[dataKey];

        if (dataKey === "role") {
            value = roleLabel;
        }

        if (dataKey === "joined_date") {
            value = joinedDate;
        }

        el.textContent = value ?? "";
    });

    // ---------- Mess section ----------
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

    Object.entries(messFieldIds).forEach(function ([dataKey, elementId]) {
        const el = document.getElementById(elementId);
        if (!el) return;

        let value = messData[dataKey];

        if (dataKey === "created_at" && value && value.toString) {
            value = value.toString().slice(0, 10);
        }

        el.textContent = value ?? "";
    });

    // Photo
    renderProfilePhoto();
}

// ============================
// 4) Edit Modal form fill
// ============================
function fillEditForm() {
    if (!userData || !messData) return;

    const joinedDate =
        (userData.joined_date || userData.created_at || "").toString().slice(0, 10);

    // Read-only user fields
    const elUserId = document.getElementById("edit_user_id");
    const elRole = document.getElementById("edit_role");
    const elJoined = document.getElementById("edit_joined_date");

    if (elUserId) elUserId.value = userData.user_id || "";
    if (elRole) elRole.value = userData.role || "";
    if (elJoined) elJoined.value = joinedDate;

    // Editable user fields
    const userMap = {
        edit_full_name: userData.full_name,
        edit_gender: userData.gender || "",
        edit_contact_number: userData.contact_number,
        edit_email_id: userData.email_id,
        edit_blood_group: userData.blood_group || "",
        edit_religion: userData.religion,
        edit_profession: userData.profession,
        edit_user_address: userData.address,
        edit_photo: userData.photo || ""
    };

    Object.entries(userMap).forEach(function ([id, val]) {
        const el = document.getElementById(id);
        if (el) el.value = val || "";
    });

    // Read-only mess fields
    const messReadOnly = {
        edit_mess_id: messData.mess_id,
        edit_admin_name: messData.admin_name,
        edit_admin_email: messData.admin_email,
        edit_admin_id: messData.admin_id,
        edit_created_at: messData.created_at
    };
    Object.entries(messReadOnly).forEach(function ([id, val]) {
        const el = document.getElementById(id);
        if (el) el.value = val || "";
    });

    // Editable mess fields (admin only)
    const messEditable = {
        edit_mess_name: messData.mess_name,
        edit_capacity: messData.capacity,
        edit_mess_email_id: messData.mess_email_id,
        edit_mess_address: messData.address,
        edit_mess_description: messData.mess_description
    };
    Object.entries(messEditable).forEach(function ([id, val]) {
        const el = document.getElementById(id);
        if (el) el.value = val || "";
    });
}

// ============================
// 5) Admin permission apply
// ============================
function applyAdminPermissions() {
    const adminOnlyIds = [
        "edit_mess_name",
        "edit_capacity",
        "edit_mess_email_id",
        "edit_mess_address",
        "edit_mess_description"
    ];

    adminOnlyIds.forEach(function (id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.disabled = !IS_ADMIN;
    });
}

// ============================
// 6) Modal control
// ============================
function openModal() {
    const modal = document.getElementById("editModal");
    if (!modal) return;
    fillEditForm();
    applyAdminPermissions();
    modal.classList.add("open");
    document.body.classList.add("modal-open");
}

function closeModal() {
    const modal = document.getElementById("editModal");
    if (!modal) return;
    modal.classList.remove("open");
    document.body.classList.remove("modal-open");
}

// ============================
// 7) Events init
// ============================
function initEvents() {
    const editBtn = document.getElementById("editBtn");
    const closeBtn = document.getElementById("closeModal");
    const cancelBtn = document.getElementById("cancelEdit");
    const modal = document.getElementById("editModal");
    const form = document.getElementById("editForm");

    if (editBtn) {
        editBtn.addEventListener("click", openModal);
    }
    if (closeBtn) {
        closeBtn.addEventListener("click", closeModal);
    }
    if (cancelBtn) {
        cancelBtn.addEventListener("click", closeModal);
    }

    if (modal) {
        modal.addEventListener("click", function (e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }

    // এখন শুধু front‑end object update; পরে চাইলে DB update er জন্য আলাদা PHP বানাতে পারো
    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            if (!userData || !messData) return;

            function getVal(id) {
                const el = document.getElementById(id);
                if (!el || el.disabled) return null;
                return el.value;
            }

            // ---- userData update ----
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

            // ---- messData update (admin only fields) ----
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

            // UI refresh + modal বন্ধ
            renderProfile();
            closeModal();

            // TODO: এখানে চাইলে fetch দিয়ে profileUpdate.php তে POST করে DB update করতে পারো
        });
    }
}

// ============================
// 8) Entry point
// ============================
document.addEventListener("DOMContentLoaded", async function () {
    await loadProfileData();   // PHP থেকে ডাটা আনা
    renderProfile();           // UI তে বসানো
    initEvents();              // event গুলো attach
    applyAdminPermissions();   // admin হলে mess edit enable
});