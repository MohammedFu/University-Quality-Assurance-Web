<?php
session_start();
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'lecturer') {
    header('Location: login.php');
    exit();
}

$lecturerId = $_SESSION['user_id'];
$error = $success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_question'])) {
        $topicId = $_POST['topic_id'];
        $questionText = $_POST['question_text'];
        $choices = [
            $_POST['choice1'],
            $_POST['choice2'],
            $_POST['choice3'],
            $_POST['choice4']
        ];
        $correctChoice = $_POST['correct_choice'];
        $origin = isset($_POST['origin']) ? $_POST['origin'] : null;
        $returnTo = isset($_POST['return_to']) ? $_POST['return_to'] : null;

        try {
            $pdo->beginTransaction();
            
            // Get lecture_id and course_id associated with the selected topic
            $topicInfoStmt = $pdo->prepare("SELECT lecture_id, course_id FROM topics WHERE topic_id = ?");
            $topicInfoStmt->execute([$topicId]);
            $topicInfo = $topicInfoStmt->fetch(PDO::FETCH_ASSOC);
            $lectureId = $topicInfo ? $topicInfo['lecture_id'] : null;
            $courseIdForRedirect = $topicInfo ? $topicInfo['course_id'] : null;
            
            $stmt = $pdo->prepare("INSERT INTO questions 
                (topic_id, lecture_id, question_text, choice_one, choice_two, 
                 choice_three, choice_four, right_choice) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $topicId,
                $lectureId,
                $questionText,
                $choices[0],
                $choices[1],
                $choices[2],
                $choices[3],
                $choices[$correctChoice-1]
            ]);
            
            $pdo->commit();
            // Redirect back depending on origin
            if ($origin === 'topics' && $returnTo) {
                header('Location: ' . $returnTo . '?expand_course_id=' . urlencode((string)$courseIdForRedirect) . '&flash=question_added');
                exit();
            } else {
                // Keep context on questions page
                header('Location: questions.php?course_id=' . urlencode((string)$courseIdForRedirect) . '&topic_id=' . urlencode((string)$topicId) . '&status=success#topic-' . urlencode((string)$topicId));
                exit();
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            // Redirect with error status; avoid exposing full error details in UI
            if ($origin === 'topics' && $returnTo) {
                header('Location: ' . $returnTo . '?flash=question_error');
                exit();
            } else {
                header('Location: questions.php?topic_id=' . urlencode((string)$topicId) . '&status=error#topic-' . urlencode((string)$topicId));
                exit();
            }
        }
    }
}

