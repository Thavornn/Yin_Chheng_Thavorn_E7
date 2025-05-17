<?php
// index.php
include_once "config/database.php";
include_once "includes/header.php";

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Count total students
$query = "SELECT COUNT(*) as total FROM students";
$stmt = $db->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$total_students = $row['total'];

// Get recently added students
$query = "SELECT * FROM students ORDER BY created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count students by major (for chart data)
$query = "SELECT major, COUNT(*) as count FROM students GROUP BY major ORDER BY count DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$students_by_major = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: #343a40;
            color: white;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu {
            padding: 0;
            list-style: none;
        }
        
        .sidebar-menu li {
            width: 100%;
        }
        
        .sidebar-menu li a {
            padding: 15px 20px;
            display: block;
            color: #ced4da;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu li a:hover {
            background: #495057;
            color: white;
        }
        
        .sidebar-menu li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .stat-card i {
            font-size: 2.5rem;
            margin-right: 15px;
        }
        
        .stat-card-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0;
        }
        
        .stat-card-label {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 0;
        }
        
        .toggle-btn {
            position: fixed;
            left: 250px;
            top: 20px;
            z-index: 999;
            background: #343a40;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            text-align: center;
            line-height: 40px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .sidebar-collapsed {
            left: -250px;
        }
        
        .main-content-expanded {
            margin-left: 0;
        }
        
        .toggle-btn-moved {
            left: 20px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .toggle-btn {
                left: 20px;
            }
            
            .sidebar-mobile-active {
                left: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4>Student MS</h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="students/create.php"><i class="fas fa-user-plus"></i> Add Student</a></li>
            <li><a href="students/read.php"><i class="fas fa-list"></i> All Students</a></li>
            <li><a href="reports/index.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Toggle Button -->
    <button class="toggle-btn" id="toggle-btn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <div class="container-fluid">
            <!-- Welcome Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-primary text-white">
                        <div class="card-body p-4">
                            <h2 class="card-title">Welcome to Student Management System</h2>
                            <p class="card-text">A comprehensive system to manage student information efficiently.</p>
                            <div class="mt-3">
                                <a class="btn btn-light" href="students/create.php" role="button">
                                    <i class="fas fa-user-plus me-2"></i>Add New Student
                                </a>
                                <a class="btn btn-outline-light ms-2" href="students/read.php" role="button">
                                    <i class="fas fa-list me-2"></i>View All Students
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-card bg-info text-white">
                        <i class="fas fa-users"></i>
                        <div>
                            <p class="stat-card-value"><?php echo $total_students; ?></p>
                            <p class="stat-card-label">Total Students</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card bg-success text-white">
                        <i class="fas fa-graduation-cap"></i>
                        <div>
                            <p class="stat-card-value">
                                <?php 
                                $query = "SELECT COUNT(DISTINCT major) as total_majors FROM students";
                                $stmt = $db->prepare($query);
                                $stmt->execute();
                                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                echo $row['total_majors'];
                                ?>
                            </p>
                            <p class="stat-card-label">Total Majors</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card bg-warning text-white">
                        <i class="fas fa-clock"></i>
                        <div>
                            <p class="stat-card-value">
                                <?php 
                                $query = "SELECT COUNT(*) as count FROM students WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                                $stmt = $db->prepare($query);
                                $stmt->execute();
                                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                echo $row['count'];
                                ?>
                            </p>
                            <p class="stat-card-label">New This Week</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Students & Chart Area -->
            <div class="row">
                <!-- Recent Students -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recently Added Students</h5>
                                <a href="students/read.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (count($recent_students) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Major</th>
                                                <th>Date Added</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_students as $student): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($student['major']); ?></span></td>
                                                    <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="students/read.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="students/update.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-warning">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="students/delete.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student?');">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> No students found in the database.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Distribution by Major -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Students by Major</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($students_by_major) > 0): ?>
                                <canvas id="majorChart" width="100%" height="100%"></canvas>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> No data available for chart.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Actions Card -->
                    <div class="card mt-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <a href="students/create.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-user-plus me-2"></i> Add New Student
                                </a>
                                <a href="reports/index.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-chart-line me-2"></i> Generate Reports
                                </a>
                                <a href="students/export.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-file-export me-2"></i> Export Student Data
                                </a>
                                <a href="settings.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-cog me-2"></i> System Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap & JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    <script>
        // Toggle sidebar
        document.getElementById('toggle-btn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('sidebar-collapsed');
            document.getElementById('main-content').classList.toggle('main-content-expanded');
            this.classList.toggle('toggle-btn-moved');
        });

        // Handle mobile sidebar
        if (window.innerWidth <= 768) {
            document.getElementById('sidebar').classList.add('sidebar-collapsed');
            document.getElementById('main-content').classList.add('main-content-expanded');
            document.getElementById('toggle-btn').classList.add('toggle-btn-moved');
        }

        // Chart for majors
        <?php if (count($students_by_major) > 0): ?>
        var ctx = document.getElementById('majorChart').getContext('2d');
        var majorChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php 
                    foreach ($students_by_major as $major) {
                        echo "'" . $major['major'] . "', ";
                    }
                    ?>
                ],
                datasets: [{
                    data: [
                        <?php 
                        foreach ($students_by_major as $major) {
                            echo $major['count'] . ", ";
                        }
                        ?>
                    ],
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                        '#6f42c1', '#5a5c69', '#858796', '#20c9a6', '#f8f9fc'
                    ],
                    hoverBackgroundColor: [
                        '#2e59d9', '#17a673', '#2c9faf', '#f4b619', '#e02d1b',
                        '#5a30a9', '#484a54', '#717384', '#169c82', '#e6e8ed'
                    ],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 15,
                            padding: 15
                        }
                    }
                },
                cutout: '70%',
                animation: {
                    animateScale: true
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>