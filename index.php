<?php
require_once "config.php";

if (isset($_SESSION["user_type"])) {
    if ($_SESSION["user_type"] === "student") {
        header("Location: student_dashboard.php");
        exit;
    }
    if ($_SESSION["user_type"] === "faculty") {
        header("Location: faculty_dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Hub — Student & Faculty Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,500;8..60,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="bg-shape"></div>
    <div class="bg-shape-2"></div>

    <header class="site-header">
        <a href="index.php" class="brand">
            <span class="brand-mark">PH</span>
            <span class="brand-name">Project Hub</span>
        </a>
    </header>

    <main class="container landing-wrap">
        <section class="hero-panel hero center">
            <p class="eyebrow">College project portal</p>
            <h1 class="brand-hero">Project Hub</h1>
            <p class="lead">One place for students to submit work and for faculty to review, grade, and track every project.</p>
        </section>

        <section class="portal-grid">
            <a href="student_login.php" class="portal-card">
                <span class="portal-icon">S</span>
                <h2>Student</h2>
                <p>Register, submit projects, update your work, and browse all submissions.</p>
                <span class="portal-cta">Enter as student →</span>
            </a>
            <a href="faculty_login.php" class="portal-card faculty">
                <span class="portal-icon">F</span>
                <h2>Faculty</h2>
                <p>Review all projects, set pending or completed status, award marks, or delete entries.</p>
                <span class="portal-cta">Enter as faculty →</span>
            </a>
        </section>
    </main>

    <footer class="site-footer">
        <p>Project Hub · PHP · MySQL · XAMPP</p>
    </footer>
</body>
</html>
