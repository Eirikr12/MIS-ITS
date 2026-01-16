// filepath: c:\xampp\htdocs\MIS_ITS\MIS_ADMIN\Request Status\request_status.js

// Fetch request data from the backend
async function fetchRequests() {
    try {
        const response = await fetch("fetch_requests.php"); // Backend script to fetch request data
        const requests = await response.json(); // Parse the JSON response
        populateTable(requests); // Populate the table with fetched data
    } catch (error) {
        console.error("Error fetching requests:", error);
    }
}

// Populate the table with request data
function populateTable(requests) {
    const requestList = document.getElementById("requestList");
    requestList.innerHTML = ""; // Clear existing rows

    requests.forEach(request => {
        const row = document.createElement("tr");
        let statusClass = '';
        if (request.status === "Pending") {
            statusClass = 'pending';
        } else if (request.status === "In Progress") {
            statusClass = 'in-progress';
        } else if (request.status === "Completed") {
            statusClass = 'completed';
        }

        row.innerHTML = `
            <td>${request.requestId}</td>
            <td>${request.employeeName}</td>
            <td>${request.department}</td>
            <td>${request.requestDescription}</td>
            <td>${request.dateRequested}</td>
            <td class="${statusClass}">${request.status}</td>
        `;
        requestList.appendChild(row);
    });
}

// Fetch requests on page load
fetchRequests();