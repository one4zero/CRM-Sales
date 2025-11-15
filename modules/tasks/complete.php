<?php
require_once '../../config/paths.php';
require_once '../../includes/auth.php';
require_once '../../config/database.php';

checkAuth();

$taskId = intval($_GET['id'] ?? 0);
$userId = getCurrentUserId();

if ($taskId > 0) {
    // Отметить задачу как выполненную
    if (isAdmin()) {
        $stmt = executeQuery("UPDATE tasks SET completed = 1 WHERE id = ?", [$taskId], 'i');
    } else {
        $stmt = executeQuery("UPDATE tasks SET completed = 1 WHERE id = ? AND assigned_to = ?", [$taskId, $userId], 'ii');
    }

    if ($stmt) {
        header('Location: ' . getUrl('modules/tasks/index.php?success=completed');
    } else {
        header('Location: ' . getUrl('modules/tasks/index.php?error=complete_failed');
    }
} else {
    header('Location: ' . getUrl('modules/tasks/index.php');
}
exit();
