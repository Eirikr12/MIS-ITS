// Sample user data (replace with data from your backend)
const users = [
    { firstName: "Angelo", lastName: "Ecleo", email: "gelo@gmail.com", role: "Employee", status: "Approved" },
    { firstName: "Vincent", lastName: "Malageño", email: "vincent@yahoo.com", role: "HR", status: "Pending" },
    { firstName: "Kenneth", lastName: "Briones", email: "ken@gmail.com", role: "Tech Team", status: "Pending" }
];

const userList = document.getElementById("userList");

function populateTable() {
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
            <td>${user.firstName}</td>
            <td>${user.lastName}</td>
            <td>${user.email}</td>
            <td>${user.role}</td>
            <td class="${statusClass}">${user.status}</td>
            <td>
                ${user.status === "Pending" ? `<button onclick="approveUser('${user.email}')">Approve</button>` : ""}
            </td>
        `;
        userList.appendChild(row);
    });
}
populateTable();

function approveUser(email) {
    console.log(`Approving user with email: ${email}`);
    const user = users.find(u => u.email === email);
    if (user) {
        user.status = "Approved";
        populateTable(); // Update the table
    }
}