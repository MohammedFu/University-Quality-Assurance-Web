<?php
session_start();
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'lecturer') {
    header('Location: index.php');
    exit();
}

$lectureId = filter_input(INPUT_GET, 'lecture_id', FILTER_VALIDATE_INT);
$lecturerId = $_SESSION['user_id'];
$error = '';
$success = '';

// Validate lecture access
$lecture = $pdo->prepare("
    SELECT l.*, c.course_name 
    FROM lectures l
    JOIN schedule s ON l.schedule_id = s.schedule_id
    JOIN courses c ON s.course_id = c.course_id
    WHERE l.lecture_id = ? AND s.lecturer_id = ?
");
$lecture->execute([$lectureId, $lecturerId]);
$lectureData = $lecture->fetch();

if (!$lectureData) {
    $error = "Invalid lecture access or lecture not found";
    $studentData = []; // Initialize empty student data
} else {
    // Get enrolled students only if lecture data is valid
    $students = $pdo->prepare("
        SELECT s.student_id, s.first_name, s.last_name, a.present 
        FROM students s
        LEFT JOIN attendance a 
            ON s.student_id = a.student_id 
            AND a.lecture_id = ?
        JOIN courses c ON s.major_id = c.major_id
        JOIN schedule sch ON c.course_id = sch.course_id
        WHERE sch.schedule_id = ?
        ORDER BY s.last_name ASC
    ");
    $students->execute([$lectureId, $lectureData['schedule_id']]);
    $studentData = $students->fetchAll();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $lectureData) {
    try {
        $pdo->beginTransaction();
        
        foreach ($_POST['attendance'] as $studentId => $status) {
            $stmt = $pdo->prepare("
                INSERT INTO attendance 
                (student_id, lecture_id, date, present) 
                VALUES (?, ?, CURDATE(), ?)
                ON DUPLICATE KEY UPDATE present = ?
            ");
            $present = ($status === 'present') ? 1 : 0;
            $stmt->execute([$studentId, $lectureId, $present, $present]);
        }
        
        $pdo->commit();
        $success = "Attendance successfully recorded!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error saving attendance: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management - University QA</title>
    <link href="img/favicon.jpg" rel="icon">
    <?php include 'includes/header.php'; ?>
    <style>
        .attendance-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .attendance-header {
            background: #2c3e50;
            color: white;
            padding: 1.5rem;
        }
        
        .attendance-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .attendance-table th, 
        .attendance-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .attendance-status {
            display: flex;
            gap: 1rem;
        }
        
        .status-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        
        .status-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #ddd;
        }
        
        input[type="radio"]:checked + .status-indicator {
            border-color: transparent;
        }
        
        input[type="radio"][value="present"]:checked + .status-indicator {
            background: #27ae60;
        }
        
        input[type="radio"][value="absent"]:checked + .status-indicator {
            background: #e74c3c;
        }
    </style>
</head>
<body>
   
    
    <main class="dashboard-container">
        <div class="attendance-table">
            <div class="attendance-header">
                <h2>
                    <i class="fas fa-clipboard-list"></i> 
                    Attendance for <?= htmlspecialchars($lectureData['course_name'] ?? '') ?>
                </h2>
                <p class="lecture-date">
                    <?= date('F j, Y', strtotime($lectureData['lecture_date'] ?? '')) ?>
                </p>
            </div>

            <?php if ($error): ?>
                <div class="alert error"><?= $error ?></div>
            <?php elseif ($success): ?>
                <div class="alert success"><?= $success ?></div>
            <?php endif; ?>

            <?php if ($lectureData && !$error): ?>
                <form method="POST">
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Attendance Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($studentData as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                <td>
                                    <div class="attendance-status">
                                        <label class="status-label">
                                            <input 
                                                type="radio" 
                                                name="attendance[<?= $student['student_id'] ?>]" 
                                                value="present" 
                                                <?= $student['present'] === 1 ? 'checked' : '' ?>
                                                required
                                            >
                                            <span class="status-indicator"></span>
                                            Present
                                        </label>
                                        <label class="status-label">
                                            <input 
                                                type="radio" 
                                                name="attendance[<?= $student['student_id'] ?>]" 
                                                value="absent" 
                                                <?= $student['present'] === 0 ? 'checked' : '' ?>
                                            >
                                            <span class="status-indicator"></span>
                                            Absent
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Save Attendance
                        </button>
                        <a href="lectures.php" class="btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Lectures
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    });
    </script>
</body>
</html>