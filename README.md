# 🎓 Digital Classroom Manager

An enterprise-grade, full-stack educational dashboard built with robust security, a modern glassmorphism UI, and powerful role-based features. Designed to completely streamline how schools manage assignments, notes, and students.

![Project Preview Banner](https://images.unsplash.com/photo-1501504905252-473c47e087f8?auto=format&fit=crop&q=80&w=1200&h=400)

## ✨ Key Features

### 🛡️ Uncompromising Security
- **OTP Verification**: Secure 2-step registration workflow using `PHPMailer` to aggressively prevent bot accounts.
- **CSRF Protection**: Native cryptographic tokens injected into *all* forms to prevent Cross-Site Request Forgery.
- **SQLi Defense**: Pure parameterized query integration (Prepared Statements) mapping across the entire database.
- **Strict File Validation**: Enforces MIME-type (`finfo`) introspection magic byte checks to safely upload `.pdf`, `.doc`, `.docx`, and `.zip` files while rejecting malicious `.php`/`.exe` masquerades.
- **Intelligent Authorization**: Regex-enforced strong passwords alongside comprehensive session controls.

### 👥 Role-Based Architecture
- **Admin**: Total system oversight. Can enable, disable, and gracefully delete users with dynamic foreign-key decoupling.
- **Teacher**: Full content creation suite featuring **Quill.js** for Rich-Text editing and comprehensive student submission grading interfaces.
- **Student**: Focused learning dashboard tailored precisely by their Academic Branch, Year, Mode (Regular/Self-Finance), and Semester. Features real-time notifications for upcoming homework deadlines.

### 🎨 Modern UI/UX
- **Glassmorphism Aesthetics**: Built with premium custom CSS utilizing frosted-glass app cards, soft shadows, and deep violet/indigo gradients.
- **Responsive Architecture**: Flexbox architectures tailored dynamically across desktop and mobile screens.
- **Dynamic Interactions**: Features micro-animations, loading states, and dynamic JavaScript form toggles (hiding student forms when Teacher is selected).


## 💻 Technology Stack
- **Frontend**: HTML5, CSS3 (Vanilla), JavaScript (Vanilla), Quill.js (CDN).
- **Backend**: Core PHP 8+
- **Database**: MySQL (Relational Schema)
- **Email Server**: SMTP via PHPMailer

## 🚀 Setting Up Locally

Want to run this project on your local machine?

1. **Clone the Repo**
   ```bash
   git clone https://github.com/kmalik6811-cmd/digital-classroom.git
   ```
2. **Move to Server Directory**
   Move the project into your local server (e.g., inside `htdocs` for XAMPP or `www` for WAMP).

3. **Setup Database**
   - Head over to `phpMyAdmin`.
   - Run the provided `database_setup.sql` script to automatically generate the relational database structure.
   
4. **Configure Database Credentials**
   - Open `config/db.php`.
   - Adjust the credentials (`$host`, `$user`, `$pass`, `$db`) to match your server configuration.

5. **Start Learning**
   - Access the platform locally via your browser (e.g., `http://localhost/digital_classroom/`).

---

*Engineered with clean code practices (DRY architecture via included headers/sidebars) and tailored for educational excellence.*
