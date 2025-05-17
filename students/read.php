<?php
// students/read.php
include_once "../config/database.php";
include_once "../includes/header.php";

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Check if an ID was passed
if (isset($_GET['id']) && !empty($_GET['id'])) {
    // Show single student
    $id = $_GET['id'];
    
    $query = "SELECT * FROM students WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-header">Student Details</h2>
    <div>
        <a href="update.php?id=<?php echo $student['id']; ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="delete.php?id=<?php echo $student['id']; ?>" class="btn btn-danger delete-btn">
            <i class="fas fa-trash"></i> Delete
        </a>
        <a href="read.php" class="btn btn-secondary">
            <i class="fas fa-list"></i> All Students
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h4><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></p>
                <p><strong>Date of Birth:</strong> <?php echo $student['date_of_birth'] ? date('F j, Y', strtotime($student['date_of_birth'])) : 'N/A'; ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Major:</strong> <?php echo htmlspecialchars($student['major'] ?? 'N/A'); ?></p>
                <p><strong>GPA:</strong> <?php echo $student['gpa'] ?? 'N/A'; ?></p>
                <p><strong>Enrollment Date:</strong> <?php echo $student['enrollment_date'] ? date('F j, Y', strtotime($student['enrollment_date'])) : 'N/A'; ?></p>
                <p><strong>Registered:</strong> <?php echo date('F j, Y', strtotime($student['created_at'])); ?></p>
            </div>
        </div>
    </div>
</div>

<?php
    } else {
        echo "<div class='alert alert-danger'>Student not found.</div>";
        echo "<a href='read.php' class='btn btn-primary'>View All Students</a>";
    }
} else {
    // List all students
    // Handle pagination
    $records_per_page = 10;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
    $from_record_num = ($records_per_page * $page) - $records_per_page;
    
    // Handle search
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $search_condition = '';
    $search_params = [];
    
    if (!empty($search)) {
        $search_condition = " WHERE student_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR major LIKE ?";
        $search_term = "%{$search}%";
        $search_params = [$search_term, $search_term, $search_term, $search_term, $search_term];
    }
    
    // Count total rows for pagination
    $count_query = "SELECT COUNT(*) as total FROM students" . $search_condition;
    $count_stmt = $db->prepare($count_query);
    if (!empty($search_params)) {
        $count_stmt->execute($search_params);
    } else {
        $count_stmt->execute();
    }
    $row = $count_stmt->fetch(PDO::FETCH_ASSOC);
    $total_rows = $row['total'];
    $total_pages = ceil($total_rows / $records_per_page);
    
    // Query for student list
    $query = "SELECT * FROM students" . $search_condition . " ORDER BY id DESC LIMIT ?, ?";
    $stmt = $db->prepare($query);
    
    // Bind parameters
    if (!empty($search_params)) {
        // Bind search parameters
        for ($i = 0; $i < count($search_params); $i++) {
            $stmt->bindParam($i + 1, $search_params[$i]);
        }
        // Bind pagination parameters
        $stmt->bindParam(count($search_params) + 1, $from_record_num, PDO::PARAM_INT);
        $stmt->bindParam(count($search_params) + 2, $records_per_page, PDO::PARAM_INT);
    } else {
        // Bind only pagination parameters
        $stmt->bindParam(1, $from_record_num, PDO::PARAM_INT);
        $stmt->bindParam(2, $records_per_page, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    
    // Message for successful delete
    $delete_message = isset($_SESSION['delete_message']) ? $_SESSION['delete_message'] : '';
    $delete_message_class = isset($_SESSION['delete_message_class']) ? $_SESSION['delete_message_class'] : '';
    
    // Clear session message after displaying
    unset($_SESSION['delete_message']);
    unset($_SESSION['delete_message_class']);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-header">All Students</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Student
    </a>
</div>

<?php if ($delete_message): ?>
    <div class="alert <?php echo $delete_message_class; ?>" role="alert">
        <?php echo $delete_message; ?>
    </div>
<?php endif; ?>

<!-- Search Form -->
<form action="read.php" method="get" class="mb-4">
    <div class="input-group">
        <input type="text" class="form-control" placeholder="Search for students..." name="search" value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-outline-secondary" type="submit">Search</button>
        <?php if (!empty($search)): ?>
            <a href="read.php" class="btn btn-outline-danger">Clear</a>
        <?php endif; ?>
    </div>
</form>

<?php if ($total_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Major</th>
                    <th>GPA</th>
                    <th class="actions-column">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['major'] ?? 'N/A'); ?></td>
                        <td><?php echo $row['gpa'] ?? 'N/A'; ?></td>
                        <td>
                            <a href="read.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="update.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm delete-btn" title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo "read.php?page=1" . (!empty($search) ? "&search={$search}" : ""); ?>">First</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo "read.php?page=" . ($page - 1) . (!empty($search) ? "&search={$search}" : ""); ?>">Previous</a>
                    </li>
                <?php endif; ?>
                
                <?php
                // Calculate range of page numbers to display
                $range = 2;
                $start_page = max(1, $page - $range);
                $end_page = min($total_pages, $page + $range);
                
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo "read.php?page={$i}" . (!empty($search) ? "&search={$search}" : ""); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo "read.php?page=" . ($page + 1) . (!empty($search) ? "&search={$search}" : ""); ?>">Next</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo "read.php?page={$total_pages}" . (!empty($search) ? "&search={$search}" : ""); ?>">Last</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
    
<?php else: ?>
    <div class="alert alert-info">
        No students found.
    </div>
<?php endif; ?>

<?php
}
include_once "../includes/footer.php";
?>