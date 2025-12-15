/**
 * Admin Panel Functionality
 * Handles all admin-related operations
 */

/**
 * Load admin dashboard with stats
 */
function loadAdminPage() {
  console.log('loadAdminPage called');
  const token = localStorage.getItem('user_token');
  console.log('Token exists:', !!token);
  
  if (token) {
    const decoded = Utils.parseJwt(token);
    console.log('Decoded token:', decoded);
    console.log('Roles from token:', decoded.roles, decoded.user?.roles, decoded.user?.role);
  }
  
  console.log('AlbumService.isAdmin():', AlbumService.isAdmin());
  
  // Check if user is admin
  if (!AlbumService.isAdmin()) {
    toastr.error("Access denied. Admin only.");
    window.location.hash = "home";
    return;
  }

  setTimeout(() => {
    // Load admin stats
    loadAdminStats();

    // Load albums table
    loadAdminAlbums();

    // Load users table
    loadAdminUsers();

    // Populate album dropdowns
    populateAlbumDropdowns();

    // Set up modal event handlers
    setupAdminEventHandlers();

    // Set up album filter for tracks
    $("#track-album-filter").on("change", function () {
      const albumId = $(this).val();
      if (albumId) {
        loadAdminTracks(albumId);
      }
    });
  }, 100);
}

/**
 * Load admin statistics
 */
function loadAdminStats() {
  // Get total albums
  AlbumService.getAll()
    .then((response) => {
      const albums = Array.isArray(response) ? response : 
                    (response.data?.albums || response.data || response.albums || []);
      $("#admin-total-albums").text(albums.length || 0);
    })
    .catch((error) => {
      console.error("Error loading albums:", error);
    });

  // Get stats from backend
  UserService.getStats()
    .then((stats) => {
      $("#admin-total-users").text(stats.total_users || 0);
      $("#admin-total-ratings").text(stats.total_ratings || 0);
      $("#admin-total-reviews").text(stats.total_reviews || 0);
    })
    .catch((error) => {
      console.error("Error loading stats:", error);
      $("#admin-total-users").text("N/A");
      $("#admin-total-ratings").text("N/A");
      $("#admin-total-reviews").text("N/A");
    });
}

/**
 * Load albums table
 */
