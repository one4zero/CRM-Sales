<?php
/**
 * Система авторизации и управления сессиями
 */

// Запуск сессии, если еще не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/**
 * Проверка авторизации пользователя
 */
function checkAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        header('Location: /login.php');
        exit();
    }
}

/**
 * Проверка прав администратора
 */
function checkAdmin() {
    checkAuth();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: /dashboard.php');
        exit();
    }
}

/**
 * Получить ID текущего пользователя
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Получить роль текущего пользователя
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Получить имя текущего пользователя
 */
function getCurrentUserName() {
    return $_SESSION['user_name'] ?? '';
}

/**
 * Проверка является ли пользователь администратором
 */
function isAdmin() {
    return getCurrentUserRole() === 'admin';
}

/**
 * Аутентификация пользователя
 */
function authenticate($username, $password) {
    $query = "SELECT id, username, password, full_name, email, role FROM users WHERE username = ?";
    $user = fetchRow($query, [$username], 's');

    if ($user && password_verify($password, $user['password'])) {
        // Установка данных сессии
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['username'] = $user['username'];

        return true;
    }

    return false;
}

/**
 * Выход из системы
 */
function logout() {
    session_unset();
    session_destroy();
    header('Location: /login.php');
    exit();
}

/**
 * Проверка CSRF токена
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Валидация CSRF токена
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Валидация email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Валидация телефона
 */
function validatePhone($phone) {
    $pattern = '/^\+?[0-9\s\-\(\)]{10,20}$/';
    return preg_match($pattern, $phone);
}

/**
 * Очистка и валидация входных данных
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
