<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';

$pdo = getDb();

$pathInfo = $_SERVER['PATH_INFO'] ?? '/';
$method   = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function route(string $pathInfo): array {
    $pathInfo = trim($pathInfo, '/');
    if ($pathInfo === '') return ['resource' => '', 'id' => null];
    $parts = explode('/', $pathInfo);
    $resource = $parts[0] ?? '';
    $id = isset($parts[1]) && $parts[1] !== '' ? $parts[1] : null;
    return ['resource' => $resource, 'id' => $id];
}

function parseBody(): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        return getJsonInput();
    }
    return $_POST ?: getJsonInput();
}

$target = route($pathInfo);

switch ($target['resource']) {
    case 'auth':
        $data = parseBody();
        $action = $data['action'] ?? '';
        if ($method === 'POST' && $action === 'login') {
            $username = trim((string)($data['username'] ?? ''));
            $password = (string)($data['password'] ?? '');
            if ($username === '' || $password === '') {
                jsonResponse(['success' => false, 'message' => 'Username and password required'], 400);
            }
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :u');
            $stmt->execute([':u' => $username]);
            $user = $stmt->fetch();
            if (!$user || !password_verify($password, $user['password'])) {
                jsonResponse(['success' => false, 'message' => 'Invalid credentials'], 401);
            }
            $_SESSION['user_id'] = (int)$user['user_id'];
            // Mint simple token
            $token = bin2hex(random_bytes(24));
            $expires = time() + 86400; // 1 day
            $pdo->prepare('UPDATE users SET api_token = :t, token_expires_at = :e WHERE user_id = :id')
                ->execute([':t' => $token, ':e' => $expires, ':id' => (int)$user['user_id']]);
            jsonResponse(['success' => true, 'message' => 'Login successful', 'token' => $token, 'user' => [
                'user_id' => (int)$user['user_id'], 'username' => $user['username'], 'role' => $user['role']
            ]]);
        }
        if ($method === 'POST' && $action === 'logout') {
            session_destroy();
            jsonResponse(['success' => true, 'message' => 'Logged out']);
        }
        jsonResponse(['success' => false, 'message' => 'Unsupported auth action'], 400);

    case 'mess-menu':
        if ($method === 'GET' && $target['id'] === null) {
            // list
            $stmt = $pdo->query('SELECT * FROM mess_menu ORDER BY date DESC');
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
        }
        if ($method === 'GET' && $target['id'] !== null) {
            $stmt = $pdo->prepare('SELECT * FROM mess_menu WHERE menu_id = :id');
            $stmt->execute([':id' => (int)$target['id']]);
            $row = $stmt->fetch();
            if (!$row) jsonResponse(['success' => false, 'message' => 'Not found'], 404);
            jsonResponse(['success' => true, 'data' => $row]);
        }
        // write ops require auth
        requireAuth($pdo);
        if ($method === 'POST') {
            $data = parseBody();
            $stmt = $pdo->prepare('INSERT INTO mess_menu (date, meal_type, items, category, fee) VALUES (:date, :meal, :items, :cat, :fee)');
            $stmt->execute([
                ':date' => (string)($data['date'] ?? ''),
                ':meal' => (string)($data['meal_type'] ?? ''),
                ':items' => (string)($data['items'] ?? ''),
                ':cat' => $data['category'] ?? null,
                ':fee' => (float)($data['fee'] ?? 0),
            ]);
            jsonResponse(['success' => true, 'message' => 'Menu created']);
        }
        if ($method === 'PUT' && $target['id'] !== null) {
            $data = getJsonInput();
            $stmt = $pdo->prepare('UPDATE mess_menu SET date = :date, meal_type = :meal, items = :items, category = :cat, fee = :fee WHERE menu_id = :id');
            $stmt->execute([
                ':date' => (string)($data['date'] ?? ''),
                ':meal' => (string)($data['meal_type'] ?? ''),
                ':items' => (string)($data['items'] ?? ''),
                ':cat' => $data['category'] ?? null,
                ':fee' => (float)($data['fee'] ?? 0),
                ':id' => (int)$target['id'],
            ]);
            jsonResponse(['success' => true, 'message' => 'Menu updated']);
        }
        if ($method === 'DELETE' && $target['id'] !== null) {
            $stmt = $pdo->prepare('DELETE FROM mess_menu WHERE menu_id = :id');
            $stmt->execute([':id' => (int)$target['id']]);
            jsonResponse(['success' => true, 'message' => 'Menu deleted']);
        }
        jsonResponse(['success' => false, 'message' => 'Unsupported method'], 405);

    case 'mess-tokens':
        if ($method === 'GET' && $target['id'] === null) {
            $stmt = $pdo->query('SELECT mt.token_id, s.name AS student_name, mt.student_roll_number AS roll_number, m.meal_type, m.date AS menu_date, m.items, mt.token_type, mt.special_fee, mt.created_at FROM mess_tokens mt LEFT JOIN mess_menu m ON m.menu_id = mt.menu_id LEFT JOIN students s ON s.roll_number = mt.student_roll_number ORDER BY mt.created_at DESC');
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
        }
        if ($method === 'GET' && $target['id'] !== null) {
            $stmt = $pdo->prepare('SELECT mt.*, m.items, m.date AS menu_date FROM mess_tokens mt LEFT JOIN mess_menu m ON m.menu_id = mt.menu_id WHERE token_id = :id');
            $stmt->execute([':id' => (int)$target['id']]);
            $row = $stmt->fetch();
            if (!$row) jsonResponse(['success' => false, 'message' => 'Not found'], 404);
            jsonResponse(['success' => true, 'data' => $row]);
        }
        // write ops require auth
        requireAuth($pdo);
        if ($method === 'POST') {
            $data = parseBody();
            $stmt = $pdo->prepare('INSERT INTO mess_tokens (student_roll_number, menu_id, token_type, from_date, to_date, special_fee) VALUES (:srn, :mid, :tt, :fd, :td, :sf)');
            $stmt->execute([
                ':srn' => $data['student_roll_number'] ?? null,
                ':mid' => isset($data['menu_id']) ? (int)$data['menu_id'] : null,
                ':tt'  => (string)($data['token_type'] ?? 'Paid'),
                ':fd'  => $data['from_date'] ?? null,
                ':td'  => $data['to_date'] ?? null,
                ':sf'  => isset($data['special_fee']) ? (float)$data['special_fee'] : 0.0,
            ]);
            jsonResponse(['success' => true, 'message' => 'Token created']);
        }
        jsonResponse(['success' => false, 'message' => 'Unsupported method'], 405);

    case '':
        jsonResponse(['success' => true, 'message' => 'API is up']);

    default:
        jsonResponse(['success' => false, 'message' => 'Not found'], 404);
}
