document.getElementById('saveProfile').addEventListener('click', function() {
    const adminName = document.getElementById('adminName').value;
    const contactNumber = document.getElementById('contactNumber').value;
    const address = document.getElementById('address').value;
    const email = document.getElementById('email').value;

    // Here you would typically send this data to your backend server
    console.log({ adminName, contactNumber, address, email });

    // Example: update the page with the new info.
    // document.getElementById('adminName').value = adminName;
});