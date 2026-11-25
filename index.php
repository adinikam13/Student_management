<?php
session_start();
require_once 'config/database.php';

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// Search
$search = trim($_GET['search'] ?? '');
// Sorting
$sort = $_GET['sort'] ?? 'newest';

$allowedSorts = [
    'newest' => 'id DESC',
    'oldest' => 'id ASC',
    'name_asc' => 'name ASC',
    'name_desc' => 'name DESC'
];

$orderBy = $allowedSorts[$sort] ?? 'id DESC';

// Pagination settings
$limit = 10;
$page = max(1, (int)($_GET['page'] ?? 1));

// Dashboard statistics
$countSql = "SELECT COUNT(*) FROM students";
$countStmt = $pdo->query($countSql);
$totalStudents = $countStmt->fetchColumn();

// Recently added students
$recentSql = "SELECT COUNT(*) FROM students
              WHERE id IN (
                  SELECT id FROM students
                  ORDER BY id DESC
                  LIMIT 5
              )";

$recentStmt = $pdo->query($recentSql);
$recentStudents = $recentStmt->fetchColumn();

// Count students matching search
if ($search !== '') {

    $countSearchSql = "SELECT COUNT(*) FROM students
                       WHERE name ILIKE :search
                       OR email ILIKE :search";

    $countSearchStmt = $pdo->prepare($countSearchSql);

    $countSearchStmt->execute([
        ':search' => "%$search%"
    ]);

    $filteredCount = $countSearchStmt->fetchColumn();

} else {

    $filteredCount = $totalStudents;

}

// Calculate total pages
$totalPages = max(1, ceil($filteredCount / $limit));

// Prevent invalid page numbers
$page = min($page, $totalPages);

// Calculate starting record
$offset = ($page - 1) * $limit;

// Fetch students for current page
if ($search !== '') {

    $sql = "SELECT * FROM students
            WHERE name ILIKE :search
            OR email ILIKE :search
            ORDER BY $orderBy
            LIMIT $limit OFFSET $offset";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':search' => "%$search%"
    ]);

} else {

    $sql = "SELECT * FROM students
            ORDER BY $orderBy
            LIMIT $limit OFFSET $offset";

    $stmt = $pdo->query($sql);

}

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Management System</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<div class="container">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>Student Hub</h2>

        <a href="index.php" class="active">Dashboard</a>
        <a href="add_student.php">Add Student</a>
        <a href="view_students.php">View Students</a>
    </div>


    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- PAGE HEADER -->
        <div class="page-header">
            <h1>Dashboard Overview</h1>
            <p>Welcome to your Student Management System</p>
        </div>

        <!-- SUCCESS MESSAGE -->
        <?php if (!empty($success)): ?>
            <div class="success-message">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>


        <!-- DASHBOARD STATISTICS -->
        <div class="dashboard-cards">

            <div class="stat-card">
                <h3>Total Students</h3>
                <h2><?= $totalStudents ?></h2>
            </div>

            <div class="stat-card">
                <h3>Active Students</h3>
                <h2><?= $totalStudents ?></h2>
            </div>

            <div class="stat-card">
                <h3>Recently Added</h3>
                <h2><?= $recentStudents ?></h2>
            </div>

            <div class="stat-card">
                <h3>Total Records</h3>
                <h2><?= $totalStudents ?></h2>
            </div>

        </div>


        <!-- STUDENT MANAGEMENT SECTION -->
        <div class="student-section">

            <div class="section-header">
                <div>
                    <h2>Student List</h2>
                    <p>Manage and view all registered students</p>
                </div>

                <a href="add_student.php" class="add-button">
                    + Add New Student
                </a>
            </div>


            <!-- SEARCH BAR -->
           <form method="GET" action="index.php" class="search-form">

    <input
        type="text"
        name="search"
        placeholder="Search by name or email..."
        value="<?= htmlspecialchars($search) ?>"
    >

    <select name="sort">

        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>
            Newest First
        </option>

        <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>
            Oldest First
        </option>

        <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>
            Name A-Z
        </option>

        <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>
            Name Z-A
        </option>

    </select>

    <button type="submit">Apply</button>

    <a href="index.php">Clear</a>

</form>


            <!-- STUDENT TABLE -->
            <div class="table-wrapper">

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (count($students) > 0): ?>

                            <?php foreach ($students as $student): ?>

                                <tr>
                                    <td><?= htmlspecialchars($student['id']) ?></td>
                                    <td><?= htmlspecialchars($student['name']) ?></td>
                                    <td><?= htmlspecialchars($student['email']) ?></td>
                                    <td><?= htmlspecialchars($student['phone']) ?></td>

                                    <td class="action-links">

                                        <a href="edit_student.php?id=<?= $student['id'] ?>">
                                            Edit
                                        </a>

                                        <a
                                            href="delete_student.php?id=<?= $student['id'] ?>"
                                            onclick="return confirm('Are you sure you want to delete this student?')"
                                        >
                                            Delete
                                        </a>

                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="5">No students found.</td>
                            </tr>

                        <?php endif; ?>

                    </tbody>
                </table>

            </div>
<!-- PAGINATION -->
<div class="pagination">

    <span>
        Showing <?= count($students) ?> of <?= $filteredCount ?> students
    </span>

    <div class="pagination-buttons">

        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>">
                Previous
            </a>   
         
        <?php endif; ?>

        <span>
            Page <?= $page ?> of <?= $totalPages ?>
        </span>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>">
                Next
            </a>
          
        <?php endif; ?>

    </div>

</div>
        </div>

    </div>

</div>

</body>
</html>
