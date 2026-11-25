<?php
session_start();
require_once 'config/database.php';

$message = "";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    die("Invalid student ID.");
}

// Fetch existing student
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
$stmt->execute([':id' => $id]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found.");
}

// Update student
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $course = trim($_POST['course'] ?? '');

    // Preserve entered values if validation fails
    $student['name'] = $name;
    $student['email'] = $email;
    $student['phone'] = $phone;
    $student['course'] = $course;

    // Validate name
    if ($name === '') {

        $message = "Please enter the student's name.";

    // Validate email
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    // Validate phone if provided
    } elseif (
        $phone !== '' &&
        !preg_match('/^(?:\+91)?[6-9][0-9]{9}$/',
        preg_replace('/[\s-]/', '', $phone))
    ) {

        $message = "Please enter a valid 10-digit Indian mobile number.";

    } else {

        try {

            $sql = "UPDATE students
                    SET name = :name,
                        email = :email,
                        phone = :phone,
                        course=:course
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':course'=> $course,
                ':id' => $id
            ]);

            $_SESSION['success'] = "Student updated successfully!";

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {

            if ($e->getCode() == '23505') {
                $message = "This email is already registered!";
            } else {
                $message = "Something went wrong. Please try again.";
            }

        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<div class="container">

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Student Hub</h2>

        <a href="index.php">Dashboard</a>
        <a href="add_student.php">Add Student</a>
        <a href="view_students.php" class="active">View Students</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">

        <div class="page-header">
            <h1>Edit Student</h1>
            <p>Update the student's information below</p>
        </div>

        <div class="form-container">

            <h2>Student Information</h2>
            <p>Make the necessary changes and save the details.</p>

            <?php if (!empty($message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <label>Full Name</label>
                <input type="text" name="name"
                value="<?php echo htmlspecialchars($student['name']); ?>" required>

                <label>Email Address</label>
                <input type="email" name="email"
                value="<?php echo htmlspecialchars($student['email']); ?>" required>

                <label>Phone Number</label>
                <input type="text" name="phone"
                value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>">

                <label>Course</label>
                <input type="text" name="course"
                value="<?php echo htmlspecialchars($student['course'] ?? ''); ?>">

                <button type="submit">Update Student</button>

                <a href="view_students.php" class="back-link">Cancel</a>

            </form>

        </div>

    </div>

</div>

</body>
</html>