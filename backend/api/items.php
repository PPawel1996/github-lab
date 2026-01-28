<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$dataFile = __DIR__ . '/../storage/data.json';

function respond(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function readJson(string $file): array {
    if (!file_exists($file)) return [];
    $raw = file_get_contents($file);
    if ($raw === false || trim($raw) === '') return [];
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function writeJson(string $file, array $data): void {
    $dir = dirname($file);
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    // Prosty lock, żeby nie popsuć JSON-a przy równoczesnym zapisie
    $fp = fopen($file, 'c+');
    if ($fp === false) {
        respond(500, ['error' => 'Cannot open storage file']);
    }
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        respond(500, ['error' => 'Cannot lock storage file']);
    }

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
}

function readBody(): array {
    $raw = file_get_contents('php://input') ?: '';
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function normalizeBool($value): ?bool {
    if (is_bool($value)) return $value;
    if (is_int($value)) return $value === 1 ? true : ($value === 0 ? false : null);
    if (is_string($value)) {
        $v = strtolower(trim($value));
        if ($v === 'true' || $v === '1') return true;
        if ($v === 'false' || $v === '0') return false;
    }
    return null;
}

function findIndexById(array $items, int $id): int {
    foreach ($items as $i => $it) {
        if ((int)($it['id'] ?? 0) === $id) return $i;
    }
    return -1;
}

function nextId(array $items): int {
    $max = 0;
    foreach ($items as $it) {
        $max = max($max, (int)($it['id'] ?? 0));
    }
    return $max + 1;
}

$method = $_SERVER['REQUEST_METHOD'];
$items = readJson($dataFile);

// PARAMS
$idParam = isset($_GET['id']) ? (int)$_GET['id'] : null;
$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$doneParam = isset($_GET['done']) ? normalizeBool($_GET['done']) : null;

// ROUTING
if ($method === 'GET') {
    $result = $items;

    // Filter by done
    if ($doneParam !== null) {
        $result = array_values(array_filter($result, fn($it) => (bool)($it['done'] ?? false) === $doneParam));
    }

    // Search by title
    if ($q !== '') {
        $needle = mb_strtolower($q);
        $result = array_values(array_filter($result, function($it) use ($needle) {
            $title = mb_strtolower((string)($it['title'] ?? ''));
            return mb_strpos($title, $needle) !== false;
        }));
    }

    respond(200, $result);
}

if ($method === 'POST') {
    $body = readBody();
    $title = isset($body['title']) ? trim((string)$body['title']) : '';
    if (mb_strlen($title) < 3) {
        respond(400, ['error' => 'title must be at least 3 characters']);
    }

    $new = [
        'id' => nextId($items),
        'title' => $title,
        'done' => false,
        'createdAt' => gmdate('c'),
        'updatedAt' => gmdate('c'),
    ];

    $items[] = $new;
    writeJson($dataFile, $items);

    respond(201, $new);
}

if ($method === 'PUT') {
    if ($idParam === null || $idParam <= 0) {
        respond(400, ['error' => 'id query param is required for PUT']);
    }

    $idx = findIndexById($items, $idParam);
    if ($idx < 0) {
        respond(404, ['error' => 'Item not found']);
    }

    $body = readBody();

    // Update title (optional)
    if (array_key_exists('title', $body)) {
        $title = trim((string)$body['title']);
        if (mb_strlen($title) < 3) {
            respond(400, ['error' => 'title must be at least 3 characters']);
        }
        $items[$idx]['title'] = $title;
    }

    // Update done (optional)
    if (array_key_exists('done', $body)) {
        $done = normalizeBool($body['done']);
        if ($done === null) {
            respond(400, ['error' => 'done must be boolean']);
        }
        $items[$idx]['done'] = $done;
    }

    $items[$idx]['updatedAt'] = gmdate('c');
    writeJson($dataFile, $items);

    respond(200, $items[$idx]);
}

if ($method === 'DELETE') {
    if ($idParam === null || $idParam <= 0) {
        respond(400, ['error' => 'id query param is required for DELETE']);
    }

    $idx = findIndexById($items, $idParam);
    if ($idx < 0) {
        respond(404, ['error' => 'Item not found']);
    }

    $deleted = $items[$idx];
    array_splice($items, $idx, 1);
    writeJson($dataFile, $items);

    respond(200, ['deleted' => $deleted]);
}

respond(405, ['error' => 'Method not allowed']);
Plik: backend/storage/data.json
[]
