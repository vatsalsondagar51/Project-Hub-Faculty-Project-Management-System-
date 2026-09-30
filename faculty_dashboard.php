<?php
require_once "auth.php";
require_faculty();

$result = mysqli_query(
    $conn,
    "SELECT p.*, s.name AS student_name, s.roll_no, s.email AS student_email
     FROM projects p
     JOIN students s ON s.id = p.student_id
     ORDER BY p.submitted_at DESC"
);
$count = mysqli_num_rows($result);

$messages = [
    "registered" => "Welcome! Your faculty account is ready.",
    "evaluated"  => "Status and marks saved.",
    "deleted"    => "Project deleted."
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard — Project Hub</title>
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
            <span class="user-chip">Hi, <?php echo htmlspecialchars(current_user_name()); ?></span>
            <a href="logout.php" class="btn btn-ghost">Logout</a>
        </nav>
    </header>

    <main class="container">
        <section class="hero">
            <p class="eyebrow">Faculty side</p>
            <h1>Review submissions</h1>
            <p class="lead">Set pending or completed status, award marks, or delete a project.</p>
            <div class="hero-actions">
                <span class="stat-pill"><?php echo $count; ?> project<?php echo $count !== 1 ? "s" : ""; ?></span>
            </div>
        </section>

        <?php if (isset($_GET["msg"]) && isset($messages[$_GET["msg"]])): ?>
            <div class="alert alert-success" id="flash"><?php echo htmlspecialchars($messages[$_GET["msg"]]); ?></div>
        <?php endif; ?>

        <section class="list-section">
            <div class="section-head">
                <h2>All student projects</h2>
                <input type="search" id="searchBox" class="search" placeholder="Search by name, title, subject…" autocomplete="off">
            </div>

            <?php if ($count === 0): ?>
                <div class="empty">
                    <h3>No projects yet</h3>
                    <p>Students have not submitted any projects.</p>
                </div>
            <?php else: ?>
                <div class="project-grid" id="projectGrid">
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <article class="project-card"
                                 data-search="<?php echo htmlspecialchars(strtolower($row["student_name"] . " " . $row["project_title"] . " " . $row["subject"] . " " . $row["roll_no"] . " " . $row["status"])); ?>">
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
                                <div class="action-group">
                                    <a class="btn btn-primary btn-sm" href="evaluate.php?id=<?php echo (int)$row["id"]; ?>">Evaluate</a>
                                    <a class="btn btn-danger btn-sm"
                                       href="delete.php?id=<?php echo (int)$row["id"]; ?>"
                                       onclick="return confirmDelete()">Delete</a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                <p class="no-results" id="noResults" hidden>No projects match your search.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer class="site-footer"><p>Project Hub · Faculty dashboard</p></footer>
    <script src="assets/js/script.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
