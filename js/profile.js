// 1. Always get the token first
const token = localStorage.getItem('session_token');

// 2. Redirect if no token is found (Security check)
if (!token) {
    alert("Session expired. Please login again.");
    window.location.href = "login.html";
}

// 3. Handle the form submission
$('#profileForm').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        type: 'POST',
        url: 'php/profile.php', // Use relative path (works on all devices)
        data: $(this).serialize() + "&token=" + encodeURIComponent(token),
        dataType: 'json', // Tells jQuery to expect JSON automatically
        success: function(res) {
            if (res.status === "success") {
                alert(res.message);
            } else {
                alert("Error: " + res.message);
                if (res.message.includes("expired")) {
                    window.location.href = "login.html";
                }
            }
        },
        error: function(xhr) {
            console.error("Status: " + xhr.status);
            console.error("Response: " + xhr.responseText);
            alert("Could not connect to the server. Please check your internet.");
        }
    });
});