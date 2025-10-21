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
    const rememberMe = $('#remember-me').is(':checked');

    // Simulate login (will be replaced with actual API call)
    if (email && password) {
        // Create mock user
        const user = {
            id: 1,
            email: email,
            username: email.split('@')[0],
            firstName: 'John',
            lastName: 'Doe',
            avatar: 'https://via.placeholder.com/200x200?text=User'
        };

        // Store user in app state
        AppState.currentUser = user;

        // Store in localStorage if remember me is checked
        if (rememberMe) {
            localStorage.setItem('albumrate_user', JSON.stringify(user));
        }

        // Update navigation
        updateNavigation();

        // Show success message
        showNotification('Login successful!', 'success');

        // Redirect to home
        SPARouter.navigate('home');
    } else {
        showNotification('Please fill in all fields', 'danger');
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

    if (password.length < 8) {
        showNotification('Password must be at least 8 characters', 'danger');
        return;
    }

    if (!termsAgree) {
        showNotification('Please agree to the Terms of Service', 'danger');
        return;
    }

    // Simulate registration (will be replaced with actual API call)
    const user = {
        id: Date.now(),
        email: email,
        username: username,
        firstName: firstName,
        lastName: lastName,
        avatar: 'https://via.placeholder.com/200x200?text=' + firstName.charAt(0) + lastName.charAt(0)
    };

    // Store user in app state
    AppState.currentUser = user;
    localStorage.setItem('albumrate_user', JSON.stringify(user));

    // Update navigation
    updateNavigation();

    // Show success message
    showNotification('Registration successful! Welcome to AlbumRate.', 'success');

    // Redirect to home
    SPARouter.navigate('home');
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
