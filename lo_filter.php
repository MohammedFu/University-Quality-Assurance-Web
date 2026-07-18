<?php
// Start output buffering and session
ob_start();
session_start();

// Enable error logging but not display
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Set headers first to prevent any output issues
header('Content-Type: application/json');

try {
    // Verify required includes
    $includes = [
        __DIR__ . '/includes/auth.php',
        __DIR__ . '/includes/db.php',
        __DIR__ . '/includes/helpers.php'
    ];

    foreach ($includes as $include) {
        if (!file_exists($include)) {
            throw new Exception("Required file missing: " . basename($include), 500);
        }
        require_once $include;
    }

    // Validate session
    if (!isLoggedIn() || $_SESSION['role'] !== 'lecturer') {
        throw new Exception('Unauthorized access', 401);
    }

    // Validate and sanitize parameters
    $courseId = isset($_GET['course_id']) && is_numeric($_GET['course_id']) ? (int)$_GET['course_id'] : null;
    $loType = isset($_GET['lo_type']) && preg_match('/^[a-k]$|^all$/', $_GET['lo_type']) ? $_GET['lo_type'] : 'all';
    $lecturerId = (int)$_SESSION['user_id'];

    // Get filtered data
    $learningOutcomes = getFilteredLearningOutcomes($pdo, $courseId, $loType, $lecturerId);

    // Debug: Count learning outcomes
    $loCount = count($learningOutcomes);

    // Prepare response
    $response = [
        'success' => true,
        'html' => renderLoCards($learningOutcomes),
        'chart_data' => prepareChartData($learningOutcomes),
        'debug' => [
            'lo_count' => $loCount,
            'course_id' => $courseId,
            'lo_type' => $loType,
            'lecturer_id' => $lecturerId
        ]
    ];

    // Clear buffer and send JSON
    ob_end_clean();
    echo json_encode($response);
    exit();

} catch (PDOException $e) {
    // Log the error
    error_log("PDO Exception in lo_filter.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());

    // Clean output buffer
    ob_end_clean();

    // Set HTTP status code
    http_response_code(500);

    // Return detailed error information
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
    exit();
} catch (Exception $e) {
    // Log the error
    error_log("Exception in lo_filter.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());

    // Clean output buffer
    ob_end_clean();

    // Set HTTP status code
    http_response_code($e->getCode() >= 400 ? $e->getCode() : 500);

    // Return detailed error information
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
    exit();
}
/**
 * Get filtered learning outcomes with lecturer validation
 * This is a local version of the function to avoid conflicts with helpers.php
 */
function getFilteredLearningOutcomes(PDO $pdo, ?int $courseId, string $loType, int $lecturerId): array {
    try {
        // Simplified query to avoid complex joins that might cause issues
        $sql = "
            SELECT
                lo.lo_id,
                lo.lo_symbol,
                lo.lo_description,
                c.course_name,
                t.topic_name,
                COUNT(DISTINCT q.question_id) AS question_count,
                COALESCE(AVG(sa.result), 0) * 100 AS mastery_percentage,
                GROUP_CONCAT(DISTINCT q.question_text SEPARATOR '|') AS sample_questions
            FROM learning_outcome lo
            JOIN topics t ON lo.topic_id = t.topic_id
            JOIN courses c ON t.course_id = c.course_id
            LEFT JOIN questions q ON t.topic_id = q.topic_id
            LEFT JOIN student_answers sa ON q.question_id = sa.question_id
        ";

        // Build WHERE clause
        $whereConditions = [];
        $params = [];

        // Add course filter if provided
        if ($courseId !== null) {
            $whereConditions[] = "t.course_id = :course_id";
            $params['course_id'] = $courseId;
        }

        // Add learning outcome type filter if not 'all'
        if ($loType !== 'all') {
            $whereConditions[] = "lo.lo_symbol LIKE :lo_type";
            $params['lo_type'] = $loType . '%';
        }

        // Add lecturer filter if provided
        if ($lecturerId > 0) {
            $sql .= " LEFT JOIN schedule s ON c.course_id = s.course_id";
            $whereConditions[] = "s.lecturer_id = :lecturer_id";
            $params['lecturer_id'] = $lecturerId;
        }

        // Add WHERE clause if we have conditions
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }

        // Add GROUP BY and ORDER BY
        $sql .= "
            GROUP BY lo.lo_id, lo.lo_symbol, lo.lo_description, c.course_name, t.topic_name
            ORDER BY lo.lo_symbol
        ";

        // Prepare and execute the query
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Return the results
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Log the error
        error_log("Error in getLearningOutcomes: " . $e->getMessage());

        // Return empty array to avoid breaking the application
        return [];
    }
}

/**
 * Prepare chart data from learning outcomes
 */
function prepareChartData(array $learningOutcomes): array {
    $chartData = [
        'labels' => [],
        'question_counts' => [],
        'mastery_percentages' => []
    ];

    foreach ($learningOutcomes as $lo) {
        $loSymbol = !empty($lo['lo_symbol']) ? $lo['lo_symbol'] : 'LO';

        $chartData['labels'][] = $loSymbol;
        $chartData['question_counts'][] = (int)($lo['question_count'] ?? 0);
        $chartData['mastery_percentages'][] = (float)($lo['mastery_percentage'] ?? 0);
    }

    return $chartData;
}

/**
 * Render learning outcome cards
 */
function renderLoCards(array $learningOutcomes): string {
    if (empty($learningOutcomes)) {
        return '<div class="alert alert-info">No learning outcomes match the selected filters</div>';
    }

    ob_start();
    foreach ($learningOutcomes as $lo) {
        $loSymbol = !empty($lo['lo_symbol']) ? htmlspecialchars($lo['lo_symbol']) : 'LO';
        $loDescription = !empty($lo['lo_description']) ? htmlspecialchars($lo['lo_description']) : '';
        $questionCount = (int)($lo['question_count'] ?? 0);
        $mastery = round((float)($lo['mastery_percentage'] ?? 0), 1);
        $courseName = htmlspecialchars($lo['course_name'] ?? '');
        $sampleQuestions = !empty($lo['sample_questions']) ? explode('|', $lo['sample_questions']) : [];
        ?>
        <div class="card mb-4 shadow-sm lo-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0"><?= $loSymbol ?></h5>
                        <?php if ($courseName): ?>
                            <small class="text-muted"><?= $courseName ?></small>
                        <?php endif; ?>
                    </div>
                    <span class="badge bg-primary"><?= $questionCount ?> Questions</span>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Mastery Level</span>
                        <span><?= $mastery ?>%</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar <?= getMasteryClass($mastery) ?>"
                             role="progressbar"
                             style="width: <?= $mastery ?>%"
                             aria-valuenow="<?= $mastery ?>"
                             aria-valuemin="0"
                             aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <h6 class="card-subtitle mb-2 text-muted">Description</h6>
                    <ul class="list-unstyled">
                        <?php if ($loDescription): ?>
                            <li><strong><?= $loSymbol ?>:</strong> <?= $loDescription ?></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <?php if (!empty($sampleQuestions)): ?>
                <div class="sample-questions">
                    <h6 class="card-subtitle mb-2 text-muted">Sample Questions</h6>
                    <div class="list-group">
                        <?php foreach (array_slice($sampleQuestions, 0, 3) as $question): ?>
                            <div class="list-group-item py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><?= htmlspecialchars($question) ?></span>
                                    <span class="badge bg-light text-dark">LO <?= $loSymbol ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (count($sampleQuestions) > 3): ?>
                            <button class="btn btn-sm btn-link text-decoration-none"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#moreQuestions-<?= $loSymbol ?>">
                                Show more (+<?= count($sampleQuestions) - 3 ?>)
                            </button>
                            <div class="collapse" id="moreQuestions-<?= $loSymbol ?>">
                                <?php foreach (array_slice($sampleQuestions, 3) as $question): ?>
                                    <div class="list-group-item py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><?= htmlspecialchars($question) ?></span>
                                            <span class="badge bg-light text-dark">LO <?= $loSymbol ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    return ob_get_clean();
}

/**
 * Get appropriate CSS class based on mastery percentage
 */
function getMasteryClass(float $percentage): string {
    if ($percentage >= 80) return 'bg-success';
    if ($percentage >= 50) return 'bg-primary';
    return 'bg-danger';
}