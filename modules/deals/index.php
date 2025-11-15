<?php
$pageTitle = 'Управление сделками';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$userId = getCurrentUserId();

// Фильтры
$statusFilter = sanitizeInput($_GET['status'] ?? '');

// Получение списка сделок
if (isAdmin()) {
    if (!empty($statusFilter)) {
        $deals = fetchResults("
            SELECT d.*, c.full_name as client_name, u.full_name as manager_name
            FROM deals d
            JOIN clients c ON d.client_id = c.id
            JOIN users u ON d.manager_id = u.id
            WHERE d.status = ?
            ORDER BY d.created_at DESC
        ", [$statusFilter], 's');
    } else {
        $deals = fetchResults("
            SELECT d.*, c.full_name as client_name, u.full_name as manager_name
            FROM deals d
            JOIN clients c ON d.client_id = c.id
            JOIN users u ON d.manager_id = u.id
            ORDER BY d.created_at DESC
        ");
    }
} else {
    if (!empty($statusFilter)) {
        $deals = fetchResults("
            SELECT d.*, c.full_name as client_name, u.full_name as manager_name
            FROM deals d
            JOIN clients c ON d.client_id = c.id
            JOIN users u ON d.manager_id = u.id
            WHERE d.manager_id = ? AND d.status = ?
            ORDER BY d.created_at DESC
        ", [$userId, $statusFilter], 'is');
    } else {
        $deals = fetchResults("
            SELECT d.*, c.full_name as client_name, u.full_name as manager_name
            FROM deals d
            JOIN clients c ON d.client_id = c.id
            JOIN users u ON d.manager_id = u.id
            WHERE d.manager_id = ?
            ORDER BY d.created_at DESC
        ", [$userId], 'i');
    }
}

$statusLabels = [
    'new' => 'Новая',
    'in_progress' => 'В работе',
    'completed' => 'Завершена',
    'cancelled' => 'Отменена'
];

$badges = [
    'new' => 'badge-new',
    'in_progress' => 'badge-in-progress',
    'completed' => 'badge-completed',
    'cancelled' => 'badge-cancelled'
];
?>

<?php require_once '../../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="top-header">
        <div class="header-title">
            <h1><i class="fas fa-handshake"></i> Управление сделками</h1>
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
                    if ($_GET['success'] == 'added') echo 'Сделка успешно добавлена';
                    elseif ($_GET['success'] == 'updated') echo 'Сделка успешно обновлена';
                    elseif ($_GET['success'] == 'deleted') echo 'Сделка успешно удалена';
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-handshake"></i> Список сделок</span>
                <a href="add.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Добавить сделку
                </a>
            </div>
            <div class="card-body">
                <!-- Фильтр по статусу -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Фильтр по статусу:</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Все статусы</option>
                                <?php foreach ($statusLabels as $value => $label): ?>
                                    <option value="<?php echo $value; ?>"
                                            <?php echo $statusFilter === $value ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if (!empty($statusFilter)): ?>
                            <div class="col-md-2 d-flex align-items-end">
                                <a href="<?php echo getUrl('modules/deals/index.php'); ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Сбросить
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Таблица сделок -->
                <?php if (empty($deals)): ?>
                    <p class="text-muted">Нет сделок</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Клиент</th>
                                    <th>Сумма</th>
                                    <th>Статус</th>
                                    <?php if (isAdmin()): ?>
                                        <th>Менеджер</th>
                                    <?php endif; ?>
                                    <th>Дата создания</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deals as $deal): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($deal['title']); ?></td>
                                        <td><?php echo htmlspecialchars($deal['client_name']); ?></td>
                                        <td><?php echo number_format($deal['amount'], 0, ',', ' '); ?> ₽</td>
                                        <td>
                                            <span class="badge <?php echo $badges[$deal['status']]; ?>">
                                                <?php echo $statusLabels[$deal['status']]; ?>
                                            </span>
                                        </td>
                                        <?php if (isAdmin()): ?>
                                            <td><?php echo htmlspecialchars($deal['manager_name']); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo date('d.m.Y', strtotime($deal['created_at'])); ?></td>
                                        <td class="table-actions">
                                            <a href="edit.php?id=<?php echo $deal['id']; ?>"
                                               class="btn btn-sm btn-warning" title="Редактировать">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $deal['id']; ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Вы уверены, что хотите удалить эту сделку?')"
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
