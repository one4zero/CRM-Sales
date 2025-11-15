<?php
require_once '../../config/paths.php';
require_once '../../includes/auth.php';
require_once '../../config/database.php';

checkAuth();

$clientId = intval($_GET['id'] ?? 0);
$userId = getCurrentUserId();

if ($clientId > 0) {
    // Проверка прав доступа
    if (isAdmin()) {
        $stmt = executeQuery("DELETE FROM clients WHERE id = ?", [$clientId], 'i');
    } else {
        $stmt = executeQuery("DELETE FROM clients WHERE id = ? AND created_by = ?", [$clientId, $userId], 'ii');
    }

    if ($stmt) {
        header('Location: ' . getUrl('modules/clients/index.php?success=deleted');
    } else {
        header('Location: ' . getUrl('modules/clients/index.php?error=delete_failed');
    }
} else {
    header('Location: ' . getUrl('modules/clients/index.php');
}
exit();
