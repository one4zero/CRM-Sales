<?php
$pageTitle = 'Управление клиентами';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$userId = getCurrentUserId();
$userRole = getCurrentUserRole();

// Поиск
$search = sanitizeInput($_GET['search'] ?? '');

// Получение списка клиентов
if (isAdmin()) {
    if (!empty($search)) {
        $clients = fetchResults("
            SELECT c.*, u.full_name as created_by_name
            FROM clients c
            JOIN users u ON c.created_by = u.id
            WHERE c.full_name LIKE ? OR c.phone LIKE ? OR c.company LIKE ?
            ORDER BY c.created_at DESC
        ", ["%$search%", "%$search%", "%$search%"], 'sss');
    } else {
        $clients = fetchResults("
            SELECT c.*, u.full_name as created_by_name
            FROM clients c
            JOIN users u ON c.created_by = u.id
            ORDER BY c.created_at DESC
        ");
    }
} else {
    if (!empty($search)) {
        $clients = fetchResults("
            SELECT c.*, u.full_name as created_by_name
            FROM clients c
            JOIN users u ON c.created_by = u.id
            WHERE c.created_by = ? AND (c.full_name LIKE ? OR c.phone LIKE ? OR c.company LIKE ?)
            ORDER BY c.created_at DESC
        ", [$userId, "%$search%", "%$search%", "%$search%"], 'isss');
    } else {
        $clients = fetchResults("
            SELECT c.*, u.full_name as created_by_name
            FROM clients c
            JOIN users u ON c.created_by = u.id
            WHERE c.created_by = ?
            ORDER BY c.created_at DESC
        ", [$userId], 'i');
    }
}
?>

<?php require_once '../../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="top-header">
        <div class="header-title">
            <h1><i class="fas fa-users"></i> Управление клиентами</h1>
        </div>
        <div class="header-user">
            <div class="user-info">
                <span class="user-name"><?php echo getCurrentUserName(); ?></span>
                <span class="user-role">
                    <?php echo isAdmin() ? 'Администратор' : 'Менеджер'; ?>
                </span>
            </div>
            <a href="/logout.php" class="btn btn-logout">
                <i class="fas fa-sign-out-alt"></i> Выход
            </a>
        </div>
    </div>

    <div class="content-area">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                <?php
                    if ($_GET['success'] == 'added') echo 'Клиент успешно добавлен';
                    elseif ($_GET['success'] == 'updated') echo 'Данные клиента успешно обновлены';
                    elseif ($_GET['success'] == 'deleted') echo 'Клиент успешно удален';
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-users"></i> Список клиентов</span>
                <a href="add.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Добавить клиента
                </a>
            </div>
            <div class="card-body">
                <!-- Поиск -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" name="search" class="form-control"
                                       placeholder="Поиск по имени, телефону или компании"
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Найти
                            </button>
                        </div>
                        <?php if (!empty($search)): ?>
                            <div class="col-md-2">
                                <a href="<?php echo getUrl('modules/clients/index.php'); ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Сбросить
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Таблица клиентов -->
                <?php if (empty($clients)): ?>
                    <p class="text-muted">
                        <?php echo !empty($search) ? 'Клиенты не найдены' : 'Нет добавленных клиентов'; ?>
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ФИО</th>
                                    <th>Телефон</th>
                                    <th>Email</th>
                                    <th>Компания</th>
                                    <?php if (isAdmin()): ?>
                                        <th>Добавил</th>
                                    <?php endif; ?>
                                    <th>Дата создания</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clients as $client): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($client['full_name']); ?></td>
                                        <td>
                                            <i class="fas fa-phone"></i>
                                            <?php echo htmlspecialchars($client['phone']); ?>
                                        </td>
                                        <td>
                                            <?php if ($client['email']): ?>
                                                <i class="fas fa-envelope"></i>
                                                <?php echo htmlspecialchars($client['email']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($client['company'] ?? '-'); ?></td>
                                        <?php if (isAdmin()): ?>
                                            <td><?php echo htmlspecialchars($client['created_by_name']); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo date('d.m.Y', strtotime($client['created_at'])); ?></td>
                                        <td class="table-actions">
                                            <a href="edit.php?id=<?php echo $client['id']; ?>"
                                               class="btn btn-sm btn-warning" title="Редактировать">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $client['id']; ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Вы уверены, что хотите удалить этого клиента?')"
                                               title="Удалить">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
