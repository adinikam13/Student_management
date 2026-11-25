-- STUDENT MANAGEMENT SYSTEM DATABASE

-- 1. ADMIN USERS
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. STUDENTS
CREATE TABLE students (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(15),
    course VARCHAR(100),
    department VARCHAR(100),
    admission_date DATE DEFAULT CURRENT_DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. ATTENDANCE
CREATE TABLE attendance (
    id SERIAL PRIMARY KEY,
    student_id INT REFERENCES students(id)
        ON DELETE CASCADE,
    attendance_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL
        CHECK (status IN ('Present', 'Absent', 'Late')),
    UNIQUE(student_id, attendance_date)
);

-- 4. MARKS
CREATE TABLE marks (
    id SERIAL PRIMARY KEY,
    student_id INT REFERENCES students(id)
        ON DELETE CASCADE,
    subject VARCHAR(100) NOT NULL,
    marks_obtained NUMERIC(5,2) NOT NULL,
    total_marks NUMERIC(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (marks_obtained >= 0),
    CHECK (total_marks > 0),
    CHECK (marks_obtained <= total_marks),
    UNIQUE(student_id, subject)
);

-- 5. FEES
CREATE TABLE fees (
    id SERIAL PRIMARY KEY,
    student_id INT REFERENCES students(id)
        ON DELETE CASCADE,
    total_fee NUMERIC(10,2) NOT NULL,
    paid_amount NUMERIC(10,2) DEFAULT 0,
    payment_date DATE DEFAULT CURRENT_DATE,
    status VARCHAR(20) DEFAULT 'Pending',
    CHECK (total_fee > 0),
    CHECK (paid_amount >= 0 AND paid_amount <= total_fee)
);

-- INDEXES
CREATE INDEX idx_attendance_date
ON attendance(attendance_date);

CREATE INDEX idx_attendance_student
ON attendance(student_id);

CREATE INDEX idx_marks_student
ON marks(student_id);

CREATE INDEX idx_fees_student
ON fees(student_id);