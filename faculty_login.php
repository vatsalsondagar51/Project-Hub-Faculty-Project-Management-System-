<?php
require_once "auth.php";

if (is_faculty_logged_in()) {
    header("Location: faculty_dashboard.php");
    exit;
}

$errors = [];
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $errors[] = "Email and password are required.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, name, password FROM faculty WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_type"] = "faculty";
            $_SESSION["user_id"]   = (int)$user["id"];
            $_SESSION["user_name"] = $user["name"];
            header("Location: faculty_dashboard.php");
            exit;
        }
        $errors[] = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Login — Project Hub</title>
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
        <nav>
            <a href="faculty_register.php" class="nav-link">Register</a>
            <a href="index.php" class="btn btn-ghost">Home</a>
        </nav>
    </header>

    <main class="container narrow auth-shell">
        <section class="hero compact">
            <p class="eyebrow">Faculty portal</p>
            <h1>Faculty login</h1>
            <p class="lead">Sign in to evaluate and manage student projects.</p>
        </section>

        <?php if (!empty($errors)): ?>
            <div id="jsAlert" hidden data-message="<?php echo htmlspecialchars(implode(' ', $errors)); ?>"></div>
        <?php endif; ?>

        <form class="form-panel" id="loginForm" method="POST" action="faculty_login.php">
            <div class="field">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="field">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-actions">
                <a href="faculty_register.php" class="btn btn-ghost">Create account</a>
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
    </main>

    <footer class="site-footer"><p>Project Hub · Faculty login</p></footer>
    <script src="assets/js/script.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
