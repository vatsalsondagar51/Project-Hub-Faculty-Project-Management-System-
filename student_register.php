<?php
require_once "auth.php";

if (is_student_logged_in()) {
    header("Location: student_dashboard.php");
    exit;
}

$errors = [];
$old = ["name" => "", "email" => "", "roll_no" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $old["name"]    = trim($_POST["name"] ?? "");
    $old["email"]   = trim($_POST["email"] ?? "");
    $old["roll_no"] = trim($_POST["roll_no"] ?? "");
    $password       = $_POST["password"] ?? "";
    $confirm        = $_POST["confirm_password"] ?? "";

    if ($old["name"] === "") $errors[] = "Name is required.";
    if ($old["email"] === "" || !filter_var($old["email"], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    if ($old["roll_no"] === "") $errors[] = "Roll number is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        $check = mysqli_prepare($conn, "SELECT id FROM students WHERE email = ? OR roll_no = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "ss", $old["email"], $old["roll_no"]);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        if (mysqli_stmt_num_rows($check) > 0) {
            $errors[] = "Email or roll number already registered.";
        }
        mysqli_stmt_close($check);
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO students (name, email, roll_no, password) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $old["name"], $old["email"], $old["roll_no"], $hash);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION["user_type"] = "student";
            $_SESSION["user_id"]   = mysqli_insert_id($conn);
            $_SESSION["user_name"] = $old["name"];
            mysqli_stmt_close($stmt);
            header("Location: student_dashboard.php?msg=registered");
            exit;
        }
        $errors[] = "Registration failed. Please try again.";
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Register — Project Hub</title>
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
            <a href="student_login.php" class="nav-link">Student login</a>
            <a href="index.php" class="btn btn-ghost">Home</a>
        </nav>
    </header>

    <main class="container narrow auth-shell">
        <section class="hero compact">
            <p class="eyebrow">Student account</p>
            <h1>Create student account</h1>
            <p class="lead">Register to submit projects, update your work, and view all submissions.</p>
        </section>

        <?php if (!empty($errors)): ?>
            <div id="jsAlert" hidden data-message="<?php echo htmlspecialchars(implode(' ', $errors)); ?>"></div>
        <?php endif; ?>

        <form class="form-panel" id="registerForm" method="POST" action="student_register.php">
            <div class="field">
                <label for="name">Full name *</label>
                <input type="text" id="name" name="name" maxlength="100" value="<?php echo htmlspecialchars($old["name"]); ?>" required>
            </div>
            <div class="form-row">
                <div class="field">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" maxlength="120" value="<?php echo htmlspecialchars($old["email"]); ?>" required>
                </div>
                <div class="field">
                    <label for="roll_no">Roll number *</label>
                    <input type="text" id="roll_no" name="roll_no" maxlength="50" value="<?php echo htmlspecialchars($old["roll_no"]); ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" minlength="6" required>
                </div>
                <div class="field">
                    <label for="confirm_password">Confirm password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
                </div>
            </div>
            <div class="form-actions">
                <a href="student_login.php" class="btn btn-ghost">Already have an account?</a>
                <button type="submit" class="btn btn-primary">Register</button>
            </div>
        </form>
    </main>

    <footer class="site-footer"><p>Project Hub · Student registration</p></footer>
    <script src="assets/js/script.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
