<?php
// Global configuration and database bootstrap for Mess Management System

declare(strict_types=1);

// Start session early for PHP pages that rely on it
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Paths
const DATA_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'data';
const DB_PATH  = DATA_DIR . DIRECTORY_SEPARATOR . 'app.sqlite';

if (!is_dir(DATA_DIR)) {
    @mkdir(DATA_DIR, 0775, true);
}

function getDb(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    initializeDatabase($pdo);
    return $pdo;
}

function initializeDatabase(PDO $pdo): void {
    // users
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            user_id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT "admin",
            api_token TEXT,
            token_expires_at INTEGER,
            created_at INTEGER NOT NULL DEFAULT (strftime("%s", "now"))
        )'
    );

    // students (minimal for joins)
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS students (
            student_id INTEGER PRIMARY KEY AUTOINCREMENT,
            roll_number TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL
        )'
    );

    // mess_menu
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS mess_menu (
            menu_id INTEGER PRIMARY KEY AUTOINCREMENT,
            date TEXT NOT NULL,
            meal_type TEXT NOT NULL CHECK (meal_type IN ("Breakfast","Lunch","Snacks","Dinner")),
            items TEXT NOT NULL,
            category TEXT,
            fee REAL NOT NULL DEFAULT 0.0,
            created_at INTEGER NOT NULL DEFAULT (strftime("%s", "now")),
            UNIQUE(date, meal_type)
        )'
    );

    // mess_tokens (extended fields for this app)
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS mess_tokens (
            token_id INTEGER PRIMARY KEY AUTOINCREMENT,
            student_roll_number TEXT,
            menu_id INTEGER,
            token_type TEXT NOT NULL DEFAULT "Paid",
            meal_type TEXT,
            from_date TEXT,
            to_date TEXT,
            items TEXT,
            special_fee REAL DEFAULT 0.0,
            created_at INTEGER NOT NULL DEFAULT (strftime("%s", "now")),
            FOREIGN KEY(menu_id) REFERENCES mess_menu(menu_id) ON DELETE CASCADE
        )'
    );

    // seed admin user if not exists
    $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM users WHERE username = :u');
    $stmt->execute([':u' => 'admin']);
    if ((int)$stmt->fetchColumn() === 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO users (username, password, role) VALUES (:u, :p, :r)')
            ->execute([':u' => 'admin', ':p' => $hash, ':r' => 'admin']);
    }

    // seed one demo student
    $stmt = $pdo->query('SELECT COUNT(*) FROM students');
    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->prepare('INSERT INTO students (roll_number, name) VALUES (?, ?)')
            ->execute(['22CS001', 'Demo Student']);
    }
}

function jsonResponse(array $payload, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function getJsonInput(): array {
    $raw = file_get_contents('php://input') ?: '';
    if ($raw === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function getAuthorizationHeader(): ?string {
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim((string)$_SERVER['Authorization']);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim((string)$_SERVER['HTTP_AUTHORIZATION']);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim((string)$requestHeaders['Authorization']);
        }
    }
    return $headers ?: null;
}

function getBearerToken(): ?string {
    $header = getAuthorizationHeader();
    if (!$header) return null;
    if (stripos($header, 'Bearer ') === 0) {
        return trim(substr($header, 7));
    }
    return null;
}

function requireAuth(PDO $pdo): array {
    // Allow if already in session
    if (!empty($_SESSION['user_id'])) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = :id');
        $stmt->execute([':id' => (int)$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user) return $user;
    }
    // Or via Bearer token
    $token = getBearerToken();
    if ($token) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE api_token = :t');
        $stmt->execute([':t' => $token]);
        $user = $stmt->fetch();
        if ($user) {
            // Optional expiry check
            if (!empty($user['token_expires_at']) && time() > (int)$user['token_expires_at']) {
                jsonResponse(['success' => false, 'message' => 'Token expired'], 401);
            }
            return $user;
        }
    }
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}
