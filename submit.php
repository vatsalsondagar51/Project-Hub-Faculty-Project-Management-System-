<?php
require_once "auth.php";
require_student();

$errors = [];
$old = [
    "project_title" => "",
    "subject"       => "",
    "description"   => ""
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $old["project_title"] = trim($_POST["project_title"] ?? "");
    $old["subject"]       = trim($_POST["subject"] ?? "");
    $old["description"]   = trim($_POST["description"] ?? "");

    if ($old["project_title"] === "") $errors[] = "Project title is required.";
    if ($old["subject"] === "")       $errors[] = "Subject is required.";
    if ($old["description"] === "")   $errors[] = "Description is required.";
    if (strlen($old["description"]) < 20) $errors[] = "Description should be at least 20 characters.";

    if (empty($errors)) {
        $student_id = (int)$_SESSION["user_id"];
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO projects (student_id, project_title, subject, description) VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "isss",
            $student_id,
            $old["project_title"],
            $old["subject"],
            $old["description"]
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: student_dashboard.php?msg=added");
            exit;
        }
        $errors[] = "Could not save. Please try again.";
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Project — Project Hub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,500;8..60,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="bg-shape"></div>
    <div class="bg-shape-2"></div>

    <header class="site-header">
        <a href="student_dashboard.php" class="brand">
            <span class="brand-mark">PH</span>
            <span class="brand-name">Project Hub</span>
        </a>
        <nav>
            <a href="student_dashboard.php" class="nav-link">Dashboard</a>
            <a href="logout.php" class="btn btn-ghost">Logout</a>
        </nav>
    </header>

    <main class="container narrow">
        <section class="hero compact">
            <p class="eyebrow">New entry</p>
            <h1>Add project details</h1>
            <p class="lead">Your name and roll number come from your student account automatically.</p>
        </section>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form class="form-panel" id="submitForm" method="POST" action="submit.php" novalidate>
            <div class="field">
                <label for="project_title">Project title *</label>
                <input type="text" id="project_title" name="project_title" maxlength="200"
                       value="<?php echo htmlspecialchars($old["project_title"]); ?>" required>
                <span class="field-error" data-for="project_title"></span>
            </div>

            <div class="field">
                <label for="subject">Subject *</label>
                <select id="subject" name="subject" required>
                    <option value="">Select subject</option>
                    <?php
                    $subjects = ["Web Development", "Database Systems", "Programming", "Networking", "Other"];
                    foreach ($subjects as $s) {
                        $sel = ($old["subject"] === $s) ? "selected" : "";
                        echo "<option value=\"" . htmlspecialchars($s) . "\" $sel>" . htmlspecialchars($s) . "</option>";
                    }
                    ?>
                </select>
                <span class="field-error" data-for="subject"></span>
            </div>

            <div class="field">
                <label for="description">Short description *</label>
                <textarea id="description" name="description" rows="5" maxlength="1000" required><?php echo htmlspecialchars($old["description"]); ?></textarea>
                <div class="field-meta">
                    <span class="field-error" data-for="description"></span>
                    <span class="char-count"><span id="charCount">0</span>/1000</span>
                </div>
            </div>

            <div class="form-actions">
                <a href="student_dashboard.php" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Submit project</button>
            </div>
        </form>
    </main>

    <footer class="site-footer"><p>Project Hub · Add project</p></footer>
    <script src="assets/js/script.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
