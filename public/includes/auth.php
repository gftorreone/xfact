<?php
require_once __DIR__ . '/db.php';

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        header('Location: /index.php');
        exit;
    }
}

function require_role(string $role): void
{
    if (!current_user() || current_user()['role'] !== $role) {
        http_response_code(403);
        echo 'Acceso denegado';
        exit;
    }
}

function attempt_login(string $username, string $password): bool
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];
        return true;
    }
    return false;
}

function logout(): void
{
    session_destroy();
    header('Location: /index.php');
    exit;
}
