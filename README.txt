============================================================
Releev Recruitment — Full Website + Backend
============================================================

WHAT YOU'VE GOT
---------------
Frontend:    7 HTML pages (Home, About, Services, Employers,
             Candidates, Jobs, Contact) — visually unchanged
Backend:     PHP/MySQL API for forms + admin + employer area
Forms work:  Contact, Candidate CV, Job Application, Employer Job Posting
Emails:      Sent via Gmail SMTP (PHPMailer) for contact, candidate, application
Admin:       Login-protected dashboard at /admin/
Employer:    Login-protected area at /employer/

============================================================
INSTALLATION ON XAMPP — STEP BY STEP
============================================================

STEP 1 — Copy files
    Copy the entire "surely-sci-tech" folder into:
    C:\xampp\htdocs\           (Windows)
    /Applications/XAMPP/htdocs/  (Mac)

STEP 2 — Start Apache + MySQL in XAMPP Control Panel

STEP 3 — Create the database
    Open: http://localhost/phpmyadmin
    Click "Import" tab > Choose File > select "database.sql"
    Click "Go" — this creates the database "releev_recruit" with all tables

STEP 4 — Download PHPMailer (REQUIRED for emails)
    Go to: https://github.com/PHPMailer/PHPMailer/releases
    Download the latest "Source code (zip)"
    Extract the ZIP. Copy these 3 files:
      - src/PHPMailer.php
      - src/SMTP.php
      - src/Exception.php
    Into the folder:
      releev-recruit/includes/PHPMailer/

    Final structure should look like:
      releev-recruit/includes/PHPMailer/PHPMailer.php
      releev-recruit/includes/PHPMailer/SMTP.php
     releev-recruit/includes/PHPMailer/Exception.php

STEP 5 — Configure Gmail SMTP
    1. Go to Google Account > Security > 2-Step Verification (must be ON)
    2. Go to: https://myaccount.google.com/apppasswords
    3. Create an app password (16 characters)
    4. Open db.php and edit these lines:
         define('ADMIN_EMAIL', 'your-admin-email@example.com');
         define('SMTP_USER',   'your.gmail@gmail.com');
         define('SMTP_PASS',   'YOUR16CHARAPPPW');
         define('SMTP_FROM',   'your.gmail@gmail.com');

STEP 6 — Set folder permissions (Mac/Linux only)
    chmod 755 uploads/cvs uploads/applications

STEP 7 — Test it!
    Open: http://localhost/releev-recruit/
    Submit the contact form, candidate form, and job application
    Check your admin email for notifications

============================================================
DEFAULT ADMIN LOGIN
============================================================
URL:       http://localhost/releev-recruit/admin/
Username:  admin
Password:  admin123

⚠️  CHANGE THIS PASSWORD IMMEDIATELY after first login.
   To change: log in, go to phpMyAdmin, run this SQL
   (replace YOURNEWPASSWORD with what you want):

   UPDATE admins SET password_hash = '$2y$10$...generate...'
   WHERE username = 'admin';

   To generate a new password hash, use this URL while logged in:
   http://localhost/releev-recruith/admin/change-password.php
   (or temporarily create a quick script with password_hash())

============================================================
URLS
============================================================
Public site:        http://localhost/releev-recruit/
Admin login:        http://localhost/releev-recruit/admin/login.php
Employer login:     http://localhost/releev-recruit/employer/login.php
Employer register:  http://localhost/releev-recruit/employer/register.php

============================================================
HOW IT ALL WORKS
============================================================

PUBLIC FLOWS:
  1. Contact form → api/contact.php
     → Saves to DB → emails admin → emails confirmation to visitor
  2. Candidate CV → api/candidate.php
     → Saves to DB + uploads/cvs/ → emails admin → confirms candidate
  3. Job application → api/apply.php
     → Saves to DB + uploads/applications/ → emails admin → confirms applicant
  4. Jobs page → api/jobs-public.php
     → Loads all approved jobs as JSON, rendered with JS

EMPLOYER FLOWS:
  1. Register at /employer/register.php
  2. Log in
  3. Post job → goes to DB with status='pending'
  4. Admin reviews → approves or rejects
  5. Approved jobs appear on public /jobs.html

ADMIN FLOWS (at /admin/):
  - Dashboard:    overview stats
  - Messages:     view contact submissions, mark read, reply
  - Candidates:   view CVs, download files
  - Applications: view + manage application statuses
  - Jobs:         create/edit/delete any job
  - Approve Jobs: approve/reject employer-submitted jobs
  - Employers:    manage employer accounts

============================================================
DATABASE TABLES
============================================================
admins          — admin login accounts
employers       — employer login accounts
jobs            — all job listings (with status pending/approved/rejected/closed)
contacts        — contact form submissions
candidates      — candidate CV submissions
applications    — job applications (linked to job_id)

============================================================
FILE STRUCTURE
============================================================
releev-recruit/
├── index.html, about.html, services.html, employers.html,
│   candidates.html, jobs.html, contact.html — public pages
├── style.css                    — site styling
├── db.php                       — DB config + helpers (EDIT THIS)
├── database.sql                 — schema (import once)
│
├── api/
│   ├── contact.php              — contact form handler
│   ├── candidate.php            — CV submission handler
│   ├── apply.php                — job application handler
│   ├── submit-job.php           — employer job submit
│   └── jobs-public.php          — public jobs JSON feed
│
├── admin/
│   ├── login.php, logout.php, dashboard.php
│   ├── messages.php, candidates.php, applications.php
│   ├── jobs.php, approve-jobs.php, employers.php
│
├── employer/
│   ├── register.php, login.php, logout.php
│   ├── dashboard.php, post-job.php, my-jobs.php
│
├── includes/
│   ├── mailer.php               — email helper
│   └── PHPMailer/               — DOWNLOAD MANUALLY (step 4)
│
└── uploads/
    ├── cvs/                     — candidate CVs go here
    └── applications/            — application CVs go here

============================================================
TROUBLESHOOTING
============================================================

"Failed to connect to database":
  → Check db.php DB_USER (root) and DB_PASS (empty for XAMPP)
  → Make sure MySQL is running in XAMPP

Emails not sending:
  → Make sure PHPMailer files are in includes/PHPMailer/
  → Verify Gmail App Password is correct in db.php
  → Gmail must have 2-Step Verification enabled
  → Check XAMPP error log: xampp/apache/logs/error.log

Forms show "Network error":
  → Open browser console (F12) — look for actual error
  → Check api/contact.php works directly: open it in browser, should
    return {"success":false,"message":"Invalid request method."}

CV uploads not saving:
  → Make sure uploads/cvs/ and uploads/applications/ folders exist
  → On Linux/Mac: chmod 755 these folders

Jobs page is empty:
  → Check that database.sql was imported (sample jobs included)
  → Status must be 'approved' for jobs to appear

============================================================
SUPPORT NOTES
============================================================
This site is configured for LOCAL development on XAMPP.

To deploy to GoDaddy or any production server:
  1. Update SITE_URL in db.php to your real domain
  2. Update DB credentials to your hosting MySQL info
  3. Re-import database.sql via your host's phpMyAdmin
  4. Upload all files via FTP / GoDaddy File Manager
  5. Make sure /uploads/ folder permissions are 755
  6. Test all forms

============================================================

