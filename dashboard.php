<?php
session_start();
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

if ($role === 'lecturer') {
    // Get all courses taught by the lecturer
    $coursesQuery = $pdo->prepare(
        "SELECT DISTINCT c.course_id, c.course_name 
         FROM courses c
         JOIN schedule s ON c.course_id = s.course_id
         WHERE s.lecturer_id = ?"
    );
    $coursesQuery->execute([$userId]);
    $allCourses = $coursesQuery->fetchAll();

    $courses = [];
    foreach ($allCourses as $course) {
        // Updated topics query with lecture date filter
        $topicsQuery = $pdo->prepare(
            "SELECT 
                t.topic_id,
                t.topic_name,
                c.course_id,
                c.course_name,
                COUNT(q.question_id) AS total_questions
             FROM topics t
             JOIN courses c ON t.course_id = c.course_id
             LEFT JOIN questions q ON t.topic_id = q.topic_id
             JOIN lectures l ON t.lecture_id = l.lecture_id
             JOIN schedule s ON l.schedule_id = s.schedule_id
             WHERE 
                 s.lecturer_id = ?
                 AND c.course_id = ?
                 AND l.lecture_date <= (
                     SELECT MAX(l2.lecture_date)
                     FROM lectures l2
                     JOIN schedule s2 ON l2.schedule_id = s2.schedule_id
                     WHERE 
                         s2.course_id = c.course_id
                         AND s2.lecturer_id = s.lecturer_id
                 )
             GROUP BY t.topic_id, t.topic_name, c.course_id, c.course_name"
        );
        $topicsQuery->execute([$userId, $course['course_id']]);
        $topics = $topicsQuery->fetchAll();

        $courseData = [
            'name' => $course['course_name'],
            'topics' => [],
            'labels' => [],
            'percentages' => []
        ];

        foreach ($topics as $topic) {
            // Get questions with answer statistics
            $questionsQuery = $pdo->prepare(
                "SELECT 
                    q.question_id,
                    q.question_text,
                    q.right_choice,
                    COALESCE(SUM(sa.result), 0) AS correct_answers,
                    COUNT(sa.answer_id) AS total_answers,
                    COUNT(sa.answer_id) - COALESCE(SUM(sa.result), 0) AS wrong_answers
                 FROM questions q
                 LEFT JOIN student_answers sa ON q.question_id = sa.question_id
                 WHERE q.topic_id = ?
                 GROUP BY q.question_id"
            );
            $questionsQuery->execute([$topic['topic_id']]);
            $questions = $questionsQuery->fetchAll();

            // Calculate topic statistics
            $topicCorrect = 0;
            $topicTotal = 0;
            foreach ($questions as $q) {
                $topicCorrect += $q['correct_answers'];
                $topicTotal += $q['total_answers'];
            }
            
            $percentage = $topicTotal > 0 
                ? round(($topicCorrect / $topicTotal) * 100, 1)
                : 0;

            $courseData['topics'][] = [
                'topic_id' => $topic['topic_id'],
                'topic_name' => $topic['topic_name'],
                'total_questions' => $topic['total_questions'],
                'correct' => $topicCorrect,
                'total' => $topicTotal,
                'wrong' => $topicTotal - $topicCorrect,
                'percentage' => $percentage,
                'questions' => $questions
            ];

            $courseData['labels'][] = $topic['topic_name'];
            $courseData['percentages'][] = $percentage;
        }

        if (!empty($courseData['topics'])) {
            $courses[$course['course_id']] = $courseData;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Lecturer Dashboard - University QA</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

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

    <!-- Custom Styles -->
    <style>
        :root {
            --primary: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg-light: #f8fafc;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 2rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .course-section {
            background: white;
            border-radius: 0.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .course-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .course-title {
            font-size: 1.5rem;
            margin: 0;
            color: #1e293b;
            font-weight: 600;
        }

        .line-chart-container {
            height: 400px;
            margin: 2rem 0;
        }

        .topics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .topic-card {
            background: var(--bg-light);
            border-radius: 0.5rem;
            padding: 1.5rem;
            transition: transform 0.2s;
            border: 1px solid #e2e8f0;
        }

        .topic-card:hover {
            transform: translateY(-3px);
        }

        .topic-header h3 {
            margin: 0 0 0.5rem;
            font-size: 1.1rem;
            color: #1e293b;
            font-weight: 500;
        }

        .topic-stats {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .topic-chart-container {
            position: relative;
            height: 250px;
        }

        .questions-list {
            margin-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
            padding-top: 1rem;
        }

        .question-item {
            background: white;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }

        .question-item:hover {
            transform: translateX(5px);
        }

        .question-text {
            font-weight: 500;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .question-stats {
            display: flex;
            gap: 1rem;
            font-size: 0.9rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .correct {
            color: #10b981;
        }

        .wrong {
            color: #ef4444;
        }

        .no-questions {
            color: #94a3b8;
            text-align: center;
            padding: 1rem;
        }

        .no-data-message {
            text-align: center;
            padding: 3rem;
            color: #94a3b8;
            background: white;
            border-radius: 0.5rem;
            margin: 2rem 0;
        }

        .no-data-message i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #cbd5e1;
        }

        .question-chart-container {
            position: relative;
            background: white;
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            height: 150px;
            margin: 1rem 0;
        }

        .question-item:nth-child(odd) .question-chart-container {
            border-left: 4px solid #4f46e5;
        }

        .question-item:nth-child(even) .question-chart-container {
            border-right: 4px solid #10b981;
        }

        .question-chart-container canvas {
            margin: 0 auto;
            display: block;
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

        <!-- Sidebar Start -->
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        <!-- Sidebar End -->

        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
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
                            <a href="404.html" class="dropdown-item">My Profile</a>
                            <a href="404.html" class="dropdown-item">Settings</a>
                            <a href="logout.php" class="dropdown-item">Log Out</a>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Navbar End -->

            <!-- Main Content -->
            <div class="container-fluid pt-4 px-4">
                <div class="welcome-banner">
                    <h1 class="text-white mb-3">Welcome, <?= htmlspecialchars($_SESSION['email']) ?></h1>
                    <p class="mb-0">Role: <?= ucfirst($role) ?></p>
                </div>

                <?php if ($role === 'lecturer'): ?>
                    <?php if (empty($courses)): ?>
                        <div class="no-data-message">
                            <i class="fas fa-chart-bar fa-3x"></i>
                            <p class="mb-0">No course data available. Questions and answers will appear once students start participating.</p>
                        </div>
                    <?php else: ?>
                        <div id="coursesContainer">
                            <?php foreach ($courses as $courseId => $course): ?>
                                <div class="course-section">
                                    <div class="course-header">
                                        <h2 class="course-title"><?= htmlspecialchars($course['name']) ?></h2>
                                    </div>

                                    <div class="line-chart-container">
                                        <canvas id="lineChart-<?= $courseId ?>"></canvas>
                                    </div>

                                    <div class="topics-grid">
                                        <?php foreach ($course['topics'] as $topic): ?>
                                            <div class="topic-card">
                                                <div class="topic-header">
                                                    <h3><?= htmlspecialchars($topic['topic_name']) ?></h3>
                                                    <div class="topic-stats">
                                                        <span>Total Questions: <?= $topic['total_questions'] ?></span>
                                                        <span>• Total Answers: <?= $topic['total'] ?></span>
                                                    </div>
                                                </div>
                                                <div class="topic-chart-container">
                                                    <canvas id="pieChart-<?= $topic['topic_id'] ?>"></canvas>
                                                </div>

                                                <div class="questions-list">
                                                    <h4>Questions Analysis</h4>
                                                    <?php if (!empty($topic['questions'])): ?>
                                                        <?php foreach ($topic['questions'] as $question): ?>
                                                            <div class="question-item">
                                                                <div class="question-text">
                                                                    <?= htmlspecialchars($question['question_text']) ?>
                                                                    <small class="text-muted d-block mt-1">
                                                                        Correct Answer: <?= htmlspecialchars($question['right_choice']) ?>
                                                                    </small>
                                                                </div>
                                                                
                                                                <div class="question-chart-container">
                                                                    <canvas id="questionChart-<?= $question['question_id'] ?>"></canvas>
                                                                </div>

                                                                <div class="question-stats">
                                                                    <span class="correct">
                                                                        ✓ Correct: <?= $question['correct_answers'] ?>
                                                                    </span>
                                                                    <span class="wrong">
                                                                        ✗ Wrong: <?= $question['wrong_answers'] ?>
                                                                    </span>
                                                                    <span>• Total Attempts: <?= $question['total_answers'] ?></span>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <div class="no-questions">
                                                            No questions available for this topic
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded-top p-4">
                    <div class="row">
                        <div class="col-12 text-center">
                            &copy; University QA System, All Rights Reserved.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Content End -->
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

    <!-- Chart Initialization -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        <?php if ($role === 'lecturer' && !empty($courses)): ?>
            <?php foreach ($courses as $courseId => $course): ?>
                // Line Chart
                new Chart(document.getElementById('lineChart-<?= $courseId ?>'), {
                    type: 'line',
                    data: {
                        labels: <?= json_encode($course['labels']) ?>,
                        datasets: [{
                            label: 'Understanding Percentage',
                            data: <?= json_encode($course['percentages']) ?>,
                            borderColor: '#4f46e5',
                            borderWidth: 3,
                            tension: 0.3,
                            pointRadius: 5,
                            pointBackgroundColor: '#fff',
                            pointBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    title: (items) => items[0].label,
                                    label: (context) => 
                                        `${context.dataset.label}: ${context.raw}%`
                                }
                            }
                        },
                        scales: {
                            y: {
                                title: { 
                                    display: true, 
                                    text: 'Understanding (%)',
                                    font: { size: 14 }
                                },
                                min: 0,
                                max: 100,
                                ticks: { 
                                    stepSize: 20,
                                    callback: value => value + '%'
                                }
                            },
                            x: {
                                title: { 
                                    display: true, 
                                    text: 'Topics',
                                    font: { size: 14 }
                                },
                                grid: { display: false }
                            }
                        }
                    }
                });

                // Topic Pie Charts
                <?php foreach ($course['topics'] as $topic): ?>
                    new Chart(document.getElementById('pieChart-<?= $topic['topic_id'] ?>'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Correct', 'Wrong'],
                            datasets: [{
                                data: [<?= $topic['correct'] ?>, <?= $topic['wrong'] ?>],
                                backgroundColor: ['#10b981', '#ef4444'],
                                borderWidth: 2,
                                hoverOffset: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom' },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => {
                                            const total = context.dataset.data.reduce((a, b) => a + b);
                                            const percent = (context.raw / total * 100).toFixed(1);
                                            return `${context.label}: ${context.raw} (${percent}%)`;
                                        }
                                    }
                                }
                            },
                            cutout: '65%'
                        }
                    });

                    // Question Pie Charts
                    <?php foreach ($topic['questions'] as $question): ?>
                        new Chart(document.getElementById('questionChart-<?= $question['question_id'] ?>'), {
                            type: 'doughnut',
                            data: {
                                labels: ['Correct (<?= $question['correct_answers'] ?>)', 
                                       'Wrong (<?= $question['wrong_answers'] ?>)'],
                                datasets: [{
                                    data: [<?= $question['correct_answers'] ?>, 
                                         <?= $question['wrong_answers'] ?>],
                                    backgroundColor: [
                                        'hsl(160, 84%, 39%)',
                                        'hsl(0, 84%, 60%)'
                                    ],
                                    borderColor: [
                                        'hsl(160, 84%, 20%)',
                                        'hsl(0, 84%, 30%)'
                                    ],
                                    borderWidth: 2,
                                    hoverOffset: 8,
                                    hoverBackgroundColor: [
                                        'hsl(160, 84%, 49%)',
                                        'hsl(0, 84%, 70%)'
                                    ]
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { 
                                        position: 'bottom',
                                        labels: {
                                            usePointStyle: true,
                                            pointStyle: 'circle',
                                            padding: 20
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: (context) => {
                                                const total = context.dataset.data.reduce((a, b) => a + b);
                                                const percent = (context.raw / total * 100).toFixed(1);
                                                return `${context.label}: ${percent}% (${context.raw} answers)`;
                                            }
                                        }
                                    }
                                },
                                cutout: '60%',
                                animation: {
                                    animateScale: true,
                                    animateRotate: true
                                }
                            }
                        });
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    });
    </script>
</body>
</html>