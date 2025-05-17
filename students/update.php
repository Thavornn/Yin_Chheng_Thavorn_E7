<?php
// students/update.php
include_once "../config/database.php";
include_once "../includes/header.php";

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

// Fetch student data
$query = "SELECT * FROM students WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);

if ($stmt->rowCount() == 0) {
    $_SESSION['delete_message'] = "Student not found.";
    $_SESSION['delete_message_class'] = "alert-danger";
    header("Location: read.php");
    exit();
}

$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $student_id = trim($_POST['student_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $major = trim($_POST['major']);
    $gpa = trim($_POST['gpa']);
    $enrollment_date = trim($_POST['enrollment_date']);

    // Basic validation
    if (empty($student_id) || empty($first_name) || empty($last_name) || empty($email)) {
        $error = "Student ID, First Name, Last Name, and Email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!empty($gpa) && (!is_numeric($gpa) || $gpa < 0 || $gpa > 4)) {
        $error = "GPA must be a number between 0 and 4.";
    } else {
        // Update student
        $query = "UPDATE students SET student_id = ?, first_name = ?, last_name = ?, email = ?, phone = ?, date_of_birth = ?, major = ?, gpa = ?, enrollment_date = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $params = [
            $student_id,
            $first_name,
            $last_name,
            $email,
            empty($phone) ? null : $phone,
            empty($date_of_birth) ? null : $date_of_birth,
            empty($major) ? null : $major,
            empty($gpa) ? null : $gpa,
            empty($enrollment_date) ? null : $enrollment_date,
            $id
        ];

        if ($stmt->execute($params)) {
            $_SESSION['delete_message'] = "Student updated successfully.";
            $_SESSION['delete_message_class'] = "alert-success";
            header("Location: read.php");
            exit();
        } else {
            $error = "Failed to update student. Please try again.";
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-header">Update Student</h2>
    <a href="read.php" class="btn btn-secondary">
        <i class="fas fa-list"></i> All Students
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h4>Edit Student Details</h4>
    </div>
    <div class="card-body">
        <form action="update.php?id=<?php echo $id; ?>" method="post">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="student_id" class="form-label">Student ID</label>
                    <input type="text" class="form-control" id="student_id" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo $student['date_of_birth'] ?? ''; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="major" class="form-label">Major</label>
                    <input type="text" class="form-control" id="major" name="major" value="<?php echo htmlspecialchars($student['major'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="gpa" class="form-label">GPA</label>
                    <input type="number" class="form-control" id="gpa" name="gpa" step="0.01" min="0" max="4" value="<?php echo $student['gpa'] ?? ''; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="enrollment_date" class="form-label">Enrollment Date</label>
                    <input type="date" class="form-control" id="enrollment_date" name="enrollment_date" value="<?php echo $student['enrollment_date'] ?? ''; ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Student
            </button>
        </form>
    </div>
</div>

<?php
include_once "../includes/footer.php";
?>