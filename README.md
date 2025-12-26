# AlbumDB - Music Album Rating & Review Platform

A full-stack web application for discovering, rating, and reviewing music albums. Built with vanilla JavaScript frontend, PHP backend API, and MySQL database - deployed and accessible on the internet!

## 🌐 Live Demo

- **Frontend:** https://marvelous-taiyaki-c507df.netlify.app/
- **Backend API:** https://albumdb-b5ap2.ondigitalocean.app/
- **API Documentation:** https://albumdb-b5ap2.ondigitalocean.app/public/v1/docs/

## ✨ Features

### User Features
- **Browse Albums:** Explore a collection of music albums with detailed information
- **Search & Filter:** Find albums by title, artist, genre, or year
- **Rate Albums:** Give albums ratings from 0-10
- **Write Reviews:** Share detailed reviews with titles and descriptions
- **Favorites:** Save favorite albums for quick access
- **User Profiles:** Manage your personal profile with avatar and account details
- **Authentication:** Secure JWT-based login and registration

### Admin Features
- **Album Management:** Add, edit, and delete albums with cover images
- **Track Management:** Manage album tracklists
- **User Management:** View and manage user accounts
- **Content Moderation:** Monitor ratings and reviews

## 🛠️ Tech Stack

### Frontend
- **Vanilla JavaScript** - Pure JS, no frameworks
- **Bootstrap 5** - Responsive UI components
- **jQuery** - DOM manipulation and AJAX
- **SPA Router** (jquery.spapp.js) - Hash-based single-page navigation
- **Toastr** - Notification system

### Backend
- **PHP 8** - Server-side logic
- **Flight PHP** - Lightweight micro-framework
- **MySQL** - Relational database
- **PDO** - Database abstraction layer
- **Composer** - Dependency management

### Authentication & Security
- **JWT** (Firebase PHP-JWT) - Stateless authentication
- **Bcrypt** - Password hashing
- **Role-based Access Control** - User/Admin permissions
- **CORS** - Cross-origin resource sharing

### API Documentation
- **Swagger/OpenAPI** - Interactive API documentation
- **Swagger-PHP** - Annotation-based documentation generation

### Deployment
- **Frontend:** Netlify (Static site hosting)
- **Backend:** DigitalOcean App Platform (PHP hosting)
- **Database:** FreeSQLDatabase (MySQL hosting)
- **Version Control:** GitHub with auto-deployment

## 📁 Project Structure

```
AlbumDB/
├── backend/
│   ├── config.php              # Database & JWT configuration
│   ├── index.php               # Application entry point
│   ├── composer.json           # PHP dependencies
│   ├── dao/                    # Data Access Objects
│   │   ├── BaseDAO.php         # Abstract base with common methods
│   │   ├── AlbumDAO.php        # Album database operations
│   │   ├── AuthDAO.php         # Authentication operations
│   │   ├── UserDAO.php         # User management
│   │   ├── RatingDAO.php       # Rating operations
│   │   ├── ReviewDAO.php       # Review operations
│   │   └── TrackDAO.php        # Track management
│   ├── services/               # Business logic layer
│   │   ├── BaseService.php     # Validation utilities
│   │   ├── AlbumService.php    # Album business logic
│   │   ├── AuthService.php     # Authentication & JWT
│   │   └── ...
│   ├── routes/                 # API endpoints
│   │   ├── AlbumRoutes.php     # Album API routes
│   │   ├── AuthRoutes.php      # Login/Register routes
│   │   └── ...
│   ├── MiddleWare/
│   │   └── AuthMiddleware.php  # JWT verification
│   └── public/v1/docs/         # Swagger UI
├── frontend/
│   ├── index.html              # Main HTML file
│   ├── css/
│   │   └── style.css           # Custom styles
│   ├── js/
│   │   ├── app.js              # Application initialization
│   │   ├── spa-router.js       # SPA routing logic
│   │   ├── albums.js           # Album page logic
│   │   ├── auth.js             # Authentication logic
│   │   └── admin.js            # Admin panel logic
│   ├── services/               # API service wrappers
│   │   ├── Albums.js           # Album API calls
│   │   ├── Ratings.js          # Rating API calls
│   │   └── ...
│   ├── utils/
│   │   ├── constants.js        # API URL configuration
│   │   ├── rest-client.js      # HTTP client with JWT
│   │   └── utils.js            # Helper functions
│   └── views/                  # HTML templates
│       ├── home.html           # Landing page
│       ├── login.html          # Login form
│       ├── album-details.html  # Album details page
│       └── ...
└── database/
    └── albumDB.sql             # Database schema
```

## 🏗️ Architecture

### Backend Architecture (MVC-inspired)
```
Routes → Services → DAOs → Database
   ↓         ↓         ↓
Routing   Business   Data
Layer      Logic     Access
```

