$(document).ready(function() {
    // 1. Retrieve the token saved during login
    const token = localStorage.getItem('session_token');

    if (!token) {
        alert("Please login first.");
        window.location.href = "login.html";
        return;
    }

    $.ajax({
        type: 'GET',
        url: 'php/profile.php',
        data: { token: token },
        success: function(res) {
            if (res.status === "success" && res.data) {
            // Fill the input fields with saved data
                $('input[name="age"]').val(res.data.age);
                $('input[name="dob"]').val(res.data.dob);
                $('input[name="contact"]').val(res.data.contact);
            }
    }
    });

    // 3. Handle Profile Update Form
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            type: 'POST',
            url: 'php/profile.php', // Relative path is key for multi-device support
            data: $(this).serialize() + "&token=" + encodeURIComponent(token),
            success: function(response) {
                // Parse response if it comes back as a string
                let res = (typeof response === 'string') ? JSON.parse(response) : response;

                if (res.status === "success") {
                    alert("✅ Success: " + res.message);
                } else {
                    alert("❌ Error: " + res.message);
                    if (res.message.includes("expired")) {
                        window.location.href = "login.html";
                    }
                }
            },
            error: function(xhr) {
                console.error("Connection error:", xhr.responseText);
                alert("Could not connect to AWS server. Check your connection.");
            }
        });
    });

    // 4. Handle Logout specifically for other devices
    $('#logoutBtn').on('click', function(e) {
        e.preventDefault();
        // Redirect to logout script with the current token
        window.location.href = "php/logout.php?token=" + encodeURIComponent(token);
    });
});