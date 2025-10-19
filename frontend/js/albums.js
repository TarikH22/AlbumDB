/**
 * Album-related functionality
 * Handles album display, ratings, reviews, and search
 */

/**
 * Load home page
 */
function loadHomePage() {
    setTimeout(() => {
        // Load featured albums
        const featuredAlbums = AppState.albums.slice(0, 4);
        const featuredHTML = featuredAlbums.map(album => generateAlbumCard(album)).join('');
        $('#featured-albums-container').html(featuredHTML);

        // Load top rated albums
        const topRated = [...AppState.albums].sort((a, b) => b.rating - a.rating).slice(0, 4);
        const topRatedHTML = topRated.map(album => generateAlbumCard(album)).join('');
        $('#top-rated-albums-container').html(topRatedHTML);

        // Load recent reviews
        loadRecentReviews();
    }, 100);
}

/**
 * Load recent reviews for home page
 */
function loadRecentReviews() {
    const recentReviews = AppState.reviews.slice(0, 3);
    const reviewsHTML = recentReviews.map(review => {
        const album = getAlbumById(review.albumId);
        return `
            <div class="col-md-4">
                <div class="card review-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="${album.cover}" alt="${album.title}"
                                 class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;"
                                 onerror="this.src='https://via.placeholder.com/60x60'">
                            <div>
                                <h6 class="mb-0">${album.title}</h6>
                                <small class="text-muted">${album.artist}</small>
                            </div>
                        </div>
                        <div class="review-header">
                            <span class="review-author">${review.username}</span>
                            <span class="review-rating">${review.rating}/10</span>
                        </div>
                        <h6 class="mt-2">${review.title}</h6>
                        <p class="text-muted small">${review.text.substring(0, 100)}...</p>
                        <a href="#album/${album.id}" class="btn btn-sm btn-outline-primary">Read More</a>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    $('#recent-reviews-container').html(reviewsHTML);
}

/**
 * Load album details page
 */
function loadAlbumDetailsPage(params) {
    setTimeout(() => {
        const albumId = params.id;
        const album = getAlbumById(albumId);

        if (!album) {
            $('#app-content').html('<div class="container mt-5"><h2>Album not found</h2></div>');
            return;
        }

        // Populate album details
        $('#album-cover').attr('src', album.cover);
        $('#album-title').text(album.title);
        $('#album-artist').text(album.artist);
        $('#album-genre').text(album.genre);
        $('#album-year').text(album.year);
        $('#album-label').text(album.label);
        $('#album-avg-rating').text(album.rating);
        $('#album-rating-count').text(album.ratingCount);
        $('#album-stars').html(generateStars(album.rating));
        $('#album-description').text(album.description);

        // Load track list
        const tracksHTML = album.tracks.map(track => `
            <div class="list-group-item track-list-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="track-number">${track.number}</span>
                        <span class="track-title">${track.title}</span>
                    </div>
                    <span class="text-muted">${track.duration}</span>
                </div>
            </div>
        `).join('');
        $('#track-list').html(tracksHTML);

        // Load reviews
        loadAlbumReviews(albumId);

        // Set up rating form
        $('#rating-form').on('submit', function(e) {
            e.preventDefault();
            handleRating(albumId);
        });

        // Set up review form
        $('#review-form').on('submit', function(e) {
            e.preventDefault();
            handleReview(albumId);
        });
    }, 100);
}

/**
 * Load reviews for an album
 */
function loadAlbumReviews(albumId) {
    const reviews = getAlbumReviews(albumId);

    if (reviews.length === 0) {
        $('#reviews-list').html('<p class="text-muted">No reviews yet. Be the first to review this album!</p>');
        return;
    }

    const reviewsHTML = reviews.map(review => `
        <div class="card review-card mb-3">
            <div class="card-body">
                <div class="review-header">
                    <div>
                        <span class="review-author">${review.username}</span>
                        <span class="review-date ms-2">${review.date}</span>
                    </div>
                    <span class="review-rating">${review.rating}/10</span>
                </div>
                <h5 class="mt-2">${review.title}</h5>
                <p>${review.text}</p>
            </div>
        </div>
    `).join('');

    $('#reviews-list').html(reviewsHTML);
}

/**
 * Handle rating submission
 */
function handleRating(albumId) {
    if (!AppState.currentUser) {
        showNotification('Please login to rate albums', 'warning');
        SPARouter.navigate('login');
        return;
    }

    const rating = $('input[name="rating"]:checked').val();

    if (!rating) {
        showNotification('Please select a rating', 'warning');
        return;
    }

    // Store rating (will be replaced with API call)
    AppState.userRatings[albumId] = parseInt(rating);

    // Close modal
    $('#rateModal').modal('hide');

    // Show success message
    showNotification('Rating submitted successfully!', 'success');

    // Update album rating (simulated)
    const album = getAlbumById(albumId);
    album.ratingCount++;
}

/**
 * Handle review submission
 */
function handleReview(albumId) {
    if (!AppState.currentUser) {
        showNotification('Please login to write reviews', 'warning');
        SPARouter.navigate('login');
        return;
    }

    const title = $('#review-title').val();
    const text = $('#review-text').val();

    if (!title || !text) {
        showNotification('Please fill in all fields', 'warning');
        return;
    }

    // Create review
    const review = {
        id: Date.now(),
        albumId: parseInt(albumId),
        username: AppState.currentUser.username,
        rating: AppState.userRatings[albumId] || 0,
        title: title,
        text: text,
        date: new Date().toISOString().split('T')[0]
    };

    // Add to reviews
    AppState.reviews.unshift(review);

    // Close modal
    $('#reviewModal').modal('hide');

    // Show success message
    showNotification('Review posted successfully!', 'success');

    // Reload reviews
    loadAlbumReviews(albumId);

    // Clear form
    $('#review-title').val('');
    $('#review-text').val('');
}

/**
 * Load search page
 */
function loadSearchPage(params) {
    setTimeout(() => {
        let filteredAlbums = [...AppState.albums];
        const query = params.query || '';

        // Set search input
        if (query) {
            $('#search-input').val(query);
            // Filter albums
            filteredAlbums = filteredAlbums.filter(album =>
                album.title.toLowerCase().includes(query.toLowerCase()) ||
                album.artist.toLowerCase().includes(query.toLowerCase())
            );
        }

        // Display results
        displaySearchResults(filteredAlbums);

        // Set up search button
        $('#search-btn').on('click', function() {
            const searchQuery = $('#search-input').val();
            if (searchQuery) {
                SPARouter.navigate('search', { query: searchQuery });
            }
        });

        // Set up filters
        $('#apply-filters-btn').on('click', applySearchFilters);
        $('#clear-filters-btn').on('click', clearSearchFilters);

        // Set up sort
        $('#sort-select').on('change', function() {
            const sortBy = $(this).val();
            sortSearchResults(sortBy);
        });
    }, 100);
}

/**
 * Display search results
 */
function displaySearchResults(albums) {
    if (albums.length === 0) {
        $('#search-results-container').html('');
        $('#no-results').removeClass('d-none');
        $('#results-count').text('Showing 0 results');
        return;
    }

    $('#no-results').addClass('d-none');
    const resultsHTML = albums.map(album => generateAlbumCard(album)).join('');
    $('#search-results-container').html(resultsHTML);
    $('#results-count').text(`Showing ${albums.length} results`);
}

/**
 * Apply search filters
 */
function applySearchFilters() {
    const genre = $('#filter-genre').val();
    const year = $('#filter-year').val();
    const minRating = parseInt($('#filter-rating').val()) || 0;

    let filtered = [...AppState.albums];

    if (genre) {
        filtered = filtered.filter(album => album.genre.toLowerCase() === genre.toLowerCase());
    }

    if (year) {
        if (year.includes('s')) {
            // Decade filter
            const decade = parseInt(year);
            filtered = filtered.filter(album => Math.floor(album.year / 10) * 10 === decade);
        } else {
            // Specific year
            filtered = filtered.filter(album => album.year == year);
        }
    }

    if (minRating > 0) {
        filtered = filtered.filter(album => album.rating >= minRating);
    }

    displaySearchResults(filtered);
}

/**
 * Clear search filters
 */
function clearSearchFilters() {
    $('#filter-genre').val('');
    $('#filter-year').val('');
    $('#filter-rating').val('');
    displaySearchResults(AppState.albums);
}

/**
 * Sort search results
 */
function sortSearchResults(sortBy) {
    let sorted = [...AppState.albums];

    switch (sortBy) {
        case 'rating-desc':
            sorted.sort((a, b) => b.rating - a.rating);
            break;
        case 'rating-asc':
            sorted.sort((a, b) => a.rating - b.rating);
            break;
        case 'year-desc':
            sorted.sort((a, b) => b.year - a.year);
            break;
        case 'year-asc':
            sorted.sort((a, b) => a.year - b.year);
            break;
        case 'title-asc':
            sorted.sort((a, b) => a.title.localeCompare(b.title));
            break;
        case 'title-desc':
            sorted.sort((a, b) => b.title.localeCompare(a.title));
            break;
    }

    displaySearchResults(sorted);
}

/**
 * Load top rated page
 */
function loadTopRatedPage() {
    setTimeout(() => {
        // Get sorted albums
        const topAlbums = [...AppState.albums].sort((a, b) => b.rating - a.rating);

        // Generate HTML
        const albumsHTML = topAlbums.map((album, index) => {
            const rank = index + 1;
            const rankClass = rank <= 3 ? `rank-${rank}` : '';

            return `
                <div class="top-album-item">
                    <div class="rank-number ${rankClass}">#${rank}</div>
                    <img src="${album.cover}" alt="${album.title}" class="top-album-cover"
                         onerror="this.src='https://via.placeholder.com/100x100'">
                    <div class="top-album-info">
                        <h3 class="top-album-title">${album.title}</h3>
                        <p class="top-album-artist">${album.artist}</p>
                        <div>
                            <span class="badge bg-primary me-2">${album.genre}</span>
                            <span class="badge bg-secondary">${album.year}</span>
                        </div>
                    </div>
                    <div class="top-album-rating">
                        ${album.rating}<span class="fs-5 text-muted">/10</span>
                        <div class="text-muted small">${album.ratingCount} ratings</div>
                    </div>
                    <a href="#album/${album.id}" class="btn btn-primary">View Details</a>
                </div>
            `;
        }).join('');

        $('#top-albums-list').html(albumsHTML);

        // Set up filters
        $('#timeframe-select, #genre-select, #min-ratings').on('change', filterTopRated);
    }, 100);
}

/**
 * Filter top rated albums
 */
function filterTopRated() {
    // This would filter based on the selected options
    // For now, just reload the page
    loadTopRatedPage();
}

/**
 * Load profile page
 */
function loadProfilePage() {
    if (!AppState.currentUser) {
        showNotification('Please login to view your profile', 'warning');
        SPARouter.navigate('login');
        return;
    }

    setTimeout(() => {
        const user = AppState.currentUser;

        // Populate user info
        $('#profile-avatar').attr('src', user.avatar);
        $('#profile-username').text(user.username);
        $('#profile-fullname').text(`${user.firstName} ${user.lastName}`);
        $('#profile-email span').text(user.email);

        // Calculate stats
        const userRatings = Object.keys(AppState.userRatings).length;
        const userReviews = AppState.reviews.filter(r => r.username === user.username).length;
        const avgRating = userRatings > 0
            ? (Object.values(AppState.userRatings).reduce((a, b) => a + b, 0) / userRatings).toFixed(1)
            : '0.0';

        $('#stat-ratings').text(userRatings);
        $('#stat-reviews').text(userReviews);
        $('#stat-favorites').text(AppState.userFavorites.length);
        $('#stat-avg-rating').text(avgRating);

        // Load user ratings
        loadUserRatings();

        // Load user reviews
        loadUserReviews();

        // Set up edit profile form
        $('#edit-profile-form').on('submit', function(e) {
            e.preventDefault();
            handleEditProfile();
        });
    }, 100);
}

/**
 * Load user ratings
 */
function loadUserRatings() {
    const ratings = Object.entries(AppState.userRatings);

    if (ratings.length === 0) {
        $('#user-ratings-list').html('<p class="text-muted">You haven\'t rated any albums yet.</p>');
        return;
    }

    const ratingsHTML = ratings.map(([albumId, rating]) => {
        const album = getAlbumById(albumId);
        return `
            <div class="col-md-3 col-sm-6 mb-3">
                <a href="#album/${album.id}" class="text-decoration-none">
                    <div class="card h-100">
                        <img src="${album.cover}" class="card-img-top" alt="${album.title}"
                             onerror="this.src='https://via.placeholder.com/250x250'">
                        <div class="card-body">
                            <h6>${album.title}</h6>
                            <p class="text-muted small mb-2">${album.artist}</p>
                            <span class="badge bg-warning text-dark">Your rating: ${rating}/10</span>
                        </div>
                    </div>
                </a>
            </div>
        `;
    }).join('');

    $('#user-ratings-list').html(ratingsHTML);
}

/**
 * Load user reviews
 */
function loadUserReviews() {
    const reviews = AppState.reviews.filter(r => r.username === AppState.currentUser.username);

    if (reviews.length === 0) {
        $('#user-reviews-list').html('<p class="text-muted">You haven\'t written any reviews yet.</p>');
        return;
    }

    const reviewsHTML = reviews.map(review => {
        const album = getAlbumById(review.albumId);
        return `
            <div class="card review-card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="${album.cover}" alt="${album.title}"
                             class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;"
                             onerror="this.src='https://via.placeholder.com/60x60'">
                        <div>
                            <h6 class="mb-0">${album.title}</h6>
                            <small class="text-muted">${album.artist}</small>
                        </div>
                    </div>
                    <div class="review-header">
                        <span class="review-date">${review.date}</span>
                        <span class="review-rating">${review.rating}/10</span>
                    </div>
                    <h5 class="mt-2">${review.title}</h5>
                    <p>${review.text}</p>
                </div>
            </div>
        `;
    }).join('');

    $('#user-reviews-list').html(reviewsHTML);
}

/**
 * Handle edit profile
 */
function handleEditProfile() {
    const firstName = $('#edit-firstname').val();
    const lastName = $('#edit-lastname').val();
    const username = $('#edit-username').val();
    const email = $('#edit-email').val();

    // Update user
    AppState.currentUser.firstName = firstName;
    AppState.currentUser.lastName = lastName;
    AppState.currentUser.username = username;
    AppState.currentUser.email = email;

    // Update localStorage
    localStorage.setItem('albumrate_user', JSON.stringify(AppState.currentUser));

    // Close modal
    $('#editProfileModal').modal('hide');

    // Show success message
    showNotification('Profile updated successfully!', 'success');

    // Reload profile
    loadProfilePage();
}
