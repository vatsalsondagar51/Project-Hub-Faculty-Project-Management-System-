<?php
require_once "auth.php";
require_student();

$student_id = (int)$_SESSION["user_id"];

$mine = mysqli_prepare(
    $conn,
    "SELECT p.*, s.name AS student_name, s.roll_no
     FROM projects p
     JOIN students s ON s.id = p.student_id
     WHERE p.student_id = ?
     ORDER BY p.submitted_at DESC"
);
mysqli_stmt_bind_param($mine, "i", $student_id);
mysqli_stmt_execute($mine);
$my_projects = mysqli_stmt_get_result($mine);
$my_count = mysqli_num_rows($my_projects);

$others = mysqli_prepare(
    $conn,
    "SELECT p.*, s.name AS student_name, s.roll_no
     FROM projects p
     JOIN students s ON s.id = p.student_id
     WHERE p.student_id != ?
     ORDER BY p.submitted_at DESC"
);
mysqli_stmt_bind_param($others, "i", $student_id);
mysqli_stmt_execute($others);
$other_projects = mysqli_stmt_get_result($others);
$other_count = mysqli_num_rows($other_projects);

$messages = [
    "registered" => "Welcome! Your student account is ready.",
    "added"      => "Project submitted successfully!",
    "updated"    => "Project updated successfully!"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard — Project Hub</title>
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
            <span class="user-chip">Hi, <?php echo htmlspecialchars(current_user_name()); ?></span>
            <a href="submit.php" class="btn btn-primary">Add Project</a>
            <a href="logout.php" class="btn btn-ghost">Logout</a>
        </nav>
    </header>

    <main class="container">
        <section class="hero">
            <p class="eyebrow">Student side</p>
            <h1>Your projects</h1>
            <p class="lead">Add details, update your submissions, and see what others have submitted.</p>
            <div class="hero-actions">
                <a href="submit.php" class="btn btn-primary">New submission</a>
                <span class="stat-pill"><?php echo $my_count; ?> of yours</span>
                <span class="stat-pill"><?php echo $other_count; ?> from others</span>
            </div>
        </section>

        <?php if (isset($_GET["msg"]) && isset($messages[$_GET["msg"]])): ?>
            <div class="alert alert-success" id="flash"><?php echo htmlspecialchars($messages[$_GET["msg"]]); ?></div>
        <?php endif; ?>

        <section class="list-section">
            <div class="section-head">
                <h2>My submissions</h2>
                <input type="search" id="searchMine" class="search" placeholder="Search my projects…" autocomplete="off">
            </div>

            <?php if ($my_count === 0): ?>
                <div class="empty">
                    <h3>No projects yet</h3>
                    <p>Submit your first project to get started.</p>
                    <a href="submit.php" class="btn btn-primary">Add Project</a>
                </div>
            <?php else: ?>
                <div class="project-grid" id="mineGrid">
                    <?php while ($row = mysqli_fetch_assoc($my_projects)): ?>
                        <article class="project-card"
                                 data-search="<?php echo htmlspecialchars(strtolower($row["project_title"] . " " . $row["subject"] . " " . $row["status"])); ?>">
                            <div class="card-top">
                                <span class="subject-tag"><?php echo htmlspecialchars($row["subject"]); ?></span>
                                <?php echo status_badge($row["status"]); ?>
                            </div>
                            <h3><?php echo htmlspecialchars($row["project_title"]); ?></h3>
                            <p class="desc"><?php echo htmlspecialchars($row["description"]); ?></p>
                            <div class="meta-row">
                                <span>Marks: <strong><?php echo format_marks($row["marks"]); ?></strong></span>
                                <time><?php echo date("d M Y", strtotime($row["submitted_at"])); ?></time>
                            </div>
                            <div class="card-foot">
                                <div class="student">
                                    <strong><?php echo htmlspecialchars($row["student_name"]); ?></strong>
                                    <span>Roll: <?php echo htmlspecialchars($row["roll_no"]); ?></span>
                                </div>
                                <a class="btn btn-primary btn-sm" href="edit.php?id=<?php echo (int)$row["id"]; ?>">Update</a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                <p class="no-results" id="noMine" hidden>No projects match your search.</p>
            <?php endif; ?>
        </section>

        <section class="list-section spaced">
            <div class="section-head">
                <h2>All other submissions</h2>
                <input type="search" id="searchOthers" class="search" placeholder="Search by name, title, subject…" autocomplete="off">
            </div>

            <?php if ($other_count === 0): ?>
                <div class="empty">
                    <h3>No other projects yet</h3>
                    <p>When classmates submit, their projects will appear here.</p>
                </div>
            <?php else: ?>
                <div class="project-grid" id="othersGrid">
                    <?php while ($row = mysqli_fetch_assoc($other_projects)): ?>
                        <article class="project-card"
                                 data-search="<?php echo htmlspecialchars(strtolower($row["student_name"] . " " . $row["project_title"] . " " . $row["subject"] . " " . $row["roll_no"])); ?>">
                            <div class="card-top">
                                <span class="subject-tag"><?php echo htmlspecialchars($row["subject"]); ?></span>
                                <?php echo status_badge($row["status"]); ?>
                            </div>
                            <h3><?php echo htmlspecialchars($row["project_title"]); ?></h3>
                            <p class="desc"><?php echo htmlspecialchars($row["description"]); ?></p>
                            <div class="meta-row">
                                <span>Marks: <strong><?php echo format_marks($row["marks"]); ?></strong></span>
                                <time><?php echo date("d M Y", strtotime($row["submitted_at"])); ?></time>
                            </div>
                            <div class="card-foot">
                                <div class="student">
                                    <strong><?php echo htmlspecialchars($row["student_name"]); ?></strong>
                                    <span>Roll: <?php echo htmlspecialchars($row["roll_no"]); ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                <p class="no-results" id="noOthers" hidden>No projects match your search.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer class="site-footer"><p>Project Hub · Student dashboard</p></footer>
    <script src="assets/js/script.js"></script>
</body>
</html>
<?php
mysqli_stmt_close($mine);
mysqli_stmt_close($others);
mysqli_close($conn);
?>
