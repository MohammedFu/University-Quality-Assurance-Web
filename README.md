# University Quality Assurance Web Application

The **University Quality Assurance Web Application** is a comprehensive PHP-based system designed to streamline and manage the quality assurance processes within a university. It tracks learning outcomes (LO), student attendance, lecturer courses, topics, and assessments to ensure high academic standards.

## 🚀 Features

- **User Authentication**: Secure login and password recovery for administrators and lecturers.
- **Interactive Dashboards**: Overview of statistics, courses, and active quality assurance metrics.
- **Learning Outcomes (LO) Management**: Dedicated dashboard and filters to track and evaluate student performance against specific learning outcomes.
- **Attendance Tracking**: Manage and monitor student attendance across various lectures and courses.
- **Course & Lecturer Management**: Assign courses to lecturers and manage academic years, colleges, and majors.
- **Assessments & Questions**: Create, manage, and link examination questions to course topics and learning outcomes to track specific educational goals.

## 🛠️ Technology Stack

- **Backend**: PHP 8.x
- **Database**: MySQL (via phpMyAdmin)
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling / UI Framework**: Bootstrap 5, SASS
- **Libraries/Plugins**: OwlCarousel, TempusDominus, Waypoints, Chart.js

## 📁 Project Structure

```text
/
├── api/                   # API endpoints for frontend AJAX requests
├── css/ & scss/           # Stylesheets and SASS source files
├── img/                   # Image assets and avatars
├── includes/              # Reusable PHP components (header, footer, auth, db connection)
├── js/ & lib/             # JavaScript files and external vendor libraries
├── *.php                  # Main application pages (dashboard, attendance, login, etc.)
└── *.sql                  # Database schemas, procedures, and seed data
```

## ⚙️ Installation & Setup

To run this project locally, you will need a local server environment like **WAMP**, **XAMPP**, or **MAMP**.

1. **Clone the repository**
   ```bash
   git clone git@github.com:MohammedFu/University-Quality-Assurance-Web.git
   ```

2. **Move to Server Directory**
   Move the project folder into your local server's root directory:
   - For WAMP: `C:\wamp64\www\university_quality_assurance`
   - For XAMPP: `C:\xampp\htdocs\university_quality_assurance`

3. **Database Setup**
   - Open **phpMyAdmin** (usually `http://localhost/phpmyadmin`).
   - Create a new database named `university_quality_assurance`.
   - Import the `university_quality_assurance.sql` file provided in the root directory.
   - *(Optional)* Execute any other `.sql` fix/seed scripts if you need dummy data or schema updates.

4. **Configure Database Connection**
   - Open `includes/db.php`.
   - Ensure the database credentials match your local setup (default is usually `root` with no password).

5. **Run the Application**
   - Open your web browser and navigate to: `http://localhost/university_quality_assurance/login.php`
   - Log in using one of the administrator credentials provided in the database (e.g., `johndoe@example.com` / `password`).

## 📄 License

This project is licensed under the MIT License.
