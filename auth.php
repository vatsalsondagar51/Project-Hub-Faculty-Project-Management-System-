<?php
require_once __DIR__ . "/config.php";

function is_student_logged_in(): bool
{
    return isset($_SESSION["user_type"], $_SESSION["user_id"]) && $_SESSION["user_type"] === "student";
}

function is_faculty_logged_in(): bool
{
    return isset($_SESSION["user_type"], $_SESSION["user_id"]) && $_SESSION["user_type"] === "faculty";
}

function require_student(): void
{
    if (!is_student_logged_in()) {
        header("Location: student_login.php");
        exit;
    }
}

function require_faculty(): void
{
    if (!is_faculty_logged_in()) {
        header("Location: faculty_login.php");
        exit;
    }
}

function current_user_name(): string
{
    return $_SESSION["user_name"] ?? "User";
}

function status_badge(string $status): string
{
    $class = $status === "completed" ? "badge-completed" : "badge-pending";
    return '<span class="badge ' . $class . '">' . htmlspecialchars(ucfirst($status)) . '</span>';
}

function format_marks($marks): string
{
    if ($marks === null || $marks === "") {
        return "—";
    }
    return number_format((float)$marks, 1);
}
?>
