# ReleaseNotes.app
A simple, standalone self-hosted release notes content management system built with PHP, PDO, and the Trumbowyg WYSIWYG editor. It features a tiered public feed, a dedicated secure admin dashboard, a JSON API, and an embeddable JavaScript widget for cross-site notifications.

---

## Features

- **Tiered Public Feed (`index.php`)**: Displays the newest full-length release, the next 5 summarized releases with "Read More" links, and a compact archive list for older updates.
- **Single Release View (`view.php`)**: Dedicated page for reading individual release entries with header banner support and conditional embed layout handling.
- **Secure Admin Dashboard (`admin/`)**: Full CRUD capabilities (create, edit, delete, duplicate releases), image upload handling (JPG, PNG, WEBP), custom publish date/time selection, and secure password-hashed session authentication.
- **JSON API Endpoint (`api/releases.php`)**: Provides external access to the latest release ID, type, and title with CORS enabled (`Access-Control-Allow-Origin: *`) for seamless cross-domain queries.
- **Embeddable JavaScript Widget (`assets/widget.js`)**: A drop-in notification widget featuring a real-time unread badge indicator, animated popup dialog, and an interactive modal iframe loading your public release feed.
- **Automated Installation Wizard (`install/`)**: Web-based setup script that configures your database connection, populates required tables, sets up administrator credentials, and automatically generates your application configuration.

---

## Tech Stack

- **Backend**: PHP 7.4+ with PDO (PHP Data Objects)
- **Database**: MySQL / MariaDB
- **Frontend**: HTML5, CSS3 (Separated `style.css` and `admin.css`), JavaScript (Vanilla ES6)
- **WYSIWYG Editor**: Trumbowyg jQuery plugin

---

## Project Directory Structure

```text
release-notes-cms/
├── admin/
│   ├── create.php         # Create new release form (with Trumbowyg editor)
│   ├── edit.php           # Edit existing release form
│   └── index.php          # Admin login & management dashboard
├── api/
│   └── releases.php       # JSON API endpoint for widget status checks
├── assets/
│   ├── admin.css          # Stylesheet for the admin dashboard & login screen
│   ├── style.css          # Stylesheet for the public feed & single article view
│   └── widget.js          # Embeddable notification widget script
├── includes/
│   └── db.php             # Database connection handler
├── install/
│   └── index.php          # Automated web-based installation wizard
├── uploads/               # Directory for uploaded release banner images
├── index.php              # Public release notes feed (tiered layout)
├── view.php               # Single release detail view
└── README.md              # Project documentation
```

## How to Install

1. **Upload Files**: Upload the complete project directory to your web server running PHP 7.4+ with PDO and MySQL/MariaDB enabled.
2. **Run the Installer**: Open your web browser and navigate to the `install/` directory on your server:
   ```text
   [https://your-domain.com/install/]
   ```
3. **Complete the Setup Wizard**:
   - **Database Details**: Enter your MySQL Host, Database Name, Username, and Password.
   - **Application Settings**: Enter your application name (used across titles and public headers).
   - **Admin Account**: Set your secure administrator username and password.
4. **Automatic Configuration**: The installer validates the database connection, creates the `releases` and `admin_users` tables, and automatically generates your configuration files (`config.php` / connection settings).
5. **Post-Installation Security**: For security best practices, delete or restrict web access to the `install/` folder after completing setup.

---

## How to Use

### 1. Admin Management (`/admin/`)
- Navigate to `https://your-domain.com/admin/` and log in using your administrator credentials created during installation.
- **Publishing Releases**: Click **+ New Release**, fill out the title, select your update type (**Minor Update** for an unread badge notification dot, or **Major Update** for priority alerts), choose a custom publish date/time if desired, upload an optional banner image, and write your summary and content using the Trumbowyg editor.
- **Management Actions**: From the dashboard, you can **Edit**, **Duplicate**, or **Delete** any existing release.

### 2. Public Feed (`index.php`)
- Visitors view all updates at your main feed URL (`https://your-domain.com/index.php`). 
- The layout automatically adapts: the newest release is shown in full, the next 5 appear with summaries and "Read More" buttons, and older entries collapse into a clean chronological archive list.

### 3. Embedding the Widget on External Webpages
To display your release notification badge and interactive update modal on any external website (such as your main web application, landing page, or client portal):

1. **Copy the Code Snippet**:
   ```html
   <script src="[https://your-domain.com/assets/widget.js]"></script>
   ```
2. **Where to Paste It**: 
   Open the HTML template or layout file of your external website, locate the closing `</body>` tag (usually at the very bottom of your HTML document), and paste the script tag **immediately before** `</body>`:
   ```html
       <!-- Your website content -->
       
       <!-- Paste the Release Notes widget script right here -->
       <script src="[https://your-domain.com/assets/widget.js]"></script>
   </body>
   </html>
   ```
3. **Behavior**: The script will automatically inject a floating "What's New" button with an unread badge indicator and open your CMS feed inside a responsive modal iframe when clicked.
