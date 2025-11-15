<?php
/**
 * Главная страница - перенаправление
 */
session_start();
require_once 'config/paths.php';

// Если пользователь авторизован, перенаправляем на dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . getUrl('dashboard.php'));
} else {
    header('Location: ' . getUrl('login.php'));
}
exit();
