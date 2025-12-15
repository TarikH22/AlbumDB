var TrackService = {
  /**
   * Get track by ID
   * @param {number} id - Track ID
   * @returns {Promise}
   */
  getById: function (id) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("Please login first");
      window.location.replace("#login");
      return;
    }

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "tracks/" + id,
        type: "GET",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Get track error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to fetch track");
          } catch (e) {
            toastr.error("Failed to fetch track");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Get all tracks for an album
   * @param {number} albumId - Album ID
   * @returns {Promise}
   */
  getByAlbum: function (albumId) {
    const token = localStorage.getItem("user_token");
    const headers = {};
    if (token) {
      headers.Authorization = "Bearer " + token;
    }

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "tracks/album/" + albumId,
        type: "GET",
        headers: headers,
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Get album tracks error:", xhr.responseText);
          // Only show error toast if user is logged in
          if (token) {
            try {
              const errorResponse = JSON.parse(xhr.responseText);
              toastr.error(errorResponse.error || "Failed to fetch album tracks");
            } catch (e) {
              toastr.error("Failed to fetch album tracks");
            }
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Create a new track (Admin only)
   * @param {Object} trackData - { album_id, track_number, title, duration }
   * @returns {Promise}
   */
  create: function (trackData) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("Please login first");
      window.location.replace("#login");
      return;
    }

    // Prevent duplicate submissions
    if (this._createInProgress) {
      console.log("Create track already in progress");
      return;
    }
    this._createInProgress = true;

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "tracks",
        type: "POST",
        data: JSON.stringify(trackData),
        contentType: "application/json",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          TrackService._createInProgress = false;
          toastr.success("Track created successfully!");
          resolve(result);
        },
        error: function (xhr, status, error) {
          TrackService._createInProgress = false;
          console.error("Create track error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to create track");
          } catch (e) {
            toastr.error("Failed to create track");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Create multiple tracks (Admin only)
   * @param {number} albumId - Album ID
   * @param {Array} tracks - Array of track objects
   * @returns {Promise}
   */
  createBulk: function (albumId, tracks) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("Please login first");
      window.location.replace("#login");
      return;
    }

    // Prevent duplicate submissions
    if (this._bulkCreateInProgress) {
      console.log("Bulk create tracks already in progress");
      return;
    }
    this._bulkCreateInProgress = true;

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "tracks/bulk",
        type: "POST",
        data: JSON.stringify({ album_id: albumId, tracks: tracks }),
        contentType: "application/json",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          TrackService._bulkCreateInProgress = false;
          toastr.success("Tracks created successfully!");
          resolve(result);
        },
        error: function (xhr, status, error) {
          TrackService._bulkCreateInProgress = false;
          console.error("Bulk create tracks error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to create tracks");
          } catch (e) {
            toastr.error("Failed to create tracks");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Update a track (Admin only)
   * @param {number} id - Track ID
   * @param {Object} trackData - Track fields to update
   * @returns {Promise}
   */
  update: function (id, trackData) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("Please login first");
      window.location.replace("#login");
      return;
    }

    // Prevent duplicate submissions
    if (this._updateInProgress) {
      console.log("Update track already in progress");
      return;
    }
    this._updateInProgress = true;

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "tracks/" + id,
        type: "PUT",
        data: JSON.stringify(trackData),
        contentType: "application/json",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          TrackService._updateInProgress = false;
          toastr.success("Track updated successfully!");
          resolve(result);
        },
        error: function (xhr, status, error) {
          TrackService._updateInProgress = false;
          console.error("Update track error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to update track");
          } catch (e) {
            toastr.error("Failed to update track");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Delete a track (Admin only)
   * @param {number} id - Track ID
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
        url: Constants.PROJECT_BASE_URL + "tracks/" + id,
        type: "DELETE",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          toastr.success("Track deleted successfully!");
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Delete track error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to delete track");
          } catch (e) {
            toastr.error("Failed to delete track");
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
