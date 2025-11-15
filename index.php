<?php
/**
 * Главная страница - перенаправление
 */
session_start();

// Если пользователь авторизован, перенаправляем на dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit();
