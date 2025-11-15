<?php
require_once '../../config/paths.php';
require_once '../../includes/auth.php';
require_once '../../config/database.php';

checkAuth();

$dealId = intval($_GET['id'] ?? 0);
$userId = getCurrentUserId();

if ($dealId > 0) {
    // Проверка прав доступа
    if (isAdmin()) {
        $stmt = executeQuery("DELETE FROM deals WHERE id = ?", [$dealId], 'i');
    } else {
        $stmt = executeQuery("DELETE FROM deals WHERE id = ? AND manager_id = ?", [$dealId, $userId], 'ii');
    }

    if ($stmt) {
        header('Location: ' . getUrl('modules/deals/index.php?success=deleted');
    } else {
        header('Location: ' . getUrl('modules/deals/index.php?error=delete_failed');
    }
} else {
    header('Location: ' . getUrl('modules/deals/index.php');
}
exit();
