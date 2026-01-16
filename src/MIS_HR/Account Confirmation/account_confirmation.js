function requestEdit(userID, EmpID) {
    if (!confirm(`Request edit for:\n\nID: ${userID}\nEmployee ID: ${EmpID}\n\nContinue?`)) {
        return;
    }

    const field = prompt("Which field do you want to edit?\n\nOptions: firstname, middlename, lastname, contact, department, position", "Type here...");
    if (!field) {
        alert("No field selected. Request cancelled.");
        return;
    }

    const cleanField = field.toLowerCase().trim();
    const validFields = ['firstname', 'middlename', 'lastname', 'contact', 'department', 'position'];

    if (!validFields.includes(cleanField)) {
        alert("Invalid field selected. Please try again.");
        return;
    }

    const newValue = prompt(`Enter new value for ${cleanField}:`, "");
    if (!newValue) {
        alert("No value entered. Request cancelled.");
        return;
    }

    const confirmationMessage = 
        `Please confirm your edit request:\n\n` +
        `ID: ${userID}\nEmployee ID: ${EmpID}\n` +
        `Field to change: ${cleanField}\n` +
        `New Value: ${newValue}\n\n` +
        `Submit this request?`;

    if (!confirm(confirmationMessage)) {
        alert("Edit request cancelled.");
        return;
    }

    // Submit the data via fetch()
    fetch('submit_edit_request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            userID,
            EmpID,
            field: cleanField,
            newValue
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Edit request submitted successfully.");
        } else {
            alert("Failed to submit request: " + data.message);
        }
    })
    .catch(error => {
        alert("An error occurred: " + error);
    });
}
