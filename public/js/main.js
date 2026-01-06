// Confirmation for Delete Operations
function confirmDelete(itemName) {
    return confirm("Are you sure you want to delete this " + itemName + "? This action cannot be undone.");
}

// Simple Greeting based on time
document.addEventListener("DOMContentLoaded", function() {
    console.log("Pinky Petal Online Shopping System Loaded Successfully.");
});

// Real-time Role Selection Helper (during registration)
function updateRoleDescription() {
    const roleSelect = document.getElementById('roleSelection');
    const roleInfo = document.getElementById('roleInfo');
    if(roleSelect && roleInfo) {
        roleInfo.innerText = "You are registering as a: " + roleSelect.value;
    }
}