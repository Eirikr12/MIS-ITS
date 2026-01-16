// Fetch user data from the backend
async function fetchUsers() {
    try {
        const response = await fetch("fetch_users.php"); // Backend script to fetch user data
        const users = await response.json(); // Parse the JSON response
        populateTable(users); // Populate the table with fetched data
    } catch (error) {
        console.error("Error fetching users:", error);
    }
}

// Populate the table with user data
function populateTable(users) {
    const userList = document.getElementById("userList");
    userList.innerHTML = ""; // Clear existing rows

    users.forEach(user => {
        const row = document.createElement("tr");
        let statusClass = '';
        if (user.status === "Pending") {
            statusClass = 'pending';
        } else if (user.status === "Approved") {
            statusClass = 'approved';
        }
        row.innerHTML = `
            <td>${user.first_name}</td>
            <td>${user.last_name}</td>
            <td>${user.email}</td>
            <td>${user.role}</td>
            <td class="${statusClass}">${user.status}</td>
            <td>
                ${user.status === "Pending" ? `<button onclick="approveUser('${user.id}')">Approve</button>` : ""}
            </td>
        `;
        userList.appendChild(row);
    });
}

// Approve a user
async function approveUser(userId) {
    try {
        const response = await fetch("approve_user.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ id: userId }),
        });

        const result = await response.json();
        if (result.success) {
            console.log(`User with ID ${userId} approved successfully.`);
            fetchUsers(); // Refresh the table
        } else {
            console.error("Error approving user:", result.message);
        }
    } catch (error) {
        console.error("Error approving user:", error);
    }
}

// Fetch users on page load
fetchUsers();