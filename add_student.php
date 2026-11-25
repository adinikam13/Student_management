<?php
session_start();

require_once 'config/database.php';
require_once 'config/csrf.php';

$csrfToken = generateCsrfToken();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $message = "Security token expired. Please refresh the page.";
    } else {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    // Check empty fields
    if ($name === '' || $email === '' || $password === '' || $phone === '') {

        $message = "Please fill all fields.";

    // Validate email
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    // Validate phone number
    } elseif (!preg_match('/^(?:\+91)?[6-9][0-9]{9}$/',
        preg_replace('/[\s-]/', '', $phone))) {

        $message = "Please enter a valid 10-digit Indian mobile number.";

    // Validate password
    } elseif (strlen($password) < 8) {

        $message = "Password must contain at least 8 characters.";

    } else {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO students (name, email, password, phone)
                VALUES (:name, :email, :password, :phone)";

        $stmt = $pdo->prepare($sql);

        try {

            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hashedPassword,
                ':phone' => $phone
            ]);

            $_SESSION['success'] = "Student added successfully!";

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
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<div class="container">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>Student Hub</h2>

        <a href="index.php">Dashboard</a>
        <a href="add_student.php" class="active">Add Student</a>
        <a href="view_students.php">View Students</a>
    </div>


    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- PAGE HEADER -->
        <div class="page-header">
            <h1>Add New Student</h1>
            <p>Register a new student in the system</p>
        </div>

        <!-- ERROR MESSAGE -->
        <?php if (!empty($message)): ?>
            <div class="error-message">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>


        <!-- ADD STUDENT FORM -->
        <div class="form-container">

            <h2>Student Information</h2>
            <p>Fill in the details below</p>

            <form method="POST">
                <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars($csrfToken) ?>">
            
                <label>Name</label>
                <input type="text" name="name"
                    placeholder="Enter full name"
                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>

                <label>Email</label>
                <input type="email" name="email"
                       placeholder="Enter email address"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                <label>Password</label>
                <input type="password" name="password"
                       placeholder="Minimum 8 characters"
                        minlength="8" required>

                <label>Phone</label>
                <input type="tel" name="phone"
                        placeholder="Enter 10-digit mobile number"
                        acceptmaxlength="13" required>

                <button type="submit">Add Student</button>

            </form>

        </div>

    </div>

</div>

</body>
</html>