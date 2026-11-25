<?php
session_start();
require_once 'config/database.php';

$student = null;
$error = "";

// Check whether this is a delete confirmation submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$id || $id <= 0) {
        die("Invalid student ID!");
    }

    try {
        $sql = "DELETE FROM students WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "Student deleted successfully!";
        } else {
            $_SESSION['success'] = "Student not found!";
        }

        header("Location: index.php");
        exit;

    } catch (PDOException $e) {
        die("Error deleting student: " . $e->getMessage());
    }
}

// Display confirmation page
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    die("Invalid student ID!");
}

try {
    $sql = "SELECT id, name, email, phone
            FROM students
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        die("Student not found!");
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Student</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="container">

    <div class="sidebar">
        <h2>Student Hub</h2>

        <a href="index.php">Dashboard</a>
        <a href="add_student.php">Add Student</a>
        <a href="view_students.php" class="active">View Students</a>
    </div>

    <div class="main-content">

        <div class="page-header">
            <h1>Delete Student</h1>
            <p>Review the student details before deleting</p>
        </div>

        <div class="form-container">

            <h2>Are you sure?</h2>

            <p>This action will permanently delete this student.</p>

            <p><strong>Student ID:</strong>
                <?= htmlspecialchars($student['id']) ?>
            </p>

            <p><strong>Name:</strong>
                <?= htmlspecialchars($student['name']) ?>
            </p>

            <p><strong>Email:</strong>
                <?= htmlspecialchars($student['email']) ?>
            </p>

            <p><strong>Phone:</strong>
                <?= htmlspecialchars($student['phone'] ?? '') ?>
            </p>

            <form method="POST">

                <input type="hidden" name="id"
                       value="<?= htmlspecialchars($student['id']) ?>">

                <button type="submit" class="delete-button">
                    Yes, Delete Student
                </button>

                <a href="view_students.php" class="back-link">
                    Cancel
                </a>

            </form>

        </div>

    </div>

</div>

</body>
</html>