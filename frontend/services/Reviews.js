var ReviewService = {
  /**
   * Get review by ID
   * @param {number} id - Review ID
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
        url: Constants.PROJECT_BASE_URL + "reviews/" + id,
        type: "GET",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Get review error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to fetch review");
          } catch (e) {
            toastr.error("Failed to fetch review");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Get reviews by user
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
        url: Constants.PROJECT_BASE_URL + "reviews/user/" + userId,
        type: "GET",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Get user reviews error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to fetch user reviews");
          } catch (e) {
            toastr.error("Failed to fetch user reviews");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Get reviews for an album
   * @param {number} albumId - Album ID
   * @returns {Promise}
   */
  getByAlbum: function (albumId) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("Please login first");
      window.location.replace("#login");
      return;
    }

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "reviews/album/" + albumId,
        type: "GET",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Get album reviews error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to fetch album reviews");
          } catch (e) {
            toastr.error("Failed to fetch album reviews");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Create a new review
   * @param {Object} reviewData - { album_id, title, review_text, user_id (optional) }
   * @returns {Promise}
   */
  create: function (reviewData) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("Please login first");
      window.location.replace("#login");
      return;
    }

    // If no user_id provided, get it from token
    if (!reviewData.user_id) {
      try {
        const payload = Utils.parseJwt(token);
        console.log("JWT Payload:", payload);
        reviewData.user_id = payload.user?.user_id || payload.user_id;
        console.log("Extracted user_id:", reviewData.user_id);
      } catch (e) {
        console.error("JWT parse error:", e);
        toastr.error("Invalid token");
        return;
      }
    }

    console.log("Review data being sent:", reviewData);

    // Prevent duplicate submissions
    if (this._createInProgress) {
      console.log("Create review already in progress");
      return;
    }
    this._createInProgress = true;

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "reviews",
        type: "POST",
        data: JSON.stringify(reviewData),
        contentType: "application/json",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          ReviewService._createInProgress = false;
          toastr.success("Review posted successfully!");
          resolve(result);
        },
        error: function (xhr, status, error) {
          ReviewService._createInProgress = false;
          console.error("Create review error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            // Format multiple errors into a single message
            let errorMsg = "Failed to create review";
            if (errorResponse.errors && Array.isArray(errorResponse.errors)) {
              errorMsg = errorResponse.errors.join(". ");
            } else if (errorResponse.error) {
              errorMsg = errorResponse.error;
            }
            reject(new Error(errorMsg));
          } catch (e) {
            reject(new Error("Failed to create review"));
          }
        },
      });
    });
  },

  /**
   * Update a review
   * @param {number} id - Review ID
   * @param {Object} reviewData - Review fields to update
   * @returns {Promise}
   */
  update: function (id, reviewData) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("Please login first");
      window.location.replace("#login");
      return;
    }

    // Prevent duplicate submissions
    if (this._updateInProgress) {
      console.log("Update review already in progress");
      return;
    }
    this._updateInProgress = true;

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "reviews/" + id,
        type: "PUT",
        data: JSON.stringify(reviewData),
        contentType: "application/json",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          ReviewService._updateInProgress = false;
          toastr.success("Review updated successfully!");
          resolve(result);
        },
        error: function (xhr, status, error) {
          ReviewService._updateInProgress = false;
          console.error("Update review error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to update review");
          } catch (e) {
            toastr.error("Failed to update review");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Delete a review
   * @param {number} id - Review ID
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
        url: Constants.PROJECT_BASE_URL + "reviews/" + id,
        type: "DELETE",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          toastr.success("Review deleted successfully!");
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Delete review error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to delete review");
          } catch (e) {
            toastr.error("Failed to delete review");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Get recent reviews
   * @param {number} limit - Number of reviews to fetch
   * @returns {Promise}
   */
  getRecent: function (limit = 10) {
    const token = localStorage.getItem("user_token");

    const headers = {};
    if (token) {
      headers.Authorization = "Bearer " + token;
    }

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "reviews/recent?limit=" + limit,
        type: "GET",
        headers: headers,
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Get recent reviews error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to fetch recent reviews");
          } catch (e) {
            toastr.error("Failed to fetch recent reviews");
          }
          reject(error);
        },
      });
    });
  },
};
