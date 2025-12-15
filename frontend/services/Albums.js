var AlbumService = {
  /**
   * Get all albums with optional pagination
   * @param {Object} params - { limit: number, offset: number }
   * @returns {Promise}
   */
  getAll: function (params = {}) {
    const token = localStorage.getItem("user_token");

    const queryParams = new URLSearchParams(params).toString();
    const url = Constants.PROJECT_BASE_URL + "albums" + (queryParams ? "?" + queryParams : "");

    const headers = {};
    if (token) {
      headers.Authorization = "Bearer " + token;
    }

    return new Promise((resolve, reject) => {
      $.ajax({
        url: url,
        type: "GET",
        headers: headers,
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Get albums error:", xhr.responseText);
          // Only show error toast if user is logged in (for admin operations)
          if (token) {
            try {
              const errorResponse = JSON.parse(xhr.responseText);
              toastr.error(errorResponse.error || "Failed to fetch albums");
            } catch (e) {
              toastr.error("Failed to fetch albums");
            }
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Get single album by ID
   * @param {number} id - Album ID
   * @returns {Promise}
   */
  getById: function (id) {
    const token = localStorage.getItem("user_token");
    const headers = {};
    if (token) {
      headers.Authorization = "Bearer " + token;
    }

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "albums/" + id,
        type: "GET",
        headers: headers,
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Get album error:", xhr.responseText);
          // Only show error toast if user is logged in
          if (token) {
            try {
              const errorResponse = JSON.parse(xhr.responseText);
              toastr.error(errorResponse.error || "Failed to fetch album");
            } catch (e) {
              toastr.error("Failed to fetch album");
            }
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Create a new album
   * @param {Object} albumData - { title, artist, genre, year, cover_image_url, description }
   * @returns {Promise}
   */
  create: function (albumData) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("Please login first");
      window.location.replace("#login");
      return;
    }

    // Prevent duplicate submissions
    if (this._createInProgress) {
      console.log("Create album already in progress");
      return;
    }
    this._createInProgress = true;

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "albums",
        type: "POST",
        data: JSON.stringify(albumData),
        contentType: "application/json",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          AlbumService._createInProgress = false;
          toastr.success("Album created successfully!");
          resolve(result);
        },
        error: function (xhr, status, error) {
          AlbumService._createInProgress = false;
          console.error("Create album error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to create album");
          } catch (e) {
            toastr.error("Failed to create album");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Update an existing album
   * @param {number} id - Album ID
   * @param {Object} albumData - Album fields to update
   * @returns {Promise}
   */
  update: function (id, albumData) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("Please login first");
      window.location.replace("#login");
      return;
    }

    // Prevent duplicate submissions
    if (this._updateInProgress) {
      console.log("Update album already in progress");
      return;
    }
    this._updateInProgress = true;

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "albums/" + id,
        type: "PUT",
        data: JSON.stringify(albumData),
        contentType: "application/json",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          AlbumService._updateInProgress = false;
          toastr.success("Album updated successfully!");
          resolve(result);
        },
        error: function (xhr, status, error) {
          AlbumService._updateInProgress = false;
          console.error("Update album error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to update album");
          } catch (e) {
            toastr.error("Failed to update album");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Delete an album (Admin only)
   * @param {number} id - Album ID
   * @returns {Promise}
   */
  delete: function (id) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("Please login first");
      window.location.replace("#login");
      return;
    }

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "albums/" + id,
        type: "DELETE",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          toastr.success("Album deleted successfully!");
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Delete album error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to delete album");
          } catch (e) {
            toastr.error("Failed to delete album");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Search albums
   * @param {string} searchTerm - Search query
   * @returns {Promise}
   */
  search: function (searchTerm) {
    const token = localStorage.getItem("user_token");
    const headers = {};
    if (token) {
      headers.Authorization = "Bearer " + token;
    }

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "albums/search?q=" + encodeURIComponent(searchTerm),
        type: "GET",
        headers: headers,
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Search albums error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to search albums");
          } catch (e) {
            toastr.error("Failed to search albums");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Get top rated albums
   * @param {number} limit - Number of results to return
   * @returns {Promise}
   */
  getTopRated: function (limit = 10) {
    const token = localStorage.getItem("user_token");

    const headers = {};
    if (token) {
      headers.Authorization = "Bearer " + token;
    }

    const url = Constants.PROJECT_BASE_URL + "albums/top-rated?limit=" + limit;
    console.log("Fetching top rated albums from:", url);

    return new Promise((resolve, reject) => {
      $.ajax({
        url: url,
        type: "GET",
        headers: headers,
        success: function (result) {
          console.log("Top rated albums raw response:", result);
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Get top rated albums error:", {
            status: xhr.status,
            statusText: xhr.statusText,
            responseText: xhr.responseText,
            error: error
          });
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.errors ? errorResponse.errors.join(", ") : (errorResponse.error || "Failed to fetch top rated albums"));
          } catch (e) {
            toastr.error("Failed to fetch top rated albums: " + xhr.status + " " + xhr.statusText);
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Get albums by genre
   * @param {string} genre - Genre name
   * @param {number} limit - Number of results
   * @returns {Promise}
   */
  getByGenre: function (genre, limit = 20) {
    const token = localStorage.getItem("user_token");
    const headers = {};
    if (token) {
      headers.Authorization = "Bearer " + token;
    }

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "albums/genre/" + encodeURIComponent(genre) + "?limit=" + limit,
        type: "GET",
        headers: headers,
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Get albums by genre error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to fetch albums by genre");
          } catch (e) {
            toastr.error("Failed to fetch albums by genre");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Check if current user is admin
   * @returns {boolean}
   */
  isAdmin: function () {
    const token = localStorage.getItem("user_token");
    if (!token) return false;

    try {
      const payload = Utils.parseJwt(token);
      const role = payload.roles || (payload.user && payload.user.roles) || payload.role;
      return role && role.toUpperCase() === "ADMIN";
    } catch (e) {
      return false;
    }
  },
};
