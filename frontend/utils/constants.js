let Constants = {
    PROJECT_BASE_URL: window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1' 
        ? "http://localhost:8888/backend/" 
        : "https://albumdb-b5ap2.ondigitalocean.app/",
    USER_ROLE: "user", 
    ADMIN_ROLE: "admin"
}