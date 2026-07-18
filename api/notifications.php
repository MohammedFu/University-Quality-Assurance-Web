<?php
header('Content-Type: application/json');
include '../includes/db.php';
include '../includes/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Example notifications data
$notifications = [
    [
        'icon' => 'info-circle',
        'message' => 'System maintenance scheduled for Saturday',
        'time' => '2 hours ago'
    ],
    [
        'icon' => 'exclamation-triangle',
        'message' => '3 pending quality reports need review',
        'time' => '5 hours ago'
    ]
];

echo json_encode($notifications);