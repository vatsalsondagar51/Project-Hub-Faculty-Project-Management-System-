<?php
require_once "auth.php";
require_faculty();

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$errors = [];

$stmt = mysqli_prepare(
    $conn,
    "SELECT p.*, s.name AS student_name, s.roll_no
     FROM projects p
     JOIN students s ON s.id = p.student_id
     WHERE p.id = ?
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$project = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$project) {
    header("Location: faculty_dashboard.php");
    exit;
}

$status = $project["status"];
$marks  = $project["marks"] !== null ? (string)$project["marks"] : "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $status = trim($_POST["status"] ?? "");
    $marks  = trim($_POST["marks"] ?? "");

    if (!in_array($status, ["pending", "completed"], true)) {
        $errors[] = "Status must be pending or completed.";
    }

    $marks_value = null;
    if ($marks !== "") {
        if (!is_numeric($marks) || (float)$marks < 0 || (float)$marks > 100) {
            $errors[] = "Marks must be a number between 0 and 100.";
        } else {
            $marks_value = (float)$marks;
        }
    }

    if (empty($errors)) {
        if ($marks_value === null) {
            $update = mysqli_prepare($conn, "UPDATE projects SET status = ?, marks = NULL WHERE id = ?");
            mysqli_stmt_bind_param($update, "si", $status, $id);
        } else {
            $update = mysqli_prepare($conn, "UPDATE projects SET status = ?, marks = ? WHERE id = ?");
            mysqli_stmt_bind_param($update, "sdi", $status, $marks_value, $id);
        }

        if (mysqli_stmt_execute($update)) {
            mysqli_stmt_close($update);
            header("Location: faculty_dashboard.php?msg=evaluated");
            exit;
        }
        $errors[] = "Could not save evaluation.";
        mysqli_stmt_close($update);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluate Project — Project Hub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,500;8..60,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="bg-shape"></div>
    <div class="bg-shape-2"></div>

    <header class="site-header">
        <a href="faculty_dashboard.php" class="brand">
            <span class="brand-mark">PH</span>
            <span class="brand-name">Project Hub</span>
        </a>
        <nav>
            <a href="faculty_dashboard.php" class="nav-link">Dashboard</a>
            <a href="logout.php" class="btn btn-ghost">Logout</a>
        </nav>
    </header>

    <main class="container narrow">
        <section class="hero compact">
            <p class="eyebrow">Evaluation</p>
            <h1>Set status & marks</h1>
            <p class="lead">Update whether this project is pending or completed, and enter marks out of 100.</p>
        </section>

        <div class="form-panel preview-panel">
            <div class="card-top">
                <span class="subject-tag"><?php echo htmlspecialchars($project["subject"]); ?></span>
                <?php echo status_badge($project["status"]); ?>
            </div>
            <h3><?php echo htmlspecialchars($project["project_title"]); ?></h3>
            <p class="desc full"><?php echo htmlspecialchars($project["description"]); ?></p>
            <div class="student">
                <strong><?php echo htmlspecialchars($project["student_name"]); ?></strong>
                <span>Roll: <?php echo htmlspecialchars($project["roll_no"]); ?></span>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form class="form-panel" method="POST" action="evaluate.php?id=<?php echo $id; ?>">
            <div class="form-row">
                <div class="field">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="pending" <?php echo $status === "pending" ? "selected" : ""; ?>>Pending</option>
                        <option value="completed" <?php echo $status === "completed" ? "selected" : ""; ?>>Completed</option>
                    </select>
                </div>
                <div class="field">
                    <label for="marks">Marks (0–100)</label>
                    <input type="number" id="marks" name="marks" min="0" max="100" step="0.5"
                           value="<?php echo htmlspecialchars($marks); ?>" placeholder="Leave blank if not graded">
                </div>
            </div>

            <div class="form-actions">
                <a href="faculty_dashboard.php" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Save evaluation</button>
            </div>
        </form>
    </main>

    <footer class="site-footer"><p>Project Hub · Evaluate project</p></footer>
</body>
</html>
<?php mysqli_close($conn); ?>
