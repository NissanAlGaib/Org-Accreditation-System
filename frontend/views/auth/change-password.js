document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('changePasswordForm');
    const button = document.getElementById('changePasswordButton');
    const message = document.getElementById('changePasswordMessage');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (newPassword !== confirmPassword) {
            message.style.color = 'red';
            message.textContent = 'Passwords do not match';
            return;
        }

        if (newPassword.length < 8) {
            message.style.color = 'red';
            message.textContent = 'Password must be at least 8 characters long';
            return;
        }

        button.disabled = true;
        button.textContent = 'Changing Password...';
        message.textContent = '';

        const data = {
            action: 'change_password',
            new_password: newPassword
        };

        fetch('/Org-Accreditation-System/backend/api/user_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                message.style.color = 'green';
                message.textContent = data.message;
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            } else {
                message.style.color = 'red';
                message.textContent = data.message;
            }
        })
        .catch(error => {
            message.style.color = 'red';
            message.textContent = 'An error occurred: ' + error.message;
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = 'Change Password';
        });
    });
});
