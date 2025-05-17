
CREATE DATABASE IF NOT EXISTS student_management;
USE student_management;

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    date_of_birth DATE,
    major VARCHAR(100),
    gpa DECIMAL(3,2),
    enrollment_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO students (student_id, first_name, last_name, email, phone, date_of_birth, major, gpa, enrollment_date)
VALUES 
('STU001', 'John', 'Doe', 'john.doe@example.com', '555-123-4567', '2000-05-15', 'Computer Science', 3.75, '2022-09-01'),
('STU002', 'Jane', 'Smith', 'jane.smith@example.com', '555-234-5678', '2001-08-22', 'Business Administration', 3.90, '2022-09-01'),
('STU003', 'Michael', 'Johnson', 'michael.j@example.com', '555-345-6789', '2002-01-30', 'Engineering', 3.50, '2023-01-15');
