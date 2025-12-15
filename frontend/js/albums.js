/**
 * Album-related functionality
 * Handles album display, ratings, reviews, and search
 */

/**
 * Load home page
 */
function loadHomePage() {
    setTimeout(() => {
        // Load featured albums from backend
        AlbumService.getAll({ limit: 4 }).then((response) => {
            const albums = Array.isArray(response) ? response : 
                          (response.data?.albums || response.data || response.albums || []);
            const featuredHTML = albums.map(album => generateAlbumCard(album)).join('');
            $('#featured-albums-container').html(featuredHTML);
        }).catch(error => {
            console.error('Error loading featured albums:', error);
            $('#featured-albums-container').html('<p class="text-center text-muted">Unable to load albums</p>');
        });

        // Load top rated albums from backend
        AlbumService.getTopRated(4).then((response) => {
            const albums = Array.isArray(response) ? response : 
                          (response.data?.albums || response.data || response.albums || []);
            const topRatedHTML = albums.map(album => generateAlbumCard(album)).join('');
            $('#top-rated-albums-container').html(topRatedHTML);
        }).catch(error => {
            console.error('Error loading top rated albums:', error);
            $('#top-rated-albums-container').html('<p class="text-center text-muted">Unable to load top rated albums</p>');
        });

        // Load recent reviews only for logged-in users
        if (localStorage.getItem('user_token')) {
            loadRecentReviews();
            $('.recent-reviews').show();
        } else {
            $('.recent-reviews').hide();
        }
        
        // Wire up hero search
        $('#hero-search').off('keypress').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                const query = $(this).val().trim();
                if (query) {
                    window.location.hash = 'search?q=' + encodeURIComponent(query);
                }
            }
        });
        
        $('.hero-section .btn-primary').off('click').on('click', function() {
            const query = $('#hero-search').val().trim();
            if (query) {
                window.location.hash = 'search?q=' + encodeURIComponent(query);
            } else {
                window.location.hash = 'search';
            }
        });
    }, 100);
}

/**
 * Load recent reviews for home page
 */
