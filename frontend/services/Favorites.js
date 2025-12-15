var FavoriteService = {
  /**
   * Get user's favorite albums
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
        url: Constants.PROJECT_BASE_URL + "favorites/user/" + userId,
        type: "GET",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Get user favorites error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to fetch favorites");
          } catch (e) {
            toastr.error("Failed to fetch favorites");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Get favorites count for an album
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
        url: Constants.PROJECT_BASE_URL + "favorites/album/" + albumId,
        type: "GET",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Get album favorites error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to fetch album favorites");
          } catch (e) {
            toastr.error("Failed to fetch album favorites");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Get most favorited albums
   * @param {number} limit - Number of results
   * @returns {Promise}
   */
  getMostFavorited: function (limit = 10) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("Please login first");
      window.location.replace("#login");
      return;
    }

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "favorites/most-favorited?limit=" + limit,
        type: "GET",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Get most favorited albums error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to fetch most favorited albums");
          } catch (e) {
            toastr.error("Failed to fetch most favorited albums");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Check if album is in user's favorites
   * @param {number} albumId - Album ID
   * @param {number} userId - User ID (optional, uses current user if not provided)
   * @returns {Promise<boolean>}
   */
  isFavorite: function (albumId, userId = null) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      return Promise.resolve({ data: { is_favorite: false } });
    }

    // If no userId provided, get it from token
    if (!userId) {
      try {
        const payload = Utils.parseJwt(token);
        userId = payload.user?.user_id || payload.user_id;
      } catch (e) {
        return Promise.resolve({ data: { is_favorite: false } });
      }
    }

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "favorites/user/" + userId + "/album/" + albumId,
        type: "GET",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Check favorite error:", xhr.responseText);
          resolve({ data: { is_favorite: false } });
        },
      });
    });
  },

  /**
   * Add album to favorites
   * @param {number} albumId - Album ID
   * @param {number} userId - User ID (optional, uses current user if not provided)
   * @returns {Promise}
   */
  add: function (albumId, userId = null) {
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

    // Prevent duplicate submissions
    if (this._addInProgress) {
      console.log("Add favorite already in progress");
      return;
    }
    this._addInProgress = true;

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "favorites",
        type: "POST",
        data: JSON.stringify({ user_id: userId, album_id: albumId }),
        contentType: "application/json",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          FavoriteService._addInProgress = false;
          toastr.success("Added to favorites!");
          resolve(result);
        },
        error: function (xhr, status, error) {
          FavoriteService._addInProgress = false;
          console.error("Add favorite error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to add to favorites");
          } catch (e) {
            toastr.error("Failed to add to favorites");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Remove album from favorites
   * @param {number} albumId - Album ID
   * @param {number} userId - User ID (optional, uses current user if not provided)
   * @returns {Promise}
   */
  remove: function (albumId, userId = null) {
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
        url: Constants.PROJECT_BASE_URL + "favorites/user/" + userId + "/album/" + albumId,
        type: "DELETE",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          toastr.success("Removed from favorites!");
          resolve(result);
        },
        error: function (xhr, status, error) {
          console.error("Remove favorite error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            toastr.error(errorResponse.error || "Failed to remove from favorites");
          } catch (e) {
            toastr.error("Failed to remove from favorites");
          }
          reject(error);
        },
      });
    });
  },

  /**
   * Toggle favorite status
   * @param {number} albumId - Album ID
   * @param {number} userId - User ID (optional, uses current user if not provided)
   * @returns {Promise}
   */
  toggle: function (albumId, userId = null) {
    const token = localStorage.getItem("user_token");
    if (!token) {
      toastr.error("Please login first");
      window.location.replace("#login");
      return Promise.reject(new Error("Not authenticated"));
    }

    // If no userId provided, get it from token
    if (!userId) {
      try {
        const payload = Utils.parseJwt(token);
        userId = payload.user?.user_id || payload.user_id;
      } catch (e) {
        toastr.error("Invalid token");
        return Promise.reject(new Error("Invalid token"));
      }
    }

    // Prevent duplicate submissions
    if (this._toggleInProgress) {
      console.log("Toggle favorite already in progress");
      return Promise.reject(new Error("Toggle in progress"));
    }
    this._toggleInProgress = true;

    return new Promise((resolve, reject) => {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "favorites/toggle",
        type: "POST",
        data: JSON.stringify({ user_id: userId, album_id: albumId }),
        contentType: "application/json",
        headers: {
          Authorization: "Bearer " + token,
        },
        success: function (result) {
          FavoriteService._toggleInProgress = false;
          resolve(result);
        },
        error: function (xhr, status, error) {
          FavoriteService._toggleInProgress = false;
          console.error("Toggle favorite error:", xhr.responseText);
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            // Format multiple errors into a single message
            let errorMsg = "Failed to toggle favorite";
            if (errorResponse.errors && Array.isArray(errorResponse.errors)) {
              errorMsg = errorResponse.errors.join(". ");
            } else if (errorResponse.error) {
              errorMsg = errorResponse.error;
            }
            reject(new Error(errorMsg));
          } catch (e) {
            reject(new Error("Failed to toggle favorite"));
          }
        },
      });
    });
  },
};