// Get lecturer's courses
$coursesQuery = $pdo->prepare("
    SELECT c.course_id, c.course_name 
    FROM courses c
    JOIN schedule s ON c.course_id = s.course_id
    WHERE s.lecturer_id = ?
    GROUP BY c.course_id
");
$coursesQuery->execute([$lecturerId]);
$courses = $coursesQuery->fetchAll();

// Get all topics and questions for courses
$fullData = [];
foreach ($courses as $course) {
    // Get course topics
    $topicsQuery = $pdo->prepare("
        SELECT t.topic_id, t.topic_name 
        FROM topics t 
        WHERE t.course_id = ?
    ");
    $topicsQuery->execute([$course['course_id']]);
    $topics = $topicsQuery->fetchAll();
    
    foreach ($topics as &$topic) {
        // Get questions with answer stats
        $questionsQuery = $pdo->prepare("
            SELECT q.*, 
                   COUNT(sa.answer_id) AS total_answers,
                   SUM(sa.result) AS correct_answers
            FROM questions q
            LEFT JOIN student_answers sa ON q.question_id = sa.question_id
            WHERE q.topic_id = ?
            GROUP BY q.question_id
        ");
        $questionsQuery->execute([$topic['topic_id']]);
        $topic['questions'] = $questionsQuery->fetchAll();
    }
    
    $fullData[] = [
        'course' => $course,
        'topics' => $topics
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions - University QA</title>
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
        /* Responsive Grid Layout */
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 1rem 0;
        }

        .course-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .course-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .course-divider {
            margin: 1.5rem 0;
            border-top: 2px solid #e9ecef;
        }

        .topics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .topic-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid #dee2e6;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .topic-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .question-item {
            background: white;
            padding: 1rem;
            margin: 0.5rem 0;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .question-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .correct-badge {
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8em;
        }
        .wrong-badge {
            background: #dc3545;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8em;
        }

        .stats-badge {
            background: #e9ecef;
            color: #495057;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .question-choices {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .choice-item {
            padding: 0.5rem;
            border-radius: 6px;
            background: #f8f9fa;
            position: relative;
            border: 1px solid #dee2e6;
            transition: background 0.2s ease;
        }
        .choice-item:hover {
            background: #e2e6ea;
        }
        .correct-choice {
            background: #d1e7dd;
            border: 1px solid #28a745;
        }

        /* Floating Action Button Decoration */
        .add-question-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
            padding: 1.25rem 2rem;
            font-size: 1.1rem;
            background: linear-gradient(45deg, #4f46e5, #6d28d9);
            border: none;
            color: white;
            border-radius: 50px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: background 0.3s ease;
        }
        .add-question-btn:hover {
            background: linear-gradient(45deg, #6d28d9, #4f46e5);
        }

        /* Modal Decorations */
        .modal-header {
            background: linear-gradient(45deg, #4f46e5, #6d28d9);
            color: white;
        }
        .modal-header .btn-close {
            filter: invert(1);
        }
        .modal-content {
            border-radius: 12px;
        }
        .modal-footer .btn-primary {
            background: linear-gradient(45deg, #4f46e5, #6d28d9);
            border: none;
        }
        .modal-footer .btn-primary:hover {
            background: linear-gradient(45deg, #6d28d9, #4f46e5);
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

            <!-- Main Content -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h3 class="mb-0">Manage Questions</h3>
                        <span class="stats-badge">
                            <i class="fas fa-question-circle me-2"></i>
                            Total Courses: <?= count($courses) ?>
                        </span>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <div class="courses-grid">
                        <?php foreach ($fullData as $courseData): ?>
                        <div class="course-section">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="mb-0"><?= htmlspecialchars($courseData['course']['course_name']) ?></h4>
                                <span class="stats-badge">
                                    <i class="fas fa-book-open me-2"></i>
                                    Topics: <?= count($courseData['topics']) ?>
                                </span>
                            </div>

                            <div class="topics-grid">
                                <?php foreach ($courseData['topics'] as $topic): ?>
                                <div class="topic-card" id="topic-<?= $topic['topic_id'] ?>">
                                    <h5 class="mb-3"><?= htmlspecialchars($topic['topic_name']) ?></h5>
                                    
                                    <?php foreach ($topic['questions'] as $question): ?>
                                    <div class="question-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div style="flex: 1">
                                                <strong><?= htmlspecialchars($question['question_text']) ?></strong>
                                                <div class="question-choices mt-2">
                                                    <?php foreach (['choice_one','choice_two','choice_three','choice_four'] as $index => $choice): ?>
                                                    <div class="choice-item <?= $question[$choice] === $question['right_choice'] ? 'correct-choice' : '' ?>">
                                                        <?= $question[$choice] ?>
                                                        <?php if ($question[$choice] === $question['right_choice']): ?>
                                                        <span class="correct-badge">Correct</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <div class="text-end ms-3">
                                                <div class="stats-badge">
                                                    <i class="fas fa-users me-1"></i>
                                                    <?= $question['total_answers'] ?> Answers
                                                </div>
                                                <div class="stats-badge mt-2">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    <?= $question['correct_answers'] ?> Correct
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Question Modal -->
    <div class="modal fade" id="addQuestionModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Course</label>
                                <select class="form-select" id="courseSelect" required>
                                    <option value="">Select Course</option>
                                    <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['course_id'] ?>">
                                        <?= htmlspecialchars($course['course_name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Topic</label>
                                <select class="form-select" name="topic_id" id="topicSelect" required disabled>
                                    <option value="">Select Topic</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Question Text</label>
                                <textarea name="question_text" class="form-control" rows="3" required></textarea>
                            </div>
                            
                            <?php foreach (range(1,4) as $n): ?>
                            <div class="col-md-6">
                                <label class="form-label">Choice <?= $n ?></label>
                                <input type="text" name="choice<?= $n ?>" class="form-control" required>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="col-md-12">
                                <label class="form-label">Correct Answer</label>
                                <select name="correct_choice" class="form-select" required>
                                    <option value="1">Choice 1</option>
                                    <option value="2">Choice 2</option>
                                    <option value="3">Choice 3</option>
                                    <option value="4">Choice 4</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <hr>
                                <h6>Or Upload Excel File</h6>
                                <input type="file" class="form-control" accept=".xlsx,.xls">
                                <small class="text-muted">
                                    Excel format: Question | Choice1 | Choice2 | Choice3 | Choice4 | CorrectChoice(1-4)
                                </small>
                            </div>
                        </div>
                        <!-- Carry origin and return path -->
                        <input type="hidden" name="origin" id="originField" value="">
                        <input type="hidden" name="return_to" id="returnToField" value="">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_question" class="btn btn-primary">Save Question</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <button class="btn btn-primary btn-lg rounded-pill add-question-btn" 
            data-bs-toggle="modal" data-bs-target="#addQuestionModal">
        <i class="bi bi-plus-lg me-2"></i>Add Question
    </button>
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
    // Utility to load topics for a given course and enable the topic select
    function loadTopics(courseId) {
        const topicSelect = document.getElementById('topicSelect');
        topicSelect.disabled = true;
        topicSelect.innerHTML = '<option value="">Loading topics...</option>';
        if (!courseId) {
            topicSelect.innerHTML = '<option value="">Select Topic</option>';
            return Promise.resolve();
        }
        return fetch(`get_topics.php?course_id=${courseId}`)
            .then(response => response.json())
            .then(topics => {
                topicSelect.innerHTML = '<option value="">Select Topic</option>';
                topics.forEach(topic => {
                    const option = document.createElement('option');
                    option.value = topic.topic_id;
                    option.textContent = topic.topic_name;
                    topicSelect.appendChild(option);
                });
                topicSelect.disabled = false;
            })
            .catch(() => {
                topicSelect.innerHTML = '<option value="">Failed to load topics</option>';
            });
    }

    // Load topics when course selection changes
    document.getElementById('courseSelect').addEventListener('change', function() {
        const courseId = this.value;
        loadTopics(courseId);
    });

    // Deep-link support: preselect course/topic; only auto-open modal when URL hash is '#add'
    document.addEventListener('DOMContentLoaded', async () => {
        const params = new URLSearchParams(window.location.search);
        const preCourse = params.get('course_id');
        const preTopic = params.get('topic_id');
        const shouldOpen = window.location.hash === '#add';

        if (preCourse) {
            const courseSelect = document.getElementById('courseSelect');
            courseSelect.value = preCourse;
            await loadTopics(preCourse);
            if (preTopic) {
                const topicSelect = document.getElementById('topicSelect');
                topicSelect.value = preTopic;
            }
        }

        if (shouldOpen) {
            const modalEl = document.getElementById('addQuestionModal');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        }

        // Show alert based on status param
        const status = params.get('status');
        if (status === 'success') {
            alert('Question added successfully.');
        } else if (status === 'error') {
            alert('There was an error adding the question.');
        }

        // Set hidden origin/return_to fields for POST handling
        const origin = params.get('origin');
        const returnTo = params.get('return_to');
        if (origin) document.getElementById('originField').value = origin;
        if (returnTo) document.getElementById('returnToField').value = returnTo;

        // If user closes modal and origin=topics, redirect back to topics page
        const modalEl = document.getElementById('addQuestionModal');
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', () => {
                const currentOrigin = document.getElementById('originField').value;
                const currentReturn = document.getElementById('returnToField').value;
                if (currentOrigin === 'topics' && currentReturn) {
                    const expandCourseId = preCourse || '';
                    const url = expandCourseId ? `${currentReturn}?expand_course_id=${encodeURIComponent(expandCourseId)}` : currentReturn;
                    window.location.href = url;
                }
            });
        }
    });
    </script>
</body>
</html>