function loadAdminAlbums() {
  AlbumService.getAll()
    .then((response) => {
      const albums = Array.isArray(response) ? response : 
                    (response.data?.albums || response.data || response.albums || []);
      const tbody = $("#admin-albums-list");
      tbody.empty();

      if (albums.length === 0) {
        tbody.html('<tr><td colspan="7" class="text-center">No albums found</td></tr>');
        return;
      }

      albums.forEach((album) => {
        const row = `
                    <tr>
                        <td>${album.album_id}</td>
                        <td><img src="${album.cover_image_url || "assets/images/albums/default.jpg"}" alt="${album.title}" style="width: 50px; height: 50px; object-fit: cover;"></td>
                        <td>${album.title}</td>
                        <td>${album.artist}</td>
                        <td>${album.genre}</td>
                        <td>${album.year}</td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-album-btn" data-id="${album.album_id}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-album-btn" data-id="${album.album_id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
        tbody.append(row);
      });
    })
    .catch((error) => {
      console.error("Error loading albums:", error);
      $("#admin-albums-list").html('<tr><td colspan="7" class="text-center text-danger">Error loading albums</td></tr>');
    });
}

/**
 * Load tracks for specific album
 */
function loadAdminTracks(albumId) {
  TrackService.getByAlbum(albumId)
    .then((tracks) => {
      const tbody = $("#admin-tracks-list");
      tbody.empty();

      if (tracks.length === 0) {
        tbody.html('<tr><td colspan="6" class="text-center">No tracks found for this album</td></tr>');
        return;
      }

      tracks.forEach((track) => {
        const row = `
                    <tr>
                        <td>${track.track_id}</td>
                        <td>${track.track_number}</td>
                        <td>${track.title}</td>
                        <td>${track.album_title || "N/A"}</td>
                        <td>${track.duration}</td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-track-btn" data-id="${track.track_id}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-track-btn" data-id="${track.track_id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
        tbody.append(row);
      });
    })
    .catch((error) => {
      console.error("Error loading tracks:", error);
      $("#admin-tracks-list").html('<tr><td colspan="6" class="text-center text-danger">Error loading tracks</td></tr>');
    });
}

/**
 * Load users table
 */
function loadAdminUsers() {
  UserService.getAll()
    .then((users) => {
      const tbody = $("#admin-users-list");
      tbody.empty();

      if (users.length === 0) {
        tbody.html('<tr><td colspan="6" class="text-center">No users found</td></tr>');
        return;
      }

      users.forEach((user) => {
        const fullName = (user.first_name || '') + ' ' + (user.last_name || '');
        const status = user.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
        const row = `
                    <tr>
                        <td>${user.user_id}</td>
                        <td>${user.username}</td>
                        <td>${user.email}</td>
                        <td>${fullName.trim() || 'N/A'}</td>
                        <td>${status}</td>
                        <td>
                            <button class="btn btn-sm btn-danger deactivate-user-btn" data-id="${user.user_id}" ${!user.is_active ? 'disabled' : ''}>
                                <i class="bi bi-x-circle"></i> Deactivate
                            </button>
                        </td>
                    </tr>
                `;
        tbody.append(row);
      });
    })
    .catch((error) => {
      console.error("Error loading users:", error);
      $("#admin-users-list").html('<tr><td colspan="6" class="text-center text-danger">Error loading users</td></tr>');
    });
}

/**
 * Populate album dropdowns in modals
 */
function populateAlbumDropdowns() {
  AlbumService.getAll().then((response) => {
    const albums = Array.isArray(response) ? response : 
                  (response.data?.albums || response.data || response.albums || []);
    const trackAlbumSelect = $("#track-album");
    const trackAlbumFilter = $("#track-album-filter");

    trackAlbumSelect.empty().append('<option value="">Select Album</option>');
    trackAlbumFilter.empty().append('<option value="">Select an album to view tracks</option>');

    albums.forEach((album) => {
      trackAlbumSelect.append(`<option value="${album.album_id}">${album.title} - ${album.artist}</option>`);
      trackAlbumFilter.append(`<option value="${album.album_id}">${album.title} - ${album.artist}</option>`);
    });

    // Auto-select first album if available
    if (albums.length > 0) {
      trackAlbumFilter.val(albums[0].album_id);
      loadAdminTracks(albums[0].album_id);
    }
  });
}

/**
 * Set up event handlers for admin forms
 */
function setupAdminEventHandlers() {
  // Save album button
  $("#save-album-btn").off("click").on("click", function () {
    const form = $("#add-album-form")[0];
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const albumData = {
      title: $("#album-title").val(),
      artist: $("#album-artist").val(),
      genre: $("#album-genre").val(),
      year: parseInt($("#album-year").val()),
      cover_image_url: $("#album-cover").val() || null,
      description: $("#album-description").val() || null,
    };

    AlbumService.create(albumData).then(() => {
      $("#addAlbumModal").modal("hide");
      $("#add-album-form")[0].reset();
      loadAdminAlbums();
      populateAlbumDropdowns();
    });
  });

  // Edit album button
  $(document).on("click", ".edit-album-btn", function () {
    const albumId = $(this).data("id");
    AlbumService.getById(albumId).then((album) => {
      $("#edit-album-id").val(album.album_id);
      $("#edit-album-title").val(album.title);
      $("#edit-album-artist").val(album.artist);
      $("#edit-album-genre").val(album.genre);
      $("#edit-album-year").val(album.year);
      $("#edit-album-cover").val(album.cover_image_url);
      $("#edit-album-description").val(album.description);
      $("#editAlbumModal").modal("show");
    });
  });

  // Update album button
  $("#update-album-btn").off("click").on("click", function () {
    const form = $("#edit-album-form")[0];
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const albumId = $("#edit-album-id").val();
    const albumData = {
      title: $("#edit-album-title").val(),
      artist: $("#edit-album-artist").val(),
      genre: $("#edit-album-genre").val(),
      year: parseInt($("#edit-album-year").val()),
      cover_image_url: $("#edit-album-cover").val() || null,
      description: $("#edit-album-description").val() || null,
    };

    AlbumService.update(albumId, albumData).then(() => {
      $("#editAlbumModal").modal("hide");
      loadAdminAlbums();
      populateAlbumDropdowns();
    });
  });

  // Delete album button
  $(document).on("click", ".delete-album-btn", function () {
    const albumId = $(this).data("id");
    if (confirm("Are you sure you want to delete this album? This will also delete all associated tracks, ratings, and reviews.")) {
      AlbumService.delete(albumId).then(() => {
        loadAdminAlbums();
        populateAlbumDropdowns();
      });
    }
  });

  // Save track button
  $("#save-track-btn").off("click").on("click", function () {
    const form = $("#add-track-form")[0];
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const trackData = {
      album_id: parseInt($("#track-album").val()),
      track_number: parseInt($("#track-number").val()),
      title: $("#track-title").val(),
      duration: $("#track-duration").val(),
    };

    TrackService.create(trackData).then(() => {
      $("#addTrackModal").modal("hide");
      $("#add-track-form")[0].reset();
      const selectedAlbum = $("#track-album-filter").val();
      if (selectedAlbum) {
        loadAdminTracks(selectedAlbum);
      }
    });
  });

  // Edit track button
  $(document).on("click", ".edit-track-btn", function () {
    const trackId = $(this).data("id");
    TrackService.getById(trackId).then((track) => {
      $("#edit-track-id").val(track.track_id);
      $("#edit-track-number").val(track.track_number);
      $("#edit-track-title").val(track.title);
      $("#edit-track-duration").val(track.duration);
      $("#editTrackModal").modal("show");
    });
  });

  // Update track button
  $("#update-track-btn").off("click").on("click", function () {
    const form = $("#edit-track-form")[0];
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const trackId = $("#edit-track-id").val();
    const trackData = {
      track_number: parseInt($("#edit-track-number").val()),
      title: $("#edit-track-title").val(),
      duration: $("#edit-track-duration").val(),
    };

    TrackService.update(trackId, trackData).then(() => {
      $("#editTrackModal").modal("hide");
      const selectedAlbum = $("#track-album-filter").val();
      if (selectedAlbum) {
        loadAdminTracks(selectedAlbum);
      }
    });
  });

  // Delete track button
  $(document).on("click", ".delete-track-btn", function () {
    const trackId = $(this).data("id");
    if (confirm("Are you sure you want to delete this track?")) {
      TrackService.delete(trackId).then(() => {
        const selectedAlbum = $("#track-album-filter").val();
        if (selectedAlbum) {
          loadAdminTracks(selectedAlbum);
        }
      });
    }
  });

  // Deactivate user button
  $(document).on("click", ".deactivate-user-btn", function () {
    const userId = $(this).data("id");
    if (confirm("Are you sure you want to deactivate this user?")) {
      const token = localStorage.getItem("user_token");
      if (!token) {
        toastr.error("You must be logged in");
        return;
      }

      $.ajax({
        url: Constants.PROJECT_BASE_URL + "users/" + userId,
        type: "DELETE",
        beforeSend: function(xhr) {
          xhr.setRequestHeader("Authorization", "Bearer " + token);
        },
        contentType: "application/json",
        dataType: "json",
        success: function (response) {
          if (response.success) {
            toastr.success("User deactivated successfully");
            loadAdminUsers();
            loadAdminStats();
          } else {
            toastr.error(response.error || "Failed to deactivate user");
          }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          console.error("Error deactivating user:", XMLHttpRequest.responseText);
          toastr.error("Failed to deactivate user");
        },
      });
    }
  });
}

/**
 * Load dashboard page
 */
function loadDashboardPage() {
  if (!UserService.isAuthenticated()) {
    toastr.warning("Please login to view your dashboard");
    window.location.hash = "login";
    return;
  }

  setTimeout(() => {
    loadDashboardData();
  }, 100);
}

/**
 * Load dashboard data
 */
function loadDashboardData() {
  const token = localStorage.getItem("user_token");
  const payload = Utils.parseJwt(token);
  const username = payload.username || "User";

  $("#dashboard-username").text(username);

  // Load favorites
  FavoriteService.getByUser()
    .then((favorites) => {
      $("#dashboard-favorites-count").text(favorites.length);
      displayDashboardFavorites(favorites);
    })
    .catch((error) => {
      console.error("Error loading favorites:", error);
      $("#dashboard-favorites-count").text("0");
    });

  // Load ratings
  RatingService.getByUser()
    .then((ratings) => {
      $("#dashboard-ratings-count").text(ratings.length);

      if (ratings.length > 0) {
        const avgRating = ratings.reduce((sum, r) => sum + parseFloat(r.rating), 0) / ratings.length;
        $("#dashboard-avg-rating").text(avgRating.toFixed(1));
      } else {
        $("#dashboard-avg-rating").text("0.0");
      }

      displayDashboardRatings(ratings.slice(0, 5));
    })
    .catch((error) => {
      console.error("Error loading ratings:", error);
      $("#dashboard-ratings-count").text("0");
    });

  // Load reviews
  ReviewService.getByUser()
    .then((response) => {
      const reviews = Array.isArray(response) ? response : 
                     (response.data?.reviews || response.data || response.reviews || []);
      $("#dashboard-reviews-count").text(reviews.length);
      displayDashboardReviews(reviews.slice(0, 5));
    })
    .catch((error) => {
      console.error("Error loading reviews:", error);
      $("#dashboard-reviews-count").text("0");
    });

  // Load new albums count
  AlbumService.getAll({ limit: 10 })
    .then((response) => {
      const albums = Array.isArray(response) ? response : 
                    (response.data?.albums || response.data || response.albums || []);
      $("#dashboard-new-albums").text(albums.length);
    })
    .catch((error) => {
      console.error("Error loading albums:", error);
    });

  // Load recommendations (top rated albums)
  AlbumService.getTopRated(6)
    .then((response) => {
      const albums = Array.isArray(response) ? response : 
                    (response.data?.albums || response.data || response.albums || []);
      displayDashboardRecommendations(albums);
    })
    .catch((error) => {
      console.error("Error loading recommendations:", error);
    });
}

/**
 * Display favorites on dashboard
 */
function displayDashboardFavorites(favorites) {
  const container = $("#dashboard-favorites");
  container.empty();

  if (favorites.length === 0) {
    container.html('<p class="text-center text-muted">No favorite albums yet</p>');
    return;
  }

  favorites.slice(0, 6).forEach((album) => {
    const card = `
            <div class="col-md-4 col-lg-2 mb-3">
                <div class="card h-100">
                    <img src="${album.cover_image_url || "assets/images/albums/default.jpg"}" class="card-img-top" alt="${album.title}">
                    <div class="card-body p-2">
                        <h6 class="card-title small">${album.title}</h6>
                        <p class="card-text small text-muted">${album.artist}</p>
                        <a href="#album/${album.album_id}" class="btn btn-sm btn-primary w-100">View</a>
                    </div>
                </div>
            </div>
        `;
    container.append(card);
  });
}

/**
 * Display recent ratings on dashboard
 */
function displayDashboardRatings(ratings) {
  const container = $("#dashboard-recent-ratings");
  container.empty();

  if (ratings.length === 0) {
    container.html('<p class="text-center text-muted">No ratings yet</p>');
    return;
  }

  ratings.forEach((rating) => {
    const item = `
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <div>
                    <strong>${rating.album_title || "Album"}</strong><br>
                    <small class="text-muted">${rating.artist || ""}</small>
                </div>
                <div class="text-end">
                    <span class="badge bg-warning">${rating.rating}/10</span>
                </div>
            </div>
        `;
    container.append(item);
  });
}

/**
 * Display recent reviews on dashboard
 */
function displayDashboardReviews(reviews) {
  const container = $("#dashboard-recent-reviews");
  container.empty();

  if (reviews.length === 0) {
    container.html('<p class="text-center text-muted">No reviews yet</p>');
    return;
  }

  reviews.forEach((review) => {
    const item = `
            <div class="mb-3 pb-2 border-bottom">
                <strong>${review.title}</strong><br>
                <small class="text-muted">for ${review.album_title || "Album"}</small><br>
                <small>${review.review_text ? review.review_text.substring(0, 100) + "..." : ""}</small>
            </div>
        `;
    container.append(item);
  });
}

/**
 * Display recommendations on dashboard
 */
function displayDashboardRecommendations(albums) {
  const container = $("#dashboard-recommendations");
  container.empty();

  if (albums.length === 0) {
    container.html('<p class="text-center text-muted">No recommendations available</p>');
    return;
  }

  albums.forEach((album) => {
    const card = `
            <div class="col-md-4 col-lg-2 mb-3">
                <div class="card h-100">
                    <img src="${album.cover_image_url || "assets/images/albums/default.jpg"}" class="card-img-top" alt="${album.title}">
                    <div class="card-body p-2">
                        <h6 class="card-title small">${album.title}</h6>
                        <p class="card-text small text-muted">${album.artist}</p>
                        <div class="small mb-2">
                            <i class="bi bi-star-fill text-warning"></i> ${album.average_rating || "N/A"}
                        </div>
                        <a href="#album/${album.album_id}" class="btn btn-sm btn-primary w-100">View</a>
                    </div>
                </div>
            </div>
        `;
    container.append(card);
  });
}

/**
 * Load favorites page
 */
function loadFavoritesPage() {
  if (!UserService.isAuthenticated()) {
    toastr.warning("Please login to view your favorites");
    window.location.hash = "login";
    return;
  }

  setTimeout(() => {
    loadFavorites();
  }, 100);
}

/**
 * Load user's favorites
 */
function loadFavorites() {
  FavoriteService.getByUser()
    .then((favorites) => {
      const grid = $("#favorites-grid");
      const empty = $("#favorites-empty");
      const count = $("#favorites-count");

      count.text(favorites.length);

      if (favorites.length === 0) {
        grid.addClass("d-none");
        empty.removeClass("d-none");
        return;
      }

      grid.removeClass("d-none");
      empty.addClass("d-none");
      grid.empty();

      favorites.forEach((album) => {
        const card = `
                    <div class="col-md-3 col-lg-2 mb-4">
                        <div class="card h-100 album-card">
                            <img src="${album.cover_image_url || "assets/images/albums/default.jpg"}" class="card-img-top" alt="${album.title}">
                            <div class="card-body">
                                <h5 class="card-title">${album.title}</h5>
                                <p class="card-text text-muted">${album.artist}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">${album.year}</small>
                                    <span class="badge bg-primary">${album.genre}</span>
                                </div>
                                <div class="mt-2">
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <span>${album.average_rating || "N/A"}</span>
                                </div>
                                <div class="mt-2">
                                    <a href="#album/${album.album_id}" class="btn btn-sm btn-primary w-100 mb-1">View Details</a>
                                    <button class="btn btn-sm btn-danger w-100 remove-favorite-btn" data-id="${album.album_id}">
                                        <i class="bi bi-heart-fill"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
        grid.append(card);
      });

      // Set up remove favorite buttons
      $(".remove-favorite-btn").on("click", function () {
        const albumId = $(this).data("id");
        FavoriteService.remove(albumId).then(() => {
          loadFavorites();
        });
      });
    })
    .catch((error) => {
      console.error("Error loading favorites:", error);
      $("#favorites-grid").html('<div class="col-12"><div class="alert alert-danger">Error loading favorites</div></div>');
    });
}
