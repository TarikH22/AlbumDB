var UserService = {
  init: function () {
    var token = localStorage.getItem("user_token");
    if (token && token !== undefined) {
      window.location.replace("#home");
    }
    $("#login-form").validate({
      submitHandler: function (form) {
        var entity = Object.fromEntries(new FormData(form).entries());
        UserService.login(entity);
      },
    });
  },

  login: function (entity) {
    // Prevent duplicate submissions
    if (this._loginInProgress) {
      console.log("Login already in progress, ignoring duplicate request");
      return;
    }
    this._loginInProgress = true;

    $.ajax({
      url: Constants.PROJECT_BASE_URL + "auth/login",
      type: "POST",
      data: JSON.stringify(entity),
      contentType: "application/json",
      dataType: "json",
      success: function (result) {
        UserService._loginInProgress = false;
        console.log("Login success:", result);
        if (result.success && result.data && result.data.token) {
          localStorage.setItem("user_token", result.data.token);
          toastr.success("Login successful!");
          UserService.generateMenuItems();
          window.location.replace("#home");
        } else {
          toastr.error(result.error || "Login failed");
        }
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        UserService._loginInProgress = false;
        console.error("Login error:", XMLHttpRequest.responseText);
        try {
          const errorResponse = JSON.parse(XMLHttpRequest.responseText);
          toastr.error(errorResponse.error || "Login failed");
        } catch (e) {
          toastr.error(XMLHttpRequest.responseText || 'Login failed');
        }
      },
    });
  },

  register: function (entity) {
    // Prevent duplicate submissions
    if (this._registerInProgress) {
      console.log("Registration already in progress, ignoring duplicate request");
      return;
    }
    this._registerInProgress = true;

    $.ajax({
      url: Constants.PROJECT_BASE_URL + "auth/register",
      type: "POST",
      data: JSON.stringify(entity),
      contentType: "application/json",
      dataType: "json",
      success: function (result) {
        UserService._registerInProgress = false;
        console.log("Register AJAX success callback - result:", result);
        if (result.success) {
          console.log("Registration was successful");
          toastr.success("Registration successful! Please login.");
          setTimeout(() => {
            window.location.replace("#login");
          }, 1000);
        } else {
          console.log("Registration failed with error:", result.error);
          toastr.error(result.error || "Registration failed");
        }
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        UserService._registerInProgress = false;
        console.error("Register AJAX error callback - status:", XMLHttpRequest.status);
        console.error("Register AJAX error callback - responseText:", XMLHttpRequest.responseText);
        try {
          const errorResponse = JSON.parse(XMLHttpRequest.responseText);
          console.log("Parsed error response:", errorResponse);
          toastr.error(errorResponse.error || "Registration failed");
        } catch (e) {
          console.error("Failed to parse error response:", e);
          toastr.error(XMLHttpRequest.responseText || 'Registration failed');
        }
      },
    });
  },

  logout: function () {
    localStorage.clear();
    UserService.generateMenuItems();
    toastr.success("Logged out successfully");
    window.location.replace("#login");
  },

  getProfile: function (userId) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("You are not logged in. Please log in to view your profile.");
      window.location.replace("#login");
      return;
    }

    // If userId is not provided, get it from the token
    if (!userId) {
      const decoded = Utils.parseJwt(token);
      if (decoded && decoded.user && decoded.user.user_id) {
        userId = decoded.user.user_id;
      } else {
        toastr.error("Unable to get user ID from token");
        return;
      }
    }

    $.ajax({
      url: Constants.PROJECT_BASE_URL + "users/" + userId,
      type: "GET",
      beforeSend: function(xhr) {
        xhr.setRequestHeader("Authorization", "Bearer " + token);
      },
      contentType: "application/json",
      dataType: "json",
      success: function (response) {
        console.log("Profile data received:", response);

        const userProfile = response.data || response;
        
        // Display full name
        $("#profile-fullName").text(
          (userProfile.first_name || '') + ' ' + (userProfile.last_name || '')
        );
        
        // Display role
        let roleText = "User";
        if (userProfile.roles && userProfile.roles.toLowerCase() === "admin") {
          roleText = "ADMIN";
        }
        $("#profile-role").text(roleText);

        // Display profile details
        $("#profile-firstName").text(userProfile.first_name || 'N/A');
        $("#profile-lastName").text(userProfile.last_name || 'N/A');
        $("#profile-email").text(userProfile.email || 'N/A');
        $("#profile-username").text(userProfile.username || 'N/A');
        $("#profile-createdAt").text(userProfile.created_at ? new Date(userProfile.created_at).toLocaleDateString() : 'N/A');
        
        if (userProfile.avatar_url) {
          $("#profile-avatar").attr("src", userProfile.avatar_url);
        }
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        console.error("Error fetching profile:", XMLHttpRequest.responseText);
        toastr.error(XMLHttpRequest?.responseText ? XMLHttpRequest.responseText : 'Error fetching profile');
        if (XMLHttpRequest.status === 401 || XMLHttpRequest.status === 403) {
          toastr.warning("Your session has expired or is invalid. Please log in again.");
          UserService.logout();
        }
      },
    });
  },

  updateProfile: function (userId, data) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("You are not logged in.");
      window.location.replace("#login");
      return;
    }

    $.ajax({
      url: Constants.PROJECT_BASE_URL + "users/" + userId,
      type: "PUT",
      data: JSON.stringify(data),
      beforeSend: function(xhr) {
        xhr.setRequestHeader("Authorization", "Bearer " + token);
      },
      contentType: "application/json",
      dataType: "json",
      success: function (response) {
        console.log("Profile updated:", response);
        toastr.success("Profile updated successfully!");
        UserService.getProfile(userId);
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        console.error("Error updating profile:", XMLHttpRequest.responseText);
        toastr.error(XMLHttpRequest?.responseText ? XMLHttpRequest.responseText : 'Error updating profile');
      },
    });
  },

  generateMenuItems: function () {
    const token = localStorage.getItem("user_token");
    const authNav = document.getElementById("auth-nav");
    const userNav = document.getElementById("user-nav");
    const adminNavItem = document.getElementById("admin-nav-item");
    const dashboardNavItem = document.getElementById("dashboard-nav-item");
    const favoritesNavItem = document.getElementById("favorites-nav-item");

    if (!authNav || !userNav) return;

    if (token && this.isAuthenticated()) {
      const decoded = Utils.parseJwt(token);
      const user = decoded ? decoded.user : null;

      if (user) {
        // Hide login/register nav, show user nav
        authNav.classList.add("d-none");
        userNav.classList.remove("d-none");

        // Show dashboard and favorites for all authenticated users
        if (dashboardNavItem) dashboardNavItem.classList.remove("d-none");
        if (favoritesNavItem) favoritesNavItem.classList.remove("d-none");

        // Show/hide admin nav item based on role
        // Check both decoded.roles (top level) and user.roles/user.role
        const userRole = decoded.roles || user.roles || user.role;
        if (adminNavItem) {
          if (userRole && userRole.toUpperCase() === "ADMIN") {
            adminNavItem.classList.remove("d-none");
          } else {
            adminNavItem.classList.add("d-none");
          }
        }

        // Set up logout button
        const logoutBtn = document.getElementById("logout-btn");
        if (logoutBtn) {
          // Remove any existing event listeners by cloning
          const newLogoutBtn = logoutBtn.cloneNode(true);
          logoutBtn.parentNode.replaceChild(newLogoutBtn, logoutBtn);
          
          newLogoutBtn.addEventListener("click", function (e) {
            e.preventDefault();
            UserService.logout();
          });
        }
      } else {
        // Invalid token, show login
        authNav.classList.remove("d-none");
        userNav.classList.add("d-none");
        if (adminNavItem) adminNavItem.classList.add("d-none");
        if (dashboardNavItem) dashboardNavItem.classList.add("d-none");
        if (favoritesNavItem) favoritesNavItem.classList.add("d-none");
      }
    } else {
      // No token or expired, show login/register
      authNav.classList.remove("d-none");
      userNav.classList.add("d-none");
      if (adminNavItem) adminNavItem.classList.add("d-none");
      if (dashboardNavItem) dashboardNavItem.classList.add("d-none");
      if (favoritesNavItem) favoritesNavItem.classList.add("d-none");
    }
  },

  isAuthenticated: function() {
    const token = localStorage.getItem("user_token");
    if (!token) return false;
    
    const decoded = Utils.parseJwt(token);
    if (!decoded || !decoded.exp) return false;
    
    // Check if token is expired
    const currentTime = Math.floor(Date.now() / 1000);
    return decoded.exp > currentTime;
  },

  getCurrentUser: function() {
    const token = localStorage.getItem("user_token");
    if (!token) return null;
    
    const decoded = Utils.parseJwt(token);
    return decoded ? decoded.user : null;
  },

  getAll: function() {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("You must be logged in");
      return Promise.reject("No token");
    }

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "users",
        type: "GET",
        beforeSend: function(xhr) {
          xhr.setRequestHeader("Authorization", "Bearer " + token);
        },
        contentType: "application/json",
        dataType: "json",
        success: function (response) {
          if (response.success && response.data) {
            resolve(response.data.users || response.data || []);
          } else {
            resolve([]);
          }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          console.error("Error getting users:", XMLHttpRequest.responseText);
          toastr.error("Failed to load users");
          reject(XMLHttpRequest.responseText);
        },
      });
    });
  },

  getStats: function() {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("You must be logged in");
      return Promise.reject("No token");
    }

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "users/stats/all",
        type: "GET",
        beforeSend: function(xhr) {
          xhr.setRequestHeader("Authorization", "Bearer " + token);
        },
        contentType: "application/json",
        dataType: "json",
        success: function (response) {
          if (response.success && response.data) {
            resolve(response.data);
          } else {
            resolve({});
          }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          console.error("Error getting stats:", XMLHttpRequest.responseText);
          toastr.error("Failed to load statistics");
          reject(XMLHttpRequest.responseText);
        },
      });
    });
  }
};
