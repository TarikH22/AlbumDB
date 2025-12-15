/**
 * Authentication Logic
 * Handles login, register, and authentication state
 */

/**
 * Load login page
 */
function loadLoginPage() {
    // Set up login form handler
    setTimeout(() => {
        $('#login-form').on('submit', function(e) {
            e.preventDefault();
            handleLogin();
        });
    }, 100);
}

/**
 * Initialize UserService menu on page load
 */
$(document).ready(function() {
    if (typeof UserService !== 'undefined') {
        UserService.generateMenuItems();
    }
});

/**
 * Load register page
 */
function loadRegisterPage() {
    // Set up register form handler
    setTimeout(() => {
        $('#register-form').on('submit', function(e) {
            e.preventDefault();
            handleRegister();
        });
    }, 100);
}

/**
 * Handle login submission
 */
function handleLogin() {
    const email = $('#login-email').val();
    const password = $('#login-password').val();

    // Validation
    if (!email || !password) {
        showNotification('Please fill in all fields', 'danger');
        return;
    }

    // Call UserService login
    if (typeof UserService !== 'undefined') {
        UserService.login({ email: email, password: password });
    } else {
        showNotification('UserService not available', 'danger');
    }
}

/**
 * Handle registration submission
 */
function handleRegister() {
    const firstName = $('#register-firstname').val();
    const lastName = $('#register-lastname').val();
    const username = $('#register-username').val();
    const email = $('#register-email').val();
    const password = $('#register-password').val();
    const confirmPassword = $('#register-confirm-password').val();
    const termsAgree = $('#terms-agree').is(':checked');

    // Validation
    if (!firstName || !lastName || !username || !email || !password || !confirmPassword) {
        showNotification('Please fill in all fields', 'danger');
        return;
    }

    if (password !== confirmPassword) {
        showNotification('Passwords do not match', 'danger');
        return;
    }

    if (password.length < 6) {
        showNotification('Password must be at least 6 characters', 'danger');
        return;
    }

    if (!termsAgree) {
        showNotification('Please agree to the Terms of Service', 'danger');
        return;
    }

    // Call UserService register
    if (typeof UserService !== 'undefined') {
        UserService.register({
            username: username,
            email: email,
            password: password,
            first_name: firstName,
            last_name: lastName
        });
    } else {
        showNotification('UserService not available', 'danger');
    }
}

/**
 * Show notification message
 */
function showNotification(message, type = 'info') {
    const alert = `
        <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3"
             style="z-index: 9999; min-width: 300px;" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    $('body').append(alert);

    // Auto-dismiss after 3 seconds
    setTimeout(() => {
        $('.alert').alert('close');
    }, 3000);
}
