$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            type: 'POST',
            url: 'php/login.php',
            data: $(this).serialize(),
            success: function(response) {
                // This line ensures the response is treated as data, even if the server is glitchy
                let res = (typeof response === 'string') ? JSON.parse(response) : response;

                if (res.status === "success") {
                    // 1. Save the token to the device memory
                    localStorage.setItem("session_token", res.token);
                    
                    // 2. Force the browser to stay on the working HTTP path
                    window.location.href = "http://13.51.172.237/profile.html";
                } else {
                    alert("Login failed: " + res.message);
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                alert("Could not connect to the AWS server. Check your internet or if Apache is running.");
            }
        });
    });
});