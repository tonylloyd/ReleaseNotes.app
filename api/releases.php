<?php
// api/releases.php - JSON API Endpoint for Widget Badge Check

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow external sites to query the badge status

require_once __DIR__ . '/../includes/db.php';

try {
    $stmt = $pdo->query("SELECT id, title, type, created_at FROM releases ORDER BY created_at DESC LIMIT 1");
    $latest = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($latest) {
        echo json_encode([
            'success' => true,
            'latest_id' => (int)$latest['id'],
            'type' => $latest['type'],
            'title' => $latest['title']
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'latest_id' => 0,
            'type' => 'minor',
            'title' => ''
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
}
