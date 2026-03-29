# Notes App
A simple web-based note-taking application built using JavaScript, PHP, and CSS — perfect for storing, viewing and managing notes with minimal setup.

## Live Demo
You can visit directly from your browser:
[Try Demo](https://mynotesapp.page.gd/)

## Features
- Create, view, edit, and delete notes
- Lightweight and self-hosted — no heavy dependencies required
- Simple folder structure: files served from `pages/`, with assets in `css/`, `js/`, `images/`
- Uses a simple database backend (SQL + PHP) for note storage (`db.php`, `db.sql`)

## Repository Structure
```text
/api/         — API endpoints  
/css/         — Stylesheets  
/js/          — Client-side JavaScript  
/images/      — Static images used by the app  
/pages/       — Front-end pages (HTML / PHP)  
db.php        — Database connection & helper  
db.sql        — SQL file to initialize the database  
index.php     — Main entry point  
README.md     — This file  
```

## Getting Started
### Prerequisites
- PHP (7.x or higher)
- MySQL (or compatible SQL database)
- Web server (e.g. Apache, Nginx) or a PHP-built-in server

### Installation & Setup
1. Clone the repository
```text
git clone https://github.com/rytvee/notes-app.git
cd notes-app
```
2. Create a database in MySQL (e.g. `notes_app_db`)
3. Import the provided SQL schema
```text
-- In MySQL CLI or GUI:
source db.sql;
```
4. Configure the database connection in `db.php` (set your DB host, username, password, database name)
```text
php -S localhost:8000
```
5. Serve the application via your web server or PHP built-in server:
6. Access the app in your browser at `http://localhost:8000` (or your server’s URL)


## Usage
- Visit the homepage (e.g. `index.php`) to view a list of notes
- Use the “Add Note” page to create a new note (title + content)
- Click on a note to view details or edit/delete it
- All changes are saved to the SQL database via the PHP backend

## Customization & Configuration
You can easily customize:
- Database credentials (in `db.php`)
- CSS styling (in `/css/`)
- Client-side behavior or enhancements (in `/js/`)
- Add new features — for example: note-tags, search, reminders, user login/auth

## License
This project is open-source; you can choose a license (e.g. MIT, Apache 2.0) by adding a `LICENSE` file.
