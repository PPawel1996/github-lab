
<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$dataFile = __DIR__ . '/../storage/data.json';

function readData(string $file): array {
    if (!file_exists($file)) return [];
    $raw = file_get_contents($file);
    if ($raw === false || trim($raw) === '') return [];
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function writeData(string $file, array $data): void {
    $dir = dirname($file);
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    http_response_code(200);
    echo json_encode(readData($dataFile), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === 'POST') {
    $rawBody = file_get_contents('php://input') ?: '';
    $input = json_decode($rawBody, true);

    $title = '';
    if (is_array($input) && isset($input['title'])) {
        $title = trim((string)$input['title']);
    }

    if (mb_strlen($title) < 3) {
        http_response_code(400);
        echo json_encode(['error' => 'title must be at least 3 characters'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $items = readData($dataFile);

    $maxId = 0;
    foreach ($items as $it) {
        $maxId = max($maxId, (int)($it['id'] ?? 0));
    }

    $newItem = [
        'id' => $maxId + 1,
        'title' => $title,
        'createdAt' => gmdate('c'),
    ];

    $items[] = $newItem;
    writeData($dataFile, $items);

    http_response_code(201);
    echo json_encode($newItem, JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
I ustaw plik:
•	backend/storage/data.json na:
[]
