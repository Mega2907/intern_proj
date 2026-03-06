$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault(); // Prevents the page from refreshing

        $.ajax({
            type: 'POST',
            url: 'php/login.php',
            data: $(this).serialize(),
            success: function(response) {
                // Check if the PHP backend sent a "success" status
                if (response.status === "success") {
                    // REQUIREMENT: Save session to LocalStorage
                    localStorage.setItem("session_token", response.token);
                    
                    // ACTION: Manually redirect to the profile page
                    window.location.href = "profile.html";
                } else {
                    alert("Login failed: " + response.message);
                }
            },
            error: function() {
                alert("Could not connect to the server. Is Apache running?");
            }
        });
    });
});