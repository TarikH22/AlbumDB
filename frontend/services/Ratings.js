var RatingService = {
  /**
   * Get rating by ID
   * @param {number} id - Rating ID
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
        url: Constants.PROJECT_BASE_URL + "ratings/" + id,
        type: "GET",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Get rating error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to fetch rating");
          } catch (e) {
            toastr.error("Failed to fetch rating");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Get ratings by user
   * @param {number} userId - User ID (optional, uses current user if not provided)
   * @returns {Promise}
   */
  getByUser: function (userId = null) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("Please login first");
      window.location.replace("#login");
      return;
    }

    // If no userId provided, get it from token
    if (!userId) {
      try {
        const payload = Utils.parseJwt(token);
        userId = payload.user?.user_id || payload.user_id;
      } catch (e) {
        toastr.error("Invalid token");
        return;
      }
    }

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "ratings/user/" + userId,
        type: "GET",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Get user ratings error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to fetch user ratings");
          } catch (e) {
            toastr.error("Failed to fetch user ratings");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Get ratings for an album
   * @param {number} albumId - Album ID
   * @returns {Promise}
   */
  getByAlbum: function (albumId) {
    const token = localStorage.getItem("user_token");

    return new Promise((resolve, reject) => {
      const ajaxOptions = {
        url: Constants.PROJECT_BASE_URL + "ratings/album/" + albumId,
        type: "GET",
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Get album ratings error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to fetch album ratings");
          } catch (e) {
            toastr.error("Failed to fetch album ratings");
          }
          reject(error);
        },
      };

      // Add authorization header only if token exists
      if (token) {
        ajaxOptions.headers = {
          Authorization: "Bearer " + token,
        };
      }

      $.ajax(ajaxOptions);
    });
  },

  /**
   * Get user's rating for a specific album
   * @param {number} userId - User ID (optional, uses current user if not provided)
   * @param {number} albumId - Album ID
   * @returns {Promise}
   */
  getUserAlbumRating: function (albumId, userId = null) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("Please login first");
      window.location.replace("#login");
      return;
    }

    // If no userId provided, get it from token
    if (!userId) {
      try {
        const payload = Utils.parseJwt(token);
        userId = payload.user?.user_id || payload.user_id;
      } catch (e) {
        toastr.error("Invalid token");
        return;
      }
    }

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "ratings/user/" + userId + "/album/" + albumId,
        type: "GET",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Get user album rating error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            // Don't show error if rating doesn't exist (404)
            if (xhr.status !== 404) {
              toastr.error(errorResponse.error || "Failed to fetch rating");
            }
          } catch (e) {
            if (xhr.status !== 404) {
              toastr.error("Failed to fetch rating");
            }
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Get average rating for an album
   * @param {number} albumId - Album ID
   * @returns {Promise}
   */
  getAlbumAverage: function (albumId) {
    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "ratings/album/" + albumId,
        type: "GET",
        success: function (result) {
          // Extract stats from response
          const stats = result.data?.stats || result.stats || {};
          resolve({
            data: {
              average_rating: stats.avg_rating || stats.average_rating || 0,
              rating_count: stats.total_ratings || stats.rating_count || 0
            }
          });
        },
        error: function (xhr, status, error) {
          console.error("Get album average rating error:", xhr.responseText);
          // Don't show error toast for public endpoint
          // Don't show error toast for public endpoint - just log to console
          reject(error);
        },
      });
    });
  },

  /**
   * Create or update a rating
   * @param {Object} ratingData - { album_id, rating, user_id (optional) }
   * @returns {Promise}
   */
  rate: function (ratingData) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("Please login first");
      window.location.replace("#login");
      return;
    }

    // If no user_id provided, get it from token
    if (!ratingData.user_id) {
      try {
        const payload = Utils.parseJwt(token);
        ratingData.user_id = payload.user?.user_id || payload.user_id;
      } catch (e) {
        toastr.error("Invalid token");
        return;
      }
    }

    // Prevent duplicate submissions
    if (this._rateInProgress) {
      console.log("Rate album already in progress");
      return;
    }
    this._rateInProgress = true;

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "ratings",
        type: "POST",
        data: JSON.stringify(ratingData),
        contentType: "application/json",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          RatingService._rateInProgress = false;
          toastr.success("Rating saved successfully!");
          resolve(result);
        },
        error: function (xhr, status, error) {
          RatingService._rateInProgress = false;
          console.error("Rate album error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            // Format multiple errors into a single message
            let errorMsg = "Failed to save rating";
            if (errorResponse.errors && Array.isArray(errorResponse.errors)) {
              errorMsg = errorResponse.errors.join(". ");
            } else if (errorResponse.error) {
              errorMsg = errorResponse.error;
            }
            reject(new Error(errorMsg));
          } catch (e) {
            reject(new Error("Failed to save rating"));
          }
        },
      });
    });
  },

  /**
   * Update a rating
   * @param {number} id - Rating ID
   * @param {Object} ratingData - { rating }
   * @returns {Promise}
   */
  update: function (id, ratingData) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("Please login first");
      window.location.replace("#login");
      return;
    }

    // Prevent duplicate submissions
    if (this._updateInProgress) {
      console.log("Update rating already in progress");
      return;
    }
    this._updateInProgress = true;

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "ratings/" + id,
        type: "PUT",
        data: JSON.stringify(ratingData),
        contentType: "application/json",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          RatingService._updateInProgress = false;
          toastr.success("Rating updated successfully!");
          resolve(result);
        },
        error: function (xhr, status, error) {
          RatingService._updateInProgress = false;
          console.error("Update rating error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to update rating");
          } catch (e) {
            toastr.error("Failed to update rating");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Delete a rating
   * @param {number} id - Rating ID
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
        url: Constants.PROJECT_BASE_URL + "ratings/" + id,
        type: "DELETE",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          toastr.success("Rating removed successfully!");
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Delete rating error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to remove rating");
          } catch (e) {
            toastr.error("Failed to remove rating");
          }
          reject(error);
        },
      });
    });
  },
};
