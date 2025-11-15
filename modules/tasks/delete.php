<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';

checkAuth();

$taskId = intval($_GET['id'] ?? 0);
$userId = getCurrentUserId();

if ($taskId > 0) {
    // Проверка прав доступа
    if (isAdmin()) {
        $stmt = executeQuery("DELETE FROM tasks WHERE id = ?", [$taskId], 'i');
    } else {
        $stmt = executeQuery("DELETE FROM tasks WHERE id = ? AND assigned_to = ?", [$taskId, $userId], 'ii');
    }

    if ($stmt) {
        header('Location: ' . getUrl('modules/tasks/index.php?success=deleted');
    } else {
        header('Location: ' . getUrl('modules/tasks/index.php?error=delete_failed');
    }
} else {
    header('Location: ' . getUrl('modules/tasks/index.php');
}
exit();
