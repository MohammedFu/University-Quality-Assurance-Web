<?php
require __DIR__ . '/includes/db.php';

if (isset($_GET['course_id'])) {
    $courseId = $_GET['course_id'];
    $query = $pdo->prepare("SELECT topic_id, topic_name FROM topics WHERE course_id = ?");
    $query->execute([$courseId]);
    $topics = $query->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($topics);
    exit();
}