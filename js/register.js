$(document).ready(function() {
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'php/register.php',
            data: $(this).serialize(),
            success: function(response) {
                // If the PHP returns a string, we parse it
                let res = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (res.status === "success") {
                    alert(res.message);
                    window.location.href = "login.html";
                } else {
                    alert("Error: " + res.message);
                }
            },
            error: function(xhr) {
                alert("Critical Error: " + xhr.responseText);
            }
        });
    });
});