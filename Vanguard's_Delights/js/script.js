$(document).ready(function() {
    $('#loginBtn').on('click', function() {
        let user = prompt("Username:");
        let pass = prompt("Password:");

        if(user && pass) {
            $.ajax({
                url: 'login_handler.php',
                method: 'POST',
                data: {
                    username: user,
                    password: pass
                },
                success: function(response) {
                    if(response === "success") {
                        alert("Welcome to Vanguard's Delights!");
                        location.reload(); // Refresh para makita ang session name
                    } else {
                        alert("Invalid Login. Try 'admin' and '1234'");
                    }
                }
            });
        }
    });
});