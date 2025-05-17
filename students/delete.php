<?php
// students/delete.php
include_once "../config/database.php";

session_start();

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Check if an ID was passed
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['delete_message'] = "Invalid student ID.";
    $_SESSION['delete_message_class'] = "alert-danger";
    header("Location: read.php");
    exit();
}

$id = $_GET['id'];

// Verify student exists
$query = "SELECT id FROM students WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);

if ($stmt->rowCount() == 0) {
    $_SESSION['delete_message'] = "Student not found.";
    $_SESSION['delete_message_class'] = "alert-danger";
    header("Location: read.php");
    exit();
}

// Delete student
$query = "DELETE FROM students WHERE id = ?";
$stmt = $db->prepare($query);

if ($stmt->execute([$id])) {
    $_SESSION['delete_message'] = "Student deleted successfully.";
    $_SESSION['delete_message_class'] = "alert-success";
} else {
    $_SESSION['delete_message'] = "Failed to delete student. Please try again.";
    $_SESSION['delete_message_class'] = "alert-danger";
}

header("Location: read.php");
exit();
?>