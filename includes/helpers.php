<?php
/**
 * HELPER FUNCTIONS FOR LEARNING OUTCOMES DASHBOARD
 */

/**
 * Get courses taught by a specific lecturer
 *
 * @param PDO $pdo Database connection
 * @param int $lecturerId Lecturer ID
 * @return array Array of courses
 */
// Check if function already exists to avoid redeclaration errors
if (!function_exists('getLecturerCourses')) {
function getLecturerCourses(PDO $pdo, int $lecturerId): array
{
    try {
        $stmt = $pdo->prepare("
            SELECT c.course_id, c.course_name
            FROM courses c
            JOIN schedule s ON c.course_id = s.course_id
            WHERE s.lecturer_id = :lecturer_id
            GROUP BY c.course_id
            ORDER BY c.course_name
        ");
        $stmt->execute(['lecturer_id' => $lecturerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getLecturerCourses: " . $e->getMessage());
        return [];
    }
}
} // End of if (!function_exists('getLecturerCourses'))

/**
 * Get learning outcomes with filtering options
 *
 * @param PDO $pdo Database connection
 * @param int|null $courseId Filter by course ID
 * @param string|null $loType Filter by LO type (a-k)
 * @param int|null $lecturerId Filter by lecturer ID
 * @return array Array of learning outcomes
 */
// Check if function already exists to avoid redeclaration errors
if (!function_exists('getLearningOutcomes')) {
function getLearningOutcomes(PDO $pdo, ?int $courseId = null, ?string $loType = null, ?int $lecturerId = null): array
{
    try {
        $query = "SELECT
                    lo.lo_id,
                    lo.lo_symbol,
                    lo.lo_description,
                    c.course_name,
                    t.topic_name,
                    COUNT(DISTINCT t.course_id) AS course_count,
                    COUNT(q.question_id) AS question_count,
                    COALESCE(AVG(sa.result), 0) * 100 AS mastery_percentage,
                    GROUP_CONCAT(DISTINCT q.question_text SEPARATOR '|') AS sample_questions
                  FROM learning_outcome lo
                  JOIN topics t ON lo.topic_id = t.topic_id
                  JOIN courses c ON t.course_id = c.course_id
                  LEFT JOIN questions q ON t.topic_id = q.topic_id
                  LEFT JOIN student_answers sa ON q.question_id = sa.question_id";

        $params = [];
        $conditions = [];

        if ($courseId) {
            $conditions[] = "t.course_id = :course_id";
            $params['course_id'] = $courseId;
        }

        if ($loType && $loType !== 'all') {
            $conditions[] = "lo.lo_symbol LIKE :lo_type";
            $params['lo_type'] = "$loType%";
        }

        if ($lecturerId) {
            $query .= " LEFT JOIN schedule s ON c.course_id = s.course_id AND s.lecturer_id = :lecturer_id";
            $conditions[] = "(s.lecturer_id = :lecturer_id OR :lecturer_id = 0)";
            $params['lecturer_id'] = $lecturerId;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " GROUP BY lo.lo_id, lo.lo_symbol, lo.lo_description, c.course_name, t.topic_name";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getLearningOutcomes: " . $e->getMessage());
        return [];
    }
}
} // End of if (!function_exists('getLearningOutcomes'))

/**
 * Render learning outcome cards with enhanced UI
 *
 * @param array $outcomes Array of learning outcomes
 * @return string HTML string
 */
// function renderLoCards(array $outcomes): string
// {
//     if (empty($outcomes)) {
//         return '<div class="alert alert-info">No learning outcomes found</div>';
//     }

//     $html = '';
//     foreach ($outcomes as $lo) {
//         // Safely extract LO codes and descriptions
//         $codes = [
//             !empty($lo['lo1']) ? explode(':', $lo['lo1'])[0] : 'LO1',
//             !empty($lo['lo2']) ? explode(':', $lo['lo2'])[0] : 'LO2'
//         ];

//         $descriptions = [
//             !empty($lo['lo1']) ? substr($lo['lo1'], strpos($lo['lo1'], ':') + 2) : 'No description',
//             !empty($lo['lo2']) ? substr($lo['lo2'], strpos($lo['lo2'], ':') + 2) : 'No description'
//         ];

//         $mastery = isset($lo['mastery_percentage']) ? round($lo['mastery_percentage'], 1) : 0;
//         $questionCount = $lo['question_count'] ?? 0;
//         $courseName = $lo['course_name'] ?? 'Multiple Courses';

//         // Determine progress bar color based on mastery level
//         $progressClass = 'bg-primary';
//         if ($mastery >= 80) $progressClass = 'bg-success';
//         elseif ($mastery < 50) $progressClass = 'bg-danger';
//         elseif ($mastery < 70) $progressClass = 'bg-warning';

//         $html .= <<<HTML
//         <div class="lo-card card mb-4 shadow-sm">
//             <div class="card-header d-flex justify-content-between align-items-center">
//                 <div>
//                     <h5 class="mb-0">{$codes[0]}/{$codes[1]}</h5>
//                     <small class="text-muted">{$courseName}</small>
//                 </div>
//                 <span class="badge bg-secondary">{$questionCount} questions</span>
//             </div>
//             <div class="card-body">
//                 <div class="mb-3">
//                     <div class="d-flex justify-content-between mb-1">
//                         <span>Mastery Level</span>
//                         <span>{$mastery}%</span>
//                     </div>
//                     <div class="progress" style="height: 10px;">
//                         <div class="progress-bar {$progressClass}"
//                              role="progressbar"
//                              style="width: {$mastery}%"
//                              aria-valuenow="{$mastery}"
//                              aria-valuemin="0"
//                              aria-valuemax="100"></div>
//                     </div>
//                 </div>

//                 <div class="mb-3">
//                     <h6 class="card-subtitle mb-2 text-muted">Learning Outcome Descriptions</h6>
//                     <ul class="list-unstyled">
//                         <li><strong>{$codes[0]}:</strong> {$descriptions[0]}</li>
//                         <li><strong>{$codes[1]}:</strong> {$descriptions[1]}</li>
//                     </ul>
//                 </div>
// HTML;

//         if (!empty($lo['sample_questions'])) {
//             $html .= <<<HTML
//                 <div class="sample-questions">
//                     <h6 class="card-subtitle mb-2 text-muted">Sample Questions</h6>
//                     <div class="list-group">
// HTML;

//             $questions = explode('|', $lo['sample_questions']);
//             foreach (array_slice($questions, 0, 3) as $question) {
//                 $html .= <<<HTML
//                         <div class="list-group-item py-2">
//                             <div class="d-flex justify-content-between align-items-center">
//                                 <span>{$question}</span>
//                                 <span class="badge bg-light text-dark">LO {$codes[0]}/{$codes[1]}</span>
//                             </div>
//                         </div>
// HTML;
//             }

//             if (count($questions) > 3) {
//                 $html .= <<<HTML
//                         <button class="btn btn-sm btn-link text-decoration-none"
//                                 type="button"
//                                 data-bs-toggle="collapse"
//                                 data-bs-target="#moreQuestions-{$codes[0]}-{$codes[1]}">
//                             Show more (+{count($questions) - 3})
//                         </button>
//                         <div class="collapse" id="moreQuestions-{$codes[0]}-{$codes[1]}">
// HTML;
//                 foreach (array_slice($questions, 3) as $question) {
//                     $html .= <<<HTML
//                             <div class="list-group-item py-2">
//                                 <div class="d-flex justify-content-between align-items-center">
//                                     <span>{$question}</span>
//                                     <span class="badge bg-light text-dark">LO {$codes[0]}/{$codes[1]}</span>
//                                 </div>
//                             </div>
// HTML;
//                 }
//                 $html .= '</div>';
//             }

//             $html .= <<<HTML
//                     </div>
//                 </div>
// HTML;
//         }

//         $html .= <<<HTML
//             </div>
//         </div>
// HTML;
//     }

//     return $html;
// }

/**
 * Render learning outcome cards with enhanced UI
 *
 * @param array $outcomes Array of learning outcomes
 * @return string HTML string
 */
// Check if function already exists to avoid redeclaration errors
if (!function_exists('renderLoCards')) {
function renderLoCards(array $outcomes): string
{
    if (empty($outcomes)) {
        return '<div class="alert alert-info">No learning outcomes found</div>';
    }

    $html = '';
    foreach ($outcomes as $lo) {
        // Safely extract LO symbol and description
        $loSymbol = !empty($lo['lo_symbol']) && is_string($lo['lo_symbol']) ? trim($lo['lo_symbol']) : 'LO';
        $loDescription = !empty($lo['lo_description']) && is_string($lo['lo_description']) ? trim($lo['lo_description']) : 'No description';

        $mastery = isset($lo['mastery_percentage']) ? round((float)$lo['mastery_percentage'], 1) : 0;
        $questionCount = $lo['question_count'] ?? 0;
        $courseName = $lo['course_name'] ?? 'Multiple Courses';

        // Determine progress bar color based on mastery level
        $progressClass = 'bg-primary';
        if ($mastery >= 80) $progressClass = 'bg-success';
        elseif ($mastery < 50) $progressClass = 'bg-danger';
        elseif ($mastery < 70) $progressClass = 'bg-warning';

        $html .= <<<HTML
        <div class="lo-card card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">{$loSymbol}</h5>
                    <small class="text-muted">{$courseName}</small>
                </div>
                <span class="badge bg-secondary">{$questionCount} questions</span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Mastery Level</span>
                        <span>{$mastery}%</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar {$progressClass}"
                             role="progressbar"
                             style="width: {$mastery}%"
                             aria-valuenow="{$mastery}"
                             aria-valuemin="0"
                             aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <h6 class="card-subtitle mb-2 text-muted">Learning Outcome Description</h6>
                    <ul class="list-unstyled">
                        <li><strong>{$loSymbol}:</strong> {$loDescription}</li>
                    </ul>
                </div>
HTML;

        if (!empty($lo['sample_questions']) && is_string($lo['sample_questions'])) {
            $html .= <<<HTML
                <div class="sample-questions">
                    <h6 class="card-subtitle mb-2 text-muted">Sample Questions</h6>
                    <div class="list-group">
HTML;

            $questions = explode('|', $lo['sample_questions']);
            $questions = array_filter($questions); // Remove empty questions
            $questions = array_slice($questions, 0, 3); // Limit to 3 initially

            foreach ($questions as $question) {
                $html .= <<<HTML
                        <div class="list-group-item py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>{$question}</span>
                                <span class="badge bg-light text-dark">LO {$loSymbol}</span>
                            </div>
                        </div>
HTML;
            }

            $totalQuestions = count(explode('|', $lo['sample_questions']));
            if ($totalQuestions > 3) {
                $remaining = $totalQuestions - 3;
                $html .= <<<HTML
                        <button class="btn btn-sm btn-link text-decoration-none"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#moreQuestions-{$loSymbol}">
                            Show more (+{$remaining})
                        </button>
                        <div class="collapse" id="moreQuestions-{$loSymbol}">
HTML;
                $allQuestions = explode('|', $lo['sample_questions']);
                foreach (array_slice($allQuestions, 3) as $question) {
                    if (!empty(trim($question))) {
                        $html .= <<<HTML
                            <div class="list-group-item py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>{$question}</span>
                                    <span class="badge bg-light text-dark">LO {$loSymbol}</span>
                                </div>
                            </div>
HTML;
                    }
                }
                $html .= '</div>';
            }

            $html .= <<<HTML
                    </div>
                </div>
HTML;
        }

        $html .= <<<HTML
            </div>
        </div>
HTML;
    }

    return $html;
}
} // End of if (!function_exists('renderLoCards'))

/**
 * Get all available courses
 *
 * @param PDO $pdo Database connection
 * @return array Array of courses
 */
// Check if function already exists to avoid redeclaration errors
if (!function_exists('getCourses')) {
function getCourses(PDO $pdo): array
{
    try {
        $stmt = $pdo->query("SELECT course_id, course_name FROM courses ORDER BY course_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getCourses: " . $e->getMessage());
        return [];
    }
}
} // End of if (!function_exists('getCourses'))

// Centralized 404 responder
if (!function_exists('not_found')) {
function not_found(): void
{
    http_response_code(404);
    $path = __DIR__ . '/../404.html';
    if (is_file($path)) {
        readfile($path);
    } else {
        echo '<h1>404 Not Found</h1>';
    }
    exit;
}
}