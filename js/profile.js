$.ajax({
    type: 'POST',
    url: 'php/profile.php',
    data: $(this).serialize() + "&token=" + token,
    success: function(response) {
        // This ensures we are dealing with an object, not a raw string
        let res = (typeof response === 'string') ? JSON.parse(response) : response;
        
        if(res.message) {
            alert(res.message); 
        } else {
            alert("Update successful!");
        }
    },
    error: function(xhr) {
        console.log(xhr.responseText); // Check console if it still fails
        alert("Server error occurred.");
    }
});