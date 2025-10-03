// server.js (main Node.js/Express file)

const express = require('express');
const session = require('express-session');
const db = require('./db'); // Your Node.js database connection module
const app = express();
const PORT = 3000;

// Set EJS as the templating engine
app.set('view engine', 'ejs');

// Session setup (using a placeholder secret)
app.use(session({
    secret: 'YOUR_VERY_SECRET_KEY', // Change this to a strong, random string
    resave: false,
    saveUninitialized: true,
    cookie: { secure: false } // set to true in production with HTTPS
}));

// Middleware to check if the user is logged in
function requireLogin(req, res, next) {
    if (!req.session.user_id) {
        // Redirects to login page if session is missing
        return res.redirect('/login'); 
    }
    next();
}

// Route for the Dashboard
app.get('/dashboard', requireLogin, async (req, res) => {
    try {
        const userId = req.session.user_id;
        
        // --- Database Query (Example using mysql2/promise) ---
        // Adjust the SQL and execution based on your actual DB library
        const [rows] = await db.query("SELECT * FROM courses WHERE user_id = ?", [userId]);
        const courses = rows;

        // Render the EJS template and pass data to it
        res.render('dashboard', {
            firstname: req.session.firstname,
            courses: courses
        });

    } catch (error) {
        console.error('Error fetching courses:', error);
        res.status(500).send("An error occurred while loading the dashboard.");
    }
});

// Route for Logout
app.get('/logout', (req, res) => {
    // 1. Destroy the session
    req.session.destroy(err => {
        if (err) {
            console.error('Logout error:', err);
        }
        
        // 2. Redirect to the login page (or home page)
        // Note: You must have a route defined for '/login' or '/'
        res.redirect('/login'); 
    });
});

// Start the server
app.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
});