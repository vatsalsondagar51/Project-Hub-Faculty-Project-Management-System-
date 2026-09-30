<?php
require_once "auth.php";
require_student();

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$student_id = (int)$_SESSION["user_id"];
$errors = [];

$stmt = mysqli_prepare($conn, "SELECT * FROM projects WHERE id = ? AND student_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "ii", $id, $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$project = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$project) {
    header("Location: student_dashboard.php");
    exit;
}

$old = [
    "project_title" => $project["project_title"],
    "subject"       => $project["subject"],
    "description"   => $project["description"]
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
        $update = mysqli_prepare(
            $conn,
            "UPDATE projects SET project_title = ?, subject = ?, description = ? WHERE id = ? AND student_id = ?"
        );
        mysqli_stmt_bind_param(
            $update,
            "sssii",
            $old["project_title"],
            $old["subject"],
            $old["description"],
            $id,
            $student_id
        );

        if (mysqli_stmt_execute($update)) {
            mysqli_stmt_close($update);
            header("Location: student_dashboard.php?msg=updated");
            exit;
        }
        $errors[] = "Could not update. Please try again.";
        mysqli_stmt_close($update);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Project — Project Hub</title>
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
            <p class="eyebrow">Edit submission</p>
            <h1>Update project details</h1>
            <p class="lead">Change title, subject, or description. Status and marks are set by faculty.</p>
        </section>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form class="form-panel" id="submitForm" method="POST" action="edit.php?id=<?php echo $id; ?>" novalidate>
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

            <div class="info-strip">
                Current status: <?php echo status_badge($project["status"]); ?>
                &nbsp;·&nbsp; Marks: <strong><?php echo format_marks($project["marks"]); ?></strong>
            </div>

            <div class="form-actions">
                <a href="student_dashboard.php" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
    </main>

    <footer class="site-footer"><p>Project Hub · Update project</p></footer>
    <script src="assets/js/script.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
