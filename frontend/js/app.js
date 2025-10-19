/**
 * Main Application Logic
 * Initializes the SPA and sets up routes
 */

// Sample album data (will be replaced with backend API calls later)
const SAMPLE_ALBUMS = [
    {
        id: 1,
        title: "Abbey Road",
        artist: "The Beatles",
        genre: "Rock",
        year: 1969,
        label: "Apple Records",
        cover: "assets/images/albums/abbey-road.jpg",
        rating: 9.2,
        ratingCount: 1523,
        description: "Abbey Road is the eleventh studio album by the English rock band the Beatles. It is the last album the group started recording, although Let It Be was the last album completed before the band's break-up in April 1970.",
        tracks: [
            { number: 1, title: "Come Together", duration: "4:20" },
            { number: 2, title: "Something", duration: "3:03" },
            { number: 3, title: "Maxwell's Silver Hammer", duration: "3:27" },
            { number: 4, title: "Oh! Darling", duration: "3:26" },
            { number: 5, title: "Octopus's Garden", duration: "2:51" },
            { number: 6, title: "Here Comes the Sun", duration: "3:05" }
        ]
    },
    {
        id: 2,
        title: "The Dark Side of the Moon",
        artist: "Pink Floyd",
        genre: "Rock",
        year: 1973,
        label: "Harvest Records",
        cover: "assets/images/albums/dark-side-moon.jpg",
        rating: 9.5,
        ratingCount: 2341,
        description: "The Dark Side of the Moon is the eighth studio album by the English rock band Pink Floyd. A concept album, its themes explore conflict, greed, time, death, and mental illness.",
        tracks: [
            { number: 1, title: "Speak to Me", duration: "1:30" },
            { number: 2, title: "Breathe", duration: "2:43" },
            { number: 3, title: "On the Run", duration: "3:36" },
            { number: 4, title: "Time", duration: "6:53" },
            { number: 5, title: "The Great Gig in the Sky", duration: "4:36" }
        ]
    },
    {
        id: 3,
        title: "Thriller",
        artist: "Michael Jackson",
        genre: "Pop",
        year: 1982,
        label: "Epic Records",
        cover: "assets/images/albums/thriller.jpg",
        rating: 9.0,
        ratingCount: 1876,
        description: "Thriller is the sixth studio album by Michael Jackson. It explores genres including post-disco, rock, pop, and funk. Thriller became the best-selling album of all time.",
        tracks: [
            { number: 1, title: "Wanna Be Startin' Somethin'", duration: "6:03" },
            { number: 2, title: "Baby Be Mine", duration: "4:20" },
            { number: 3, title: "The Girl Is Mine", duration: "3:42" },
            { number: 4, title: "Thriller", duration: "5:57" },
            { number: 5, title: "Beat It", duration: "4:18" }
        ]
    },
    {
        id: 4,
        title: "To Pimp a Butterfly",
        artist: "Kendrick Lamar",
        genre: "Hip Hop",
        year: 2015,
        label: "Top Dawg Entertainment",
        cover: "assets/images/albums/to-pimp-butterfly.jpg",
        rating: 8.8,
        ratingCount: 987,
        description: "To Pimp a Butterfly is the third studio album by American rapper Kendrick Lamar. A politically-charged record, it addresses issues of race, depression, and institutional discrimination.",
        tracks: [
            { number: 1, title: "Wesley's Theory", duration: "4:47" },
            { number: 2, title: "For Free?", duration: "2:10" },
            { number: 3, title: "King Kunta", duration: "3:54" },
            { number: 4, title: "Institutionalized", duration: "4:31" },
            { number: 5, title: "These Walls", duration: "5:00" }
        ]
    },
    {
        id: 5,
        title: "Random Access Memories",
        artist: "Daft Punk",
        genre: "Electronic",
        year: 2013,
        label: "Columbia Records",
        cover: "assets/images/albums/random-access-memories.jpg",
        rating: 8.5,
        ratingCount: 1234,
        description: "Random Access Memories is the fourth studio album by French electronic duo Daft Punk. It pays tribute to late 1970s and early 1980s American music.",
        tracks: [
            { number: 1, title: "Give Life Back to Music", duration: "4:34" },
            { number: 2, title: "The Game of Love", duration: "5:22" },
            { number: 3, title: "Giorgio by Moroder", duration: "9:04" },
            { number: 4, title: "Get Lucky", duration: "6:09" },
            { number: 5, title: "Instant Crush", duration: "5:37" }
        ]
    },
    {
        id: 6,
        title: "OK Computer",
        artist: "Radiohead",
        genre: "Rock",
        year: 1997,
        label: "Parlophone",
        cover: "assets/images/albums/ok-computer.jpg",
        rating: 9.3,
        ratingCount: 1654,
        description: "OK Computer is the third studio album by English rock band Radiohead. The album explores themes of modern alienation, social anxiety, and technology.",
        tracks: [
            { number: 1, title: "Airbag", duration: "4:44" },
            { number: 2, title: "Paranoid Android", duration: "6:23" },
            { number: 3, title: "Subterranean Homesick Alien", duration: "4:27" },
            { number: 4, title: "Exit Music (For a Film)", duration: "4:24" },
            { number: 5, title: "Let Down", duration: "4:59" }
        ]
    }
];

