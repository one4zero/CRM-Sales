<?php
/**
 * Конфигурация путей проекта
 * Определяет базовые пути для корректной работы приложения
 */

// Определение корневой директории проекта
define('ROOT_PATH', dirname(__DIR__));

// Базовый URL (автоматическое определение)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

// Определение базового пути из URL
// Находим директорию, где находится config (т.е. на уровень выше)
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$documentRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/'));
$rootPath = str_replace('\\', '/', ROOT_PATH);

// Вычисляем базовый путь относительно document root
if (strpos($rootPath, $documentRoot) === 0) {
    $basePath = substr($rootPath, strlen($documentRoot));
} else {
    // Если не можем вычислить от document root, используем script name
    // Удаляем имя файла и все после /modules/ или других директорий
    $basePath = $scriptPath;
    if (strpos($basePath, '/modules/') !== false) {
        $basePath = substr($basePath, 0, strpos($basePath, '/modules/'));
    } else {
        $basePath = dirname($basePath);
    }
}

$basePath = rtrim($basePath, '/');

define('BASE_URL', $protocol . '://' . $host . $basePath);
define('BASE_PATH', $basePath);

// Пути к директориям
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('MODULES_PATH', ROOT_PATH . '/modules');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('DATABASE_PATH', ROOT_PATH . '/database');

// URL пути для ресурсов
define('ASSETS_URL', BASE_PATH . '/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');

// Функция для получения абсолютного URL
function getUrl($path = '') {
    return BASE_PATH . '/' . ltrim($path, '/');
}

// Функция для получения абсолютного пути к файлу
function getPath($path = '') {
    return ROOT_PATH . '/' . ltrim($path, '/');
}