**Flow Example (Getting Albums):**
1. **Routes** - `GET /albums` → `AlbumRoutes.php`
2. **Services** - `AlbumService->getAllAlbums()` (business logic, validation)
3. **DAOs** - `AlbumDAO->getAll()` (SQL queries)
4. **Database** - MySQL returns data
5. Response flows back through layers as JSON

### Authentication Flow
1. User submits login credentials
2. `AuthService` verifies password (bcrypt)
3. JWT token generated with user data
4. Token sent to client
5. Client stores token in localStorage
6. All API requests include token in `Authorization: Bearer <token>` header
7. `AuthMiddleware` verifies token on protected routes
8. User data extracted from token and made available to routes

### Frontend Architecture (SPA)
- **Hash-based routing** - URLs like `#/albums`, `#/login`
- **Event-driven** - User actions trigger API calls
- **State management** - localStorage for JWT and user data
- **Dynamic rendering** - Views loaded and rendered on-demand

## 🚀 Local Development Setup

### Prerequisites
- PHP 8.0+
- Composer
- MySQL 8.0+
- Web server (Apache/XAMPP recommended)

### Backend Setup
1. Clone repository:
   ```bash
   git clone https://github.com/TarikH22/AlbumDB.git
   cd AlbumDB/backend
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Configure database in `config.php`:
   ```php
   DB_HOST = 'localhost'
   DB_PORT = 3306
   DB_USER = 'root'
   DB_PASSWORD = 'your_password'
   DB_NAME = 'albumDB'
   ```

4. Import database:
   ```bash
   mysql -u root -p < ../database/albumDB.sql
   ```

5. Start server (XAMPP or built-in):
   ```bash
   php -S localhost:8888
   ```

### Frontend Setup
1. Update API URL in `frontend/utils/constants.js`:
   ```javascript
   PROJECT_BASE_URL: "http://localhost:8888/backend/"
   ```

2. Open `frontend/index.html` in browser or serve via:
   ```bash
   python -m http.server 3000
   ```

## 📊 Database Schema

### Tables
- **users** - User accounts and profiles
- **albums** - Album information (title, artist, year, genre, etc.)
- **tracks** - Album tracklists
- **ratings** - User album ratings (0-10 scale)
- **reviews** - User album reviews with text
- **favorites** - User favorite albums

### Key Relationships
- Users → Ratings (one-to-many)
- Users → Reviews (one-to-many)
- Users → Favorites (many-to-many via junction table)
- Albums → Tracks (one-to-many)
- Albums → Ratings (one-to-many)
- Albums → Reviews (one-to-many)

## 🔑 API Endpoints

### Authentication
- `POST /auth/register` - Create new user account
- `POST /auth/login` - Login and receive JWT token

### Albums
- `GET /albums` - Get all albums (public)
- `GET /albums/:id` - Get single album details (public)
- `POST /albums` - Create album (admin only)
- `PUT /albums/:id` - Update album (admin only)
- `DELETE /albums/:id` - Delete album (admin only)

### Ratings
- `GET /ratings/album/:id` - Get album ratings (public)
- `POST /ratings` - Rate an album (authenticated)
- `PUT /ratings/:id` - Update rating (authenticated)
- `DELETE /ratings/:id` - Delete rating (authenticated)

### Reviews
- `GET /reviews/album/:id` - Get album reviews (public)
- `GET /reviews/recent` - Get recent reviews (public)
- `POST /reviews` - Write review (authenticated)
- `PUT /reviews/:id` - Update review (authenticated)
- `DELETE /reviews/:id` - Delete review (authenticated)

### Favorites
- `GET /favorites` - Get user favorites (authenticated)
- `POST /favorites` - Add to favorites (authenticated)
- `DELETE /favorites/:id` - Remove from favorites (authenticated)

*Full API documentation available at: https://albumdb-b5ap2.ondigitalocean.app/public/v1/docs/*

## 🔐 Environment Variables

### Backend (DigitalOcean)
```
DB_HOST=sql7.freesqldatabase.com
DB_PORT=3306
DB_USER=sql7813017
DB_PASSWORD=your_password
DB_NAME=sql7813017
JWT_SECRET=your_secret_key
```

### Frontend
No environment variables needed - API URL configured in `constants.js`

## 📝 Development Notes

### Code Quality Features
- **Parameterized queries** - SQL injection prevention
- **Password hashing** - Bcrypt for secure storage
- **Input validation** - Server-side validation in services
- **Error handling** - Try-catch blocks throughout
- **Singleton pattern** - Database connection reuse
- **RESTful design** - Standard HTTP methods and status codes

### Known Limitations
- Free database has limited storage (100MB)
- No email verification implemented
- No password reset functionality
- Limited to 6 album cover uploads per day (placeholder service)

## 🤝 Contributing

This is a student project for educational purposes. However, suggestions and feedback are welcome!

## 📄 License

This project was created as part of a Web Programming course at the International Burch University.

## 👤 Author

**Tarik Hamzic**
- GitHub: [@TarikH22](https://github.com/TarikH22)
- Project Defense: January 2026

---

**Built with ❤️ for music lovers everywhere**
