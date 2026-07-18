<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'lecturer') {
    header('Location: login.php');
    exit();
}

// Handle AJAX request for course topics
if (isset($_GET['action']) && $_GET['action'] === 'get_topics') {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                t.topic_id, 
                t.topic_name, 
                l.lecture_date, 
                l.status,
                (SELECT GROUP_CONCAT(CONCAT(lo.lo_symbol, ': ', COALESCE(lo.lo_description, '')) ORDER BY lo.lo_symbol SEPARATOR '||')
                   FROM learning_outcome lo
                  WHERE lo.topic_id = t.topic_id) AS lo_concat
            FROM topics t
            LEFT JOIN lectures l ON t.lecture_id = l.lecture_id
            WHERE t.course_id = ?
            ORDER BY l.lecture_date DESC, t.topic_name ASC
        ");
        $stmt->execute([$_GET['course_id']]);
        $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($topics);
        exit();
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit();
    }
}

try {
    // Fetch lecturer's courses
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.course_id, c.course_name, m.major_name, sm.semester_name
        FROM courses c
        JOIN schedule s ON c.course_id = s.course_id
        JOIN majors m ON m.major_id = c.major_id
        JOIN academic_year a ON a.major_id = m.major_id
        JOIN semesters sm ON sm.semester_id = a.semester_id
        WHERE s.lecturer_id = ?
        ORDER BY c.course_name ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Courses - University QA</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Favicon -->
    <link href="img/favicon.jpg" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <style>
        /* Grid Layout */
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            padding: 1rem 0;
        }

        .course-section {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .course-divider {
            margin: 1.5rem 0;
            border-top: 2px solid #e9ecef;
        }

        .topics-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }

        .topic-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid #dee2e6;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .topic-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .topic-status {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.75rem;
            padding: 3px 8px;
            border-radius: 10px;
        }

        .status-scheduled { background: #d1e7dd; color: #0f5132; }
        .status-completed { background: #cfe2ff; color: #052c65; }
        .status-cancelled { background: #f8d7da; color: #58151c; }

        .lo-badge {
            background: #e9ecef;
            color: #495057;
            font-size: 0.75rem;
            padding: 2px 6px;
            border-radius: 4px;
            margin-right: 0.5rem;
        }

        .course-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .course-actions .btn {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">
        <!-- Spinner -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        <!-- Sidebar -->
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <!-- Content -->
        <div class="content">
            <!-- Navbar -->
            <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-bell me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex">Notifications</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <a href="#" class="dropdown-item">
                                <h6 class="fw-normal mb-0">New question posted</h6>
                                <small>15 minutes ago</small>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item text-center">See all notifications</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img class="rounded-circle me-lg-2" src="img/defaultAvatar.jpg" alt="User" style="width: 40px; height: 40px;">
                            <span class="d-none d-lg-inline-flex"><?= htmlspecialchars($_SESSION['email']) ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <a href="profile.php" class="dropdown-item">My Profile</a>
                            <a href="404.html" class="dropdown-item">Settings</a>
                            <a href="logout.php" class="dropdown-item">Log Out</a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Courses Section -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h3 class="mb-0">Scheduled Courses</h3>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary me-3">
                                Total: <?= count($courses) ?>
                            </span>
                            <a href="schedule.php" class="btn btn-outline-primary">
                                <i class="fas fa-calendar-alt me-2"></i>View Schedule
                            </a>
                        </div>
                    </div>

                    <?php if (empty($courses)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-calendar-times me-2"></i>
                            You have no scheduled courses for this semester.
                        </div>
                    <?php else: ?>
                        <div class="courses-grid">
                            <?php foreach ($courses as $course): ?>
                                <div class="course-section">
                                    <!-- Course Card -->
                                    <div class="course-card">
                                        <h5 class="mb-3">Course: <?= htmlspecialchars($course['course_name']) ?>.</h5>
                                        <h5 class="mb-3">Major: <?=  htmlspecialchars($course['major_name']) ?>.</h5>
                                        <h6 class="mb-3">Semester: <?=  htmlspecialchars($course['semester_name']) ?></h6>
                                        <div class="course-actions">
                                            <button class="btn btn-primary btn-sm manage-course"
                                                data-course-id="<?= $course['course_id'] ?>">
                                                <i class="fas fa-tasks me-2"></i>Show Topics
                                            </button>
                                            <a href="attendance.php?course_id=<?= $course['course_id'] ?>" 
                                               class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-user-check me-2"></i>Take Attendance
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <hr class="course-divider">
                                    
                                    <!-- Topics Grid -->
                                    <div class="topics-grid" id="topics-<?= $course['course_id'] ?>" style="display: none;">
                                        <!-- AJAX content loaded here -->
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
            <!-- Back to Top -->
            <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.manage-course').forEach(button => {
            button.addEventListener('click', async (e) => {
                e.preventDefault();
                const courseId = button.dataset.courseId;
                const topicsGrid = document.querySelector(`#topics-${courseId}`);

                // Toggle visibility
                const isVisible = topicsGrid.style.display === 'grid';
                document.querySelectorAll('.topics-grid').forEach(g => g.style.display = 'none');
                
                if (!isVisible) {
                    try {
                        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
                        button.disabled = true;
                        
                        const response = await fetch(`?action=get_topics&course_id=${courseId}`);
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        
                        const topics = await response.json();

                        topicsGrid.innerHTML = topics.map(topic => `
                            <div class="topic-card">
                                ${topic.status ? `
                                <span class="topic-status status-${topic.status.toLowerCase()}">
                                    ${topic.status}
                                </span>` : ''}
                                
                                <h6 class="mb-2">${topic.topic_name}</h6>
                                
                                ${topic.lecture_date ? `
                                <div class="text-muted small mb-2">
                                    <i class="fas fa-calendar-day me-1"></i>
                                    ${new Date(topic.lecture_date).toLocaleDateString()}
                                </div>` : ''}
                                
                                <div class="topic-content">
                                    ${(() => {
                                        if (!topic.lo_concat) return '';
                                        const items = String(topic.lo_concat).split('||').filter(Boolean).slice(0, 2);
                                        return items.map((s) => {
                                            const parts = s.split(': ');
                                            const symbol = parts.shift() || 'LO';
                                            const desc = parts.join(': ') || '';
                                            return `
                                            <div class="lo-item">
                                                <span class="lo-badge">${symbol}</span>
                                                <small>${desc}</small>
                                            </div>`;
                                        }).join('');
                                    })()}
                                </div>
                                <div class="mt-2">
                                    <a class="btn btn-sm btn-primary" href="questions.php?course_id=${courseId}&topic_id=${topic.topic_id}&origin=topics&return_to=lecturer_courses.php#add">
                                        <i class="fas fa-plus me-1"></i>Add Question
                                    </a>
                                </div>
                            </div>
                        `).join('');

                        topicsGrid.style.display = 'grid';
                    } catch (error) {
                        console.error('Error loading topics:', error);
                        topicsGrid.innerHTML = `
                            <div class="col-12">
                                <div class="alert alert-danger py-2">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    ${error.message}
                                </div>
                            </div>`;
                    } finally {
                        button.innerHTML = '<i class="fas fa-tasks me-2"></i>Show Topics';
                        button.disabled = false;
                    }
                } else {
                    topicsGrid.style.display = 'none';
                }
            });
        });
    });
    // Auto-expand a course and show flash alert when returning from questions
    document.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        const expandId = params.get('expand_course_id');
        const flash = params.get('flash');
        if (expandId) {
            const btn = document.querySelector(`.manage-course[data-course-id="${expandId}"]`);
            if (btn) btn.click();
        }
        if (flash === 'question_added') {
            alert('Question added successfully.');
        } else if (flash === 'question_error') {
            alert('There was an error adding the question.');
        }
    });
    </script>
</body>
</html>