function loadRecentReviews() {
    ReviewService.getRecent(3).then((response) => {
        console.log('Recent reviews response:', response);
        const reviews = Array.isArray(response) ? response : 
                       (response.data?.reviews || response.data || response.reviews || []);
        console.log('Extracted reviews:', reviews);
        if (reviews.length === 0) {
            $('#recent-reviews-container').html('<p class="text-center text-muted">No reviews yet. Be the first to write one!</p>');
            return;
        }
        
        const reviewsHTML = reviews.map(review => {
            const coverUrl = review.cover_url || review.cover_image_url || 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="60" height="60"%3E%3Crect fill="%23e9ecef" width="60" height="60"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" fill="%236c757d" font-size="10"%3ENo Image%3C/text%3E%3C/svg%3E';
            return `
                <div class="col-md-4">
                    <div class="card review-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="${coverUrl}" alt="${review.album_title || 'Album'}"
                                     class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;"
                                     onerror="if(this.src.indexOf('data:image')===-1){this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'60\' height=\'60\'%3E%3Crect fill=\'%23e9ecef\' width=\'60\' height=\'60\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%236c757d\' font-size=\'10\'%3ENo Image%3C/text%3E%3C/svg%3E';}this.onerror=null;">
                                <div>
                                    <h6 class="mb-0">${review.album_title || 'Unknown Album'}</h6>
                                    <small class="text-muted">${review.artist || 'Unknown Artist'}</small>
                                </div>
                            </div>
                            <div class="review-header">
                                <span class="review-author">${review.username || 'Anonymous'}</span>
                            </div>
                            <h6 class="mt-2">${review.title}</h6>
                            <p class="text-muted small">${review.review_text ? review.review_text.substring(0, 100) + '...' : ''}</p>
                            <a href="#album/${review.album_id}" class="btn btn-sm btn-outline-primary">Read More</a>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        $('#recent-reviews-container').html(reviewsHTML);
    }).catch(error => {
        console.error('Error loading recent reviews:', error);
        $('#recent-reviews-container').html('<p class="text-center text-muted">Unable to load reviews</p>');
    });
}

/**
 * Load album details page
 */
function loadAlbumDetailsPage(params) {
    setTimeout(() => {
        const albumId = params.id;
        const isAuthenticated = !!localStorage.getItem('user_token');

        // Load album details from API
        AlbumService.getById(albumId).then((response) => {
            const album = response.data || response;

            if (!album) {
                showNotification('Album not found', 'error');
                window.location.hash = 'home';
                return;
            }
            
            // Hide interactive elements for non-logged users
            if (!isAuthenticated) {
                $('#rating-section .card-body').html('<p class="text-center text-muted"><i class="bi bi-lock"></i> <a href="#login">Login</a> to rate this album</p>');
                $('#favorites-section').html('<div class="text-center"><button class="btn btn-outline-secondary" disabled><i class="bi bi-lock"></i> Login to add to favorites</button></div>');
                $('#review-form').html('<div class="alert alert-info text-center"><i class="bi bi-lock"></i> <a href="#login">Login</a> to write a review</div>');
            }

            // Populate album details
            let coverUrl = album.cover_url || album.cover_image_url || 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300"%3E%3Crect fill="%23e9ecef" width="300" height="300"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" fill="%236c757d" font-size="20"%3ENo Cover Image%3C/text%3E%3C/svg%3E';
            // Handle cover image URL - prepend base path if it's a relative URL starting with /covers/
            if (coverUrl && coverUrl.startsWith('/covers/')) {
                coverUrl = '/milestone5/backend' + coverUrl;
            }
            $('#album-cover').attr('src', coverUrl)
                .on('error', function() {
                    if ($(this).attr('src').indexOf('data:image') === -1) {
                        $(this).attr('src', 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300"%3E%3Crect fill="%23e9ecef" width="300" height="300"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" fill="%236c757d" font-size="20"%3ENo Cover Image%3C/text%3E%3C/svg%3E');
                    }
                    $(this).off('error');
                });
            $('#album-title').text(album.title);
            $('#album-artist').text(album.artist);
            $('#album-genre').text(album.genre || 'Unknown');
            $('#album-year').text(album.release_year || 'N/A');
            $('#album-label').text(album.label || 'Independent');
            $('#album-description').text(album.description || 'No description available.');

            // Load average rating
            RatingService.getAlbumAverage(albumId).then((ratingResponse) => {
                const avgRating = ratingResponse.data?.average_rating || 0;
                const ratingCount = ratingResponse.data?.rating_count || 0;
                
                $('#album-avg-rating').text(avgRating > 0 ? avgRating.toFixed(1) : 'N/A');
                $('#album-rating-count').text(`${ratingCount} rating${ratingCount !== 1 ? 's' : ''}`);
                $('#album-stars').html(generateStars(avgRating));
            }).catch(error => {
                console.error('Error loading ratings:', error);
                $('#album-avg-rating').text('N/A');
                $('#album-rating-count').text('0 ratings');
                $('#album-stars').html(generateStars(0));
            });

            // Load tracks
            TrackService.getByAlbum(albumId).then((tracksResponse) => {
                const tracks = Array.isArray(tracksResponse) ? tracksResponse :
                              (tracksResponse.data?.tracks || tracksResponse.data || tracksResponse.tracks || []);
                
                if (tracks.length === 0) {
                    $('#track-list').html('<p class="text-muted">No tracks available</p>');
                } else {
                    const tracksHTML = tracks.map((track, index) => `
                        <div class="list-group-item track-list-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="track-number">${track.track_number || index + 1}</span>
                                    <span class="track-title">${track.title}</span>
                                </div>
                                <span class="text-muted">${track.duration || 'N/A'}</span>
                            </div>
                        </div>
                    `).join('');
                    $('#track-list').html(tracksHTML);
                }
            }).catch(error => {
                console.error('Error loading tracks:', error);
                $('#track-list').html('<p class="text-muted">Unable to load tracks</p>');
            });

            // Load reviews
            loadAlbumReviews(albumId);

            // Show/hide authenticated features
            if (isAuthenticated) {
                $('#rate-album-btn, #write-review-btn, #favorite-btn').show();
                
                // Check if user has already favorited this album
                FavoriteService.isFavorite(albumId).then((favResponse) => {
                    console.log('Favorite check response:', favResponse);
                    const isFavorited = favResponse.data?.is_favorited || favResponse.data?.is_favorite || false;
                    updateFavoriteButton(isFavorited);
                }).catch(error => {
                    console.error('Error checking favorite status:', error);
                    updateFavoriteButton(false);
                });

                // Check if user has already rated this album
                RatingService.getUserAlbumRating(albumId).then((userRatingResponse) => {
                    const userRating = userRatingResponse.data?.rating;
                    if (userRating) {
                        // Pre-select the user's rating in the modal
                        $(`input[name="rating"][value="${userRating}"]`).prop('checked', true);
                    }
                }).catch(error => {
                    console.error('Error checking user rating:', error);
                });
            } else {
                // Replace buttons with login prompts for non-authenticated users
                $('#rate-album-btn').replaceWith('<button class=\"btn btn-outline-secondary\" onclick=\"window.location.hash=\'login\'\"><i class=\"bi bi-lock\"></i> Login to Rate</button>');
                $('#write-review-btn').replaceWith('<button class=\"btn btn-outline-secondary\" onclick=\"window.location.hash=\'login\'\"><i class=\"bi bi-lock\"></i> Login to Review</button>');
                $('#favorite-btn').replaceWith('<button class=\"btn btn-outline-secondary\" onclick=\"window.location.hash=\'login\'\"><i class=\"bi bi-lock\"></i> Login to Favorite</button>');
            }

            // Set up favorites button
            $('#favorite-btn').off('click').on('click', function() {
                if (!isAuthenticated) {
                    showNotification('Please login to add favorites', 'warning');
                    window.location.hash = 'login';
                    return;
                }
                handleFavoriteToggle(albumId);
            });

            // Set up rating form
            $('#rating-form').off('submit').on('submit', function(e) {
                e.preventDefault();
                handleRating(albumId);
            });

            // Set up review form
            $('#review-form').off('submit').on('submit', function(e) {
                e.preventDefault();
                handleReview(albumId);
            });

        }).catch(error => {
            console.error('Error loading album:', error);
            showNotification('Failed to load album details', 'error');
            window.location.hash = 'home';
        });
    }, 100);
}

/**
 * Update favorite button state
 */
function updateFavoriteButton(isFavorited) {
    const btn = $('#favorite-btn');
    if (isFavorited) {
        btn.html('<i class="bi bi-heart-fill"></i> Remove from Favorites');
        btn.removeClass('btn-outline-danger').addClass('btn-danger');
    } else {
        btn.html('<i class="bi bi-heart"></i> Add to Favorites');
        btn.removeClass('btn-danger').addClass('btn-outline-danger');
    }
    btn.show();
}

/**
 * Handle favorite toggle
 */
function handleFavoriteToggle(albumId) {
    FavoriteService.toggle(albumId).then((response) => {
        console.log('Toggle favorite response:', response);
        const isFavorited = response.data?.is_favorited || response.data?.is_favorite || response.is_favorited || response.is_favorite || false;
        updateFavoriteButton(isFavorited);
        showNotification(
            isFavorited ? 'Added to favorites!' : 'Removed from favorites',
            'success'
        );
    }).catch(error => {
        console.error('Error toggling favorite:', error);
        const errorMsg = error.message || 'Failed to update favorites';
        showNotification(errorMsg, 'error');
    });
}

/**
 * Load reviews for an album
 */
function loadAlbumReviews(albumId) {
    ReviewService.getByAlbum(albumId).then((response) => {
        const reviews = Array.isArray(response) ? response :
                       (response.data?.reviews || response.data || response.reviews || []);

        if (reviews.length === 0) {
            $('#reviews-list').html('<p class="text-muted">No reviews yet. Be the first to review this album!</p>');
            return;
        }

        const reviewsHTML = reviews.map(review => `
            <div class="card review-card mb-3">
                <div class="card-body">
                    <div class="review-header">
                        <div>
                            <span class="review-author">${review.username || 'Anonymous'}</span>
                            <span class="review-date ms-2">${review.review_date || review.created_at || ''}</span>
                        </div>
                    </div>
                    <h5 class="mt-2">${review.title}</h5>
                    <p>${review.review_text || review.text || ''}</p>
                </div>
            </div>
        `).join('');

        $('#reviews-list').html(reviewsHTML);
    }).catch(error => {
        console.error('Error loading reviews:', error);
        $('#reviews-list').html('<p class="text-muted">Unable to load reviews</p>');
    });
}

/**
 * Handle rating submission
 */
function handleRating(albumId) {
    const isAuthenticated = !!localStorage.getItem('user_token');
    
    if (!isAuthenticated) {
        showNotification('Please login to rate albums', 'warning');
        window.location.hash = 'login';
        return;
    }

    const rating = $('input[name="rating"]:checked').val();

    if (!rating) {
        showNotification('Please select a rating', 'warning');
        return;
    }

    // Submit rating to API
    RatingService.rate({
        album_id: parseInt(albumId),
        rating: parseInt(rating)
    }).then((response) => {
        // Close modal
        const modalElement = document.getElementById('rateModal');
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        } else {
            $(modalElement).modal('hide');
        }

        // Show success message
        showNotification('Rating submitted successfully!', 'success');

        // Reload rating display
        RatingService.getAlbumAverage(albumId).then((ratingResponse) => {
            console.log('Rating response:', ratingResponse);
            const avgRating = ratingResponse.data?.average_rating || 0;
            const ratingCount = ratingResponse.data?.rating_count || 0;
            
            console.log('Average rating:', avgRating, 'Rating count:', ratingCount);
            
            $('#album-avg-rating').text(avgRating > 0 ? avgRating.toFixed(1) : 'N/A');
            $('#album-rating-count').text(`${ratingCount} rating${ratingCount !== 1 ? 's' : ''}`);
            $('#album-stars').html(generateStars(avgRating));
            
            console.log('Updated stars HTML:', $('#album-stars').html());
        }).catch(error => {
            console.error('Error loading updated ratings:', error);
        });
    }).catch(error => {
        console.error('Error submitting rating:', error);
        showNotification(error.message || 'Failed to submit rating', 'error');
    });
}

/**
 * Handle review submission
 */
function handleReview(albumId) {
    const isAuthenticated = !!localStorage.getItem('user_token');
    
    if (!isAuthenticated) {
        showNotification('Please login to write reviews', 'warning');
        window.location.hash = 'login';
        return;
    }

    const title = $('#review-title').val().trim();
    const reviewText = $('#review-text').val().trim();

    if (!title || !reviewText) {
        showNotification('Please fill in all fields', 'warning');
        return;
    }

    // Frontend validation
    if (title.length < 3 || title.length > 255) {
        showNotification('Title must be between 3 and 255 characters', 'warning');
        return;
    }

    if (reviewText.length < 10 || reviewText.length > 5000) {
        showNotification('Review text must be between 10 and 5000 characters', 'warning');
        return;
    }

    // Create review via API
    ReviewService.create({
        album_id: parseInt(albumId),
        title: title,
        review_text: reviewText
    }).then((response) => {
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('reviewModal'));
        modal.hide();

        // Show success message
        showNotification('Review posted successfully!', 'success');

        // Reload reviews
        loadAlbumReviews(albumId);

        // Clear form
        $('#review-title').val('');
        $('#review-text').val('');
    }).catch(error => {
        console.error('Error posting review:', error);
        const errorMsg = error.message || 'Failed to post review';
        showNotification(errorMsg, 'error');
    });
}

/**
 * Load search page
 */
function loadSearchPage(params) {
    setTimeout(() => {
        const query = params.q || '';
        const genre = params.genre || '';

        // Set search input
        if (query) {
            $('#search-input').val(query);
        }
        
        // Set genre filter if provided
        if (genre) {
            $('#filter-genre').val(genre.toLowerCase());
        }

        // Perform initial search
        performSearch(query, genre);

        // Set up search button
        $('#search-btn').off('click').on('click', function() {
            const searchQuery = $('#search-input').val().trim();
            performSearch(searchQuery, $('#filter-genre').val());
        });
        
        // Set up enter key for search
        $('#search-input').off('keypress').on('keypress', function(e) {
            if (e.which === 13) {
                const searchQuery = $(this).val().trim();
                performSearch(searchQuery, $('#filter-genre').val());
            }
        });

        // Set up filters
        $('#apply-filters-btn').off('click').on('click', applySearchFilters);
        $('#clear-filters-btn').off('click').on('click', clearSearchFilters);

        // Set up sort
        $('#sort-select').off('change').on('change', function() {
            const sortBy = $(this).val();
            sortSearchResults(sortBy);
        });
    }, 100);
}

/**
 * Perform search with query and/or genre
 */
function performSearch(query, genre) {
    $('#search-results-container').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    
    if (genre && !query) {
        // Genre-only search
        AlbumService.getByGenre(genre, 100).then((response) => {
            const albums = Array.isArray(response) ? response : 
                          (response.data?.albums || response.data || response.albums || []);
            window.currentSearchResults = albums;
            displaySearchResults(albums);
        }).catch(error => {
            console.error('Error searching by genre:', error);
            displaySearchResults([]);
        });
    } else if (query) {
        // Text search
        AlbumService.search(query).then((response) => {
            let albums = Array.isArray(response) ? response : 
                        (response.data?.albums || response.data || response.albums || []);
            
            // Filter by genre if specified
            if (genre) {
                albums = albums.filter(album => album.genre.toLowerCase() === genre.toLowerCase());
            }
            
            window.currentSearchResults = albums;
            displaySearchResults(albums);
        }).catch(error => {
            console.error('Error searching albums:', error);
            displaySearchResults([]);
        });
    } else {
        // No filters, show all albums
        AlbumService.getAll({ limit: 100 }).then((response) => {
            const albums = Array.isArray(response) ? response : 
                          (response.data?.albums || response.data || response.albums || []);
            window.currentSearchResults = albums;
            displaySearchResults(albums);
        }).catch(error => {
            console.error('Error loading albums:', error);
            displaySearchResults([]);
        });
    }
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
    const query = $('#search-input').val().trim();
    const genre = $('#filter-genre').val();
    
    // Re-perform search with genre filter
    performSearch(query, genre);
}

/**
 * Clear search filters
 */
function clearSearchFilters() {
    $('#filter-genre').val('');
    $('#filter-year').val('');
    $('#filter-rating').val('');
    $('#search-input').val('');
    performSearch('', '');
}

/**
 * Sort search results
 */
function sortSearchResults(sortBy) {
    if (!window.currentSearchResults) return;
    
    let sorted = [...window.currentSearchResults];

    switch (sortBy) {
        case 'rating-desc':
            sorted.sort((a, b) => (parseFloat(b.avg_rating) || 0) - (parseFloat(a.avg_rating) || 0));
            break;
        case 'rating-asc':
            sorted.sort((a, b) => (parseFloat(a.avg_rating) || 0) - (parseFloat(b.avg_rating) || 0));
            break;
        case 'year-desc':
            sorted.sort((a, b) => (b.year || 0) - (a.year || 0));
            break;
        case 'year-asc':
            sorted.sort((a, b) => (a.year || 0) - (b.year || 0));
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
        $('#top-albums-list').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        
        // Get filter values
        const limit = 50; // Show top 50 albums
        
        // Fetch top rated albums from API
        AlbumService.getTopRated(limit).then((response) => {
            console.log('Top rated albums response:', response);
            
            // Handle different response structures
            let albums = [];
            if (Array.isArray(response)) {
                albums = response;
            } else if (response.data) {
                if (Array.isArray(response.data)) {
                    albums = response.data;
                } else if (response.data.albums && Array.isArray(response.data.albums)) {
                    albums = response.data.albums;
                } else if (response.data.data && Array.isArray(response.data.data)) {
                    albums = response.data.data;
                }
            } else if (response.albums && Array.isArray(response.albums)) {
                albums = response.albums;
            }
            
            console.log('Extracted albums:', albums);
            
            if (albums.length === 0) {
                $('#top-albums-list').html('<div class="alert alert-info">No albums found. Be the first to rate some albums!</div>');
                return;
            }
            
            // Generate HTML with ranking
            const albumsHTML = albums.map((album, index) => {
                const rank = index + 1;
                const rankClass = rank <= 3 ? `rank-${rank}` : '';
                const avgRating = parseFloat(album.avg_rating || 0);
                const ratingCount = parseInt(album.rating_count || 0);
                const coverUrl = album.cover_url || 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="100" height="100"%3E%3Crect fill="%23e9ecef" width="100" height="100"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" fill="%236c757d" font-size="14"%3ENo Image%3C/text%3E%3C/svg%3E';

                return `
                    <div class="card mb-3 top-album-item">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="rank-number ${rankClass} display-4 fw-bold text-warning">#${rank}</div>
                                </div>
                                <div class="col-auto">
                                    <img src="${coverUrl}" 
                                         alt="${album.title}" 
                                         class="rounded" 
                                         style="width: 100px; height: 100px; object-fit: cover;"
                                         onerror="if(this.src.indexOf('data:image')===-1){this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'100\\' height=\\'100\\'%3E%3Crect fill=\\'%23e9ecef\\' width=\\'100\\' height=\\'100\\'/%3E%3Ctext x=\\'50%25\\' y=\\'50%25\\' dominant-baseline=\\'middle\\' text-anchor=\\'middle\\' fill=\\'%236c757d\\' font-size=\\'14\\'%3ENo Image%3C/text%3E%3C/svg%3E';}this.onerror=null;">
                                </div>
                                <div class="col">
                                    <h5 class="mb-1">${album.title}</h5>
                                    <p class="text-muted mb-2">${album.artist}</p>
                                    <div>
                                        <span class="badge bg-primary me-2">${album.genre}</span>
                                        <span class="badge bg-secondary">${album.year || 'N/A'}</span>
                                    </div>
                                </div>
                                <div class="col-auto text-center">
                                    <div class="display-6 fw-bold text-warning">${avgRating > 0 ? avgRating.toFixed(1) : 'N/A'}</div>
                                    <div class="small text-muted">${generateStars(avgRating)}</div>
                                    <div class="text-muted small mt-1">${ratingCount} rating${ratingCount !== 1 ? 's' : ''}</div>
                                </div>
                                <div class="col-auto">
                                    <a href="#album/${album.album_id}" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            $('#top-albums-list').html(albumsHTML);
        }).catch(error => {
            console.error('Error loading top rated albums:', error);
            $('#top-albums-list').html('<div class="alert alert-danger">Failed to load top rated albums. Please try again later.</div>');
        });

        // Set up filters (currently disabled as backend doesn't support these filters)
        $('#timeframe-select, #genre-select, #min-ratings').on('change', filterTopRated);
    }, 100);
}

