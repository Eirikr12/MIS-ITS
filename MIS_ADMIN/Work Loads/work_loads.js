// Sample work load data (replace with data from your backend)
const workLoads = [
    { requestId: 1, description: "Software Installation", assignedTo: "Unassigned", status: "Pending" },
    { requestId: 2, description: "Printer Issue", assignedTo: "Unassigned", status: "Pending" },
    { requestId: 3, description: "Software Installation", assignedTo: "Unassigned", status: "Pending" },
    { requestId: 4, description: "Hardware Repair", assignedTo: "Naruto", status: "In Progress" },
    { requestId: 5, description: "Network Issue", assignedTo: "Sasuke", status: "Completed" }
];

const techTeam = ["Angelo", "Naruto", "Sasuke"];

const workLoadsList = document.getElementById("workLoadsList");

function populateTable() {
    workLoadsList.innerHTML = ""; // Clear existing rows
    workLoads.forEach(workLoad => {
        const row = document.createElement("tr");
        let statusClass = '';
        if (workLoad.status === "Pending") {
            statusClass = 'pending';
        } else if (workLoad.status === "In Progress") {
            statusClass = 'in-progress';
        } else if (workLoad.status === "Completed") {
            statusClass = 'completed';
        }

        row.innerHTML = `
            <td>${workLoad.requestId}</td>
            <td>${workLoad.description}</td>
            <td>
                ${workLoad.assignedTo === "Unassigned" ? `<select id="assignTo-${workLoad.requestId}">${techTeam.map(teamMember => `<option value="${teamMember}">${teamMember}</option>`).join('')}</select>` : workLoad.assignedTo}
            </td>
            <td class="${statusClass}">${workLoad.status}</td>
            <td>
                ${workLoad.assignedTo === "Unassigned" ? `<button onclick="assignWorkLoad(${workLoad.requestId}, document.getElementById('assignTo-${workLoad.requestId}').value)">Assign</button>` : ""}
            </td>
        `;
        workLoadsList.appendChild(row);
    });
}
populateTable();

function assignWorkLoad(requestId, techId) {
    if (!techId) return;
    // Send AJAX request to assign technician
    fetch('assign_work_load.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ request_id: requestId, tech_id: techId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Technician assigned successfully!');
            location.reload();
        } else {
            alert('Failed to assign technician.');
        }
    })
    .catch(error => console.error('Error:', error));
}