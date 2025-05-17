<?php
// students/create.php
include_once "../config/database.php";
include_once "../includes/header.php";

// Initialize variables
$message = "";
$message_class = "";

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get form data
        $student_id = trim($_POST['student_id']);
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $date_of_birth = trim($_POST['date_of_birth']);
        $major = trim($_POST['major']);
        $gpa = trim($_POST['gpa']);
        $enrollment_date = trim($_POST['enrollment_date']);
        
        // Check if student ID or email already exists
        $check_query = "SELECT id FROM students WHERE student_id = ? OR email = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$student_id, $email]);
        
        if ($check_stmt->rowCount() > 0) {
            $message = "Error: Student ID or Email already exists!";
            $message_class = "alert-danger";
        } else {
            // Insert query
            $query = "INSERT INTO students 
                      (student_id, first_name, last_name, email, phone, date_of_birth, major, gpa, enrollment_date) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            // Prepare statement
            $stmt = $db->prepare($query);
            
            // Execute query
            $stmt->execute([
                $student_id, 
                $first_name, 
                $last_name, 
                $email, 
                $phone, 
                $date_of_birth, 
                $major, 
                $gpa, 
                $enrollment_date
            ]);
            
            $message = "Student created successfully!";
            $message_class = "alert-success";
        }
    } catch(PDOException $exception) {
        $message = "Error: " . $exception->getMessage();
        $message_class = "alert-danger";
    }
}
?>

<h2 class="page-header">Add New Student</h2>

<?php if ($message): ?>
    <div class="alert <?php echo $message_class; ?>" role="alert">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="form-container">
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="student_id" class="form-label">Student ID*</label>
                <input type="text" class="form-control" id="student_id" name="student_id" required>
                <div class="invalid-feedback">
                    Please provide a student ID.
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="first_name" class="form-label">First Name*</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
                <div class="invalid-feedback">
                    Please provide a first name.
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="last_name" class="form-label">Last Name*</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
                <div class="invalid-feedback">
                    Please provide a last name.
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email*</label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div class="invalid-feedback">
                    Please provide a valid email.
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone">
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="date_of_birth" class="form-label">Date of Birth</label>
                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="major" class="form-label">Major</label>
                <input type="text" class="form-control" id="major" name="major">
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="gpa" class="form-label">GPA</label>
                <input type="number" step="0.01" min="0" max="4.00" class="form-control" id="gpa" name="gpa">
            </div>
        </div>
        
        <div class="mb-3">
            <label for="enrollment_date" class="form-label">Enrollment Date</label>
            <input type="date" class="form-control" id="enrollment_date" name="enrollment_date">
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="submit" class="btn btn-primary">Create Student</button>
            <a href="../index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include_once "../includes/footer.php"; ?>