/**
 * Filter top rated albums
 */
function filterTopRated() {
    // Reload with current filters
    loadTopRatedPage();
}

/**
 * Load profile page
 */
function loadProfilePage() {
    const token = localStorage.getItem('user_token');
    if (!token) {
        showNotification('Please login to view your profile', 'warning');
        window.location.hash = 'login';
        return;
    }

    setTimeout(() => {
        try {
            const payload = Utils.parseJwt(token);
            const user = payload.user;
            
            if (!user) {
                showNotification('Invalid session', 'error');
                window.location.hash = 'login';
                return;
            }

            // Populate user info
            const avatarUrl = user.avatar_url || 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="200"%3E%3Crect fill="%23007bff" width="200" height="200"/%3E%3Ctext fill="%23fff" x="50%25" y="50%25" text-anchor="middle" dy=".3em" font-size="60" font-family="Arial"%3E' + (user.first_name ? user.first_name.charAt(0) : 'U') + '%3C/text%3E%3C/svg%3E';
            $('#profile-avatar').attr('src', avatarUrl)
                .on('error', function() {
                    $(this).off('error');
                    $(this).attr('src', 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="200"%3E%3Crect fill="%23007bff" width="200" height="200"/%3E%3Ctext fill="%23fff" x="50%25" y="50%25" text-anchor="middle" dy=".3em" font-size="60" font-family="Arial"%3EU%3C/text%3E%3C/svg%3E');
                });
            $('#profile-username').text(user.username || 'User');
            $('#profile-fullname').text(`${user.first_name || ''} ${user.last_name || ''}`);
            $('#profile-email span').text(user.email || '');

            // Populate edit form
            $('#edit-firstname').val(user.first_name || '');
            $('#edit-lastname').val(user.last_name || '');
            $('#edit-username').val(user.username || '');
            $('#edit-email').val(user.email || '');

            // Load user's ratings
            RatingService.getByUser().then(response => {
                const ratings = Array.isArray(response) ? response :
                               (response.data?.ratings || response.data || response.ratings || []);
                
                $('#stat-ratings').text(ratings.length);
                
                // Calculate average
                if (ratings.length > 0) {
                    const avg = ratings.reduce((sum, r) => sum + parseFloat(r.rating), 0) / ratings.length;
                    $('#stat-avg-rating').text(avg.toFixed(1));
                } else {
                    $('#stat-avg-rating').text('0.0');
                }
                
                loadUserRatings(ratings);
            }).catch(error => {
                console.error('Error loading ratings:', error);
                $('#stat-ratings').text('0');
                $('#stat-avg-rating').text('0.0');
            });

            // Load user's reviews
            ReviewService.getByUser().then(response => {
                const reviews = Array.isArray(response) ? response :
                               (response.data?.reviews || response.data || response.reviews || []);
                
                $('#stat-reviews').text(reviews.length);
                loadUserReviews(reviews);
            }).catch(error => {
                console.error('Error loading reviews:', error);
                $('#stat-reviews').text('0');
            });

            // Load user's favorites
            FavoriteService.getByUser().then(response => {
                const favorites = Array.isArray(response) ? response :
                                 (response.data?.favorites || response.data || response.favorites || []);
                
                $('#stat-favorites').text(favorites.length);
                loadUserFavorites(favorites);
            }).catch(error => {
                console.error('Error loading favorites:', error);
                $('#stat-favorites').text('0');
            });

            // Set up edit profile form
            $('#edit-profile-form').off('submit').on('submit', function(e) {
                e.preventDefault();
                handleEditProfile();
            });
        } catch (e) {
            console.error('Error parsing token:', e);
            showNotification('Invalid session', 'error');
            window.location.hash = 'login';
        }
    }, 100);
}

/**
 * Load user ratings
 */
function loadUserRatings(ratings) {
    if (!ratings || ratings.length === 0) {
        $('#user-ratings-list').html('<p class="text-muted">You haven\'t rated any albums yet.</p>');
        return;
    }

    const ratingsHTML = ratings.map(rating => {
        return `
            <div class="col-md-3 col-sm-6 mb-3">
                <a href="#album/${rating.album_id}" class="text-decoration-none">
                    <div class="card h-100">
                        <img src="${rating.cover_url || rating.cover_image_url || 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="250" height="250"%3E%3Crect fill="%23e9ecef" width="250" height="250"/%3E%3Ctext fill="%236c757d" x="50%25" y="50%25" text-anchor="middle" dy=".3em" font-size="20" font-family="Arial"%3ENo Image%3C/text%3E%3C/svg%3E'}" 
                             class="card-img-top" alt="${rating.title}"
                             onerror="if(this.src.indexOf('data:image')===-1){this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'250\' height=\'250\'%3E%3Crect fill=\'%23e9ecef\' width=\'250\' height=\'250\'/%3E%3Ctext fill=\'%236c757d\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' font-size=\'20\' font-family=\'Arial\'%3ENo Image%3C/text%3E%3C/svg%3E';}this.onerror=null;">
                        <div class="card-body">
                            <h6>${rating.title}</h6>
                            <p class="text-muted small mb-2">${rating.artist}</p>
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
function loadUserReviews(reviews) {
    if (!reviews || reviews.length === 0) {
        $('#user-reviews-list').html('<p class="text-muted">You haven\'t written any reviews yet.</p>');
        return;
    }

    const reviewsHTML = reviews.map(review => {
        return `
            <div class="card review-card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="${review.cover_url || review.cover_image_url || 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="60" height="60"%3E%3Crect fill="%23e9ecef" width="60" height="60"/%3E%3C/svg%3E'}" 
                             alt="${review.album_title || review.title}"
                             class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;"
                             onerror="if(this.src.indexOf('data:image')===-1){this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'60\' height=\'60\'%3E%3Crect fill=\'%23e9ecef\' width=\'60\' height=\'60\'/%3E%3C/svg%3E';}this.onerror=null;">
                        <div>
                            <h6 class="mb-0">${review.album_title || review.title}</h6>
                            <small class="text-muted">${review.artist}</small>
                        </div>
                    </div>
                    <div class="review-header">
                        <span class="review-date">${review.review_date || review.created_at || ''}</span>
                    </div>
                    <h5 class="mt-2">${review.title}</h5>
                    <p>${review.review_text || review.text}</p>
                    <a href="#album/${review.album_id}" class="btn btn-sm btn-outline-primary">View Album</a>
                </div>
            </div>
        `;
    }).join('');

    $('#user-reviews-list').html(reviewsHTML);
}

/**
 * Load user favorites
 */
function loadUserFavorites(favorites) {
    if (!favorites || favorites.length === 0) {
        $('#user-favorites-list').html('<p class="text-muted">You haven\'t added any favorites yet.</p>');
        return;
    }

    const favoritesHTML = favorites.map(favorite => {
        return `
            <div class="col-md-3 col-sm-6 mb-3">
                <a href="#album/${favorite.album_id}" class="text-decoration-none">
                    <div class="card h-100">
                        <img src="${favorite.cover_url || favorite.cover_image_url || 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="250" height="250"%3E%3Crect fill="%23e9ecef" width="250" height="250"/%3E%3Ctext fill="%236c757d" x="50%25" y="50%25" text-anchor="middle" dy=".3em" font-size="20" font-family="Arial"%3ENo Image%3C/text%3E%3C/svg%3E'}" 
                             class="card-img-top" alt="${favorite.title}"
                             onerror="if(this.src.indexOf('data:image')===-1){this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'250\' height=\'250\'%3E%3Crect fill=\'%23e9ecef\' width=\'250\' height=\'250\'/%3E%3Ctext fill=\'%236c757d\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' font-size=\'20\' font-family=\'Arial\'%3ENo Image%3C/text%3E%3C/svg%3E';}this.onerror=null;">
                        <div class="card-body">
                            <h6>${favorite.title}</h6>
                            <p class="text-muted small mb-2">${favorite.artist}</p>
                            <p class="text-muted small mb-0">${favorite.genre || ''} • ${favorite.release_year || favorite.year || ''}</p>
                        </div>
                    </div>
                </a>
            </div>
        `;
    }).join('');

    $('#user-favorites-list').html(favoritesHTML);
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