// Sample reviews
const SAMPLE_REVIEWS = [
    {
        id: 1,
        albumId: 1,
        username: "musiclover92",
        rating: 10,
        title: "A Masterpiece",
        text: "Abbey Road is simply perfection. Every track flows seamlessly into the next, creating one of the most cohesive albums ever made.",
        date: "2024-01-15"
    },
    {
        id: 2,
        albumId: 2,
        username: "rockfan2000",
        rating: 10,
        title: "Timeless Classic",
        text: "This album changed my life. The production, lyrics, and musicianship are all at the highest level. A true work of art.",
        date: "2024-01-20"
    }
];

// Application State
const AppState = {
    currentUser: null,
    albums: SAMPLE_ALBUMS,
    reviews: SAMPLE_REVIEWS,
    userRatings: {},
    userFavorites: []
};

/**
 * Initialize the application
 */
function initApp() {
    // Register all routes
    registerRoutes();

    // Initialize the router
    SPARouter.init();

    // Set up event listeners
    setupEventListeners();

    // Check for stored user session
    checkUserSession();
}

/**
 * Register all application routes
 */
function registerRoutes() {
    SPARouter.register('home', 'home.html', loadHomePage);
    SPARouter.register('login', 'login.html', loadLoginPage);
    SPARouter.register('register', 'register.html', loadRegisterPage);
    SPARouter.register('search', 'search.html', loadSearchPage);
    SPARouter.register('profile', 'profile.html', loadProfilePage);
    SPARouter.register('top-rated', 'top-rated.html', loadTopRatedPage);
    SPARouter.register('album/:id', 'album-details.html', loadAlbumDetailsPage);
}

/**
 * Set up global event listeners
 */
function setupEventListeners() {
    // Logout button
    $(document).on('click', '#logout-btn', function(e) {
        e.preventDefault();
        logout();
    });

    // Hero search
    $(document).on('click', '.hero-section .btn-primary', function() {
        const query = $('#hero-search').val();
        if (query) {
            SPARouter.navigate('search', { query: query });
        }
    });
}

/**
 * Check if user is logged in
 */
function checkUserSession() {
    const storedUser = localStorage.getItem('albumrate_user');
    if (storedUser) {
        AppState.currentUser = JSON.parse(storedUser);
        updateNavigation();
    }
}

/**
 * Update navigation based on login status
 */
function updateNavigation() {
    if (AppState.currentUser) {
        $('#auth-nav').addClass('d-none');
        $('#user-nav').removeClass('d-none');
    } else {
        $('#auth-nav').removeClass('d-none');
        $('#user-nav').addClass('d-none');
    }
}

/**
 * Logout function
 */
function logout() {
    AppState.currentUser = null;
    localStorage.removeItem('albumrate_user');
    updateNavigation();
    SPARouter.navigate('home');
}

/**
 * Get album by ID
 */
function getAlbumById(id) {
    return AppState.albums.find(album => album.id == id);
}

/**
 * Get reviews for an album
 */
function getAlbumReviews(albumId) {
    return AppState.reviews.filter(review => review.albumId == albumId);
}

/**
 * Generate star rating HTML
 */
function generateStars(rating) {
    const fullStars = Math.floor(rating / 2);
    const halfStar = rating % 2 >= 1;
    const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);

    let html = '';
    for (let i = 0; i < fullStars; i++) {
        html += '<i class="bi bi-star-fill"></i>';
    }
    if (halfStar) {
        html += '<i class="bi bi-star-half"></i>';
    }
    for (let i = 0; i < emptyStars; i++) {
        html += '<i class="bi bi-star"></i>';
    }
    return html;
}

/**
 * Generate album card HTML
 */
function generateAlbumCard(album) {
    return `
        <div class="col-md-3 col-sm-6">
            <a href="#album/${album.id}" class="text-decoration-none">
                <div class="card album-card">
                    <img src="${album.cover}" class="card-img-top" alt="${album.title}"
                         onerror="this.src='https://via.placeholder.com/250x250?text=Album+Cover'">
                    <div class="card-body">
                        <h5 class="album-title">${album.title}</h5>
                        <p class="album-artist">${album.artist}</p>
                        <div class="rating">
                            <span class="rating-badge">${album.rating}/10</span>
                            <small class="text-muted">(${album.ratingCount} ratings)</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    `;
}

// Initialize app when document is ready
$(document).ready(function() {
    initApp();
});
