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
// Вычисляем относительный путь от DOCUMENT_ROOT до корня проекта
$documentRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
$projectRoot = str_replace('\\', '/', ROOT_PATH);
$basePath = str_replace($documentRoot, '', $projectRoot);
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
