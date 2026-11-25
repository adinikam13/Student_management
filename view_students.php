<?php
require_once 'config/database.php';

// Get search keyword
$search = trim($_GET['search'] ?? '');

// Search students
if ($search !== '') {
    $sql = "SELECT * FROM students
            WHERE name ILIKE :search
            OR email ILIKE :search
            OR phone ILIKE :search
            OR course ILIKE :search
            ORDER BY id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':search' => '%' . $search . '%'
    ]);
} else {
    // Display all students
    $sql = "SELECT * FROM students ORDER BY id ASC";
    $stmt = $pdo->query($sql);
}

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Students</title>
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>

<div class="container">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>Student Hub</h2>

        <a href="index.php">Dashboard</a>
        <a href="add_student.php">Add Student</a>
        <a href="view_students.php" class="active">View Students</a>
    </div>


    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- PAGE HEADER -->
        <div class="page-header">
            <h1>All Students</h1>
            <p>View and manage all registered students</p>
        </div>


        <!-- STUDENT LIST SECTION -->
        <div class="student-section">

            <div class="section-header">
                <div>
                    <h2>Student Records</h2>
                    <p>Search, edit and manage student information</p>
                </div>

                <a href="add_student.php" class="add-button">
                    + Add New Student
                </a>
            </div>


            <!-- SEARCH FORM -->
            <form method="GET" action="view_students.php" class="search-form">

                <input
                    type="text"
                    name="search"
                    placeholder="Search by name, email, phone or course..."
                    value="<?= htmlspecialchars($search) ?>"
                >

                <button type="submit">Search</button>

                <a href="view_students.php">Clear</a>

            </form>


            <!-- NO RESULTS MESSAGE -->
            <?php if (count($students) === 0): ?>

                <div class="empty-message">
                    No students found!
                </div>

            <?php endif; ?>


            <!-- STUDENT TABLE -->
            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Course</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($students as $student): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars((string)$student['id']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($student['name']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($student['email']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($student['phone'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($student['course'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($student['created_at'] ?? '') ?>
                            </td>

                            <td class="action-links">

                                <a href="edit_student.php?id=<?= (int)$student['id'] ?>">
                                    Edit
                                </a>

                                <a
                                    href="delete_student.php?id=<?= (int)$student['id'] ?>"
                                    onclick="return confirm('Are you sure you want to delete this student?');"
                                >
                                    Delete
                                </a>

                            </td>

                        </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>