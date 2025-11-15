<?php
$pageTitle = 'Главная панель';
require_once 'config/paths.php';
require_once 'includes/header.php';
require_once 'config/database.php';

// Получение статистики
$userId = getCurrentUserId();
$userRole = getCurrentUserRole();

// Общее количество клиентов
if (isAdmin()) {
    $clientsCount = fetchRow("SELECT COUNT(*) as count FROM clients")['count'];
} else {
    $clientsCount = fetchRow("SELECT COUNT(*) as count FROM clients WHERE created_by = ?", [$userId], 'i')['count'];
}

// Общее количество сделок
if (isAdmin()) {
    $dealsCount = fetchRow("SELECT COUNT(*) as count FROM deals")['count'];
    $dealsSum = fetchRow("SELECT SUM(amount) as total FROM deals WHERE status != 'cancelled'")['total'] ?? 0;
} else {
    $dealsCount = fetchRow("SELECT COUNT(*) as count FROM deals WHERE manager_id = ?", [$userId], 'i')['count'];
    $dealsSum = fetchRow("SELECT SUM(amount) as total FROM deals WHERE manager_id = ? AND status != 'cancelled'", [$userId], 'i')['total'] ?? 0;
}

// Количество активных задач
if (isAdmin()) {
    $tasksCount = fetchRow("SELECT COUNT(*) as count FROM tasks WHERE completed = 0")['count'];
} else {
    $tasksCount = fetchRow("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = ? AND completed = 0", [$userId], 'i')['count'];
}

// Сделки по статусам
if (isAdmin()) {
    $dealsByStatus = fetchResults("SELECT status, COUNT(*) as count FROM deals GROUP BY status");
} else {
    $dealsByStatus = fetchResults("SELECT status, COUNT(*) as count FROM deals WHERE manager_id = ? GROUP BY status", [$userId], 'i');
}

$statusCounts = [
    'new' => 0,
    'in_progress' => 0,
    'completed' => 0,
    'cancelled' => 0
];

foreach ($dealsByStatus as $status) {
    $statusCounts[$status['status']] = $status['count'];
}

// Последние сделки
if (isAdmin()) {
    $recentDeals = fetchResults("
        SELECT d.*, c.full_name as client_name, u.full_name as manager_name
        FROM deals d
        JOIN clients c ON d.client_id = c.id
        JOIN users u ON d.manager_id = u.id
        ORDER BY d.created_at DESC
        LIMIT 5
    ");
} else {
    $recentDeals = fetchResults("
        SELECT d.*, c.full_name as client_name, u.full_name as manager_name
        FROM deals d
        JOIN clients c ON d.client_id = c.id
        JOIN users u ON d.manager_id = u.id
        WHERE d.manager_id = ?
        ORDER BY d.created_at DESC
        LIMIT 5
    ", [$userId], 'i');
}

// Ближайшие задачи
if (isAdmin()) {
    $upcomingTasks = fetchResults("
        SELECT t.*, u.full_name as assigned_name
        FROM tasks t
        JOIN users u ON t.assigned_to = u.id
        WHERE t.completed = 0
        ORDER BY t.due_date ASC
        LIMIT 5
    ");
} else {
    $upcomingTasks = fetchResults("
        SELECT t.*, u.full_name as assigned_name
        FROM tasks t
        JOIN users u ON t.assigned_to = u.id
        WHERE t.assigned_to = ? AND t.completed = 0
        ORDER BY t.due_date ASC
        LIMIT 5
    ", [$userId], 'i');
}
?>

<?php require_once 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="top-header">
        <div class="header-title">
            <h1><i class="fas fa-home"></i> Главная панель</h1>
        </div>
        <div class="header-user">
            <div class="user-info">
                <span class="user-name"><?php echo getCurrentUserName(); ?></span>
                <span class="user-role">
                    <?php echo isAdmin() ? 'Администратор' : 'Менеджер'; ?>
                </span>
            </div>
            <a href="<?php echo getUrl('logout.php'); ?>" class="btn btn-logout">
                <i class="fas fa-sign-out-alt"></i> Выход
            </a>
        </div>
    </div>

    <div class="content-area">
        <!-- Статистические карточки -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $clientsCount; ?></div>
                    <div class="stat-label">Клиентов</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card dark">
                    <div class="stat-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="stat-value"><?php echo $dealsCount; ?></div>
                    <div class="stat-label">Сделок</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-ruble-sign"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($dealsSum, 0, ',', ' '); ?></div>
                    <div class="stat-label">Сумма сделок (₽)</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card dark">
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-value"><?php echo $tasksCount; ?></div>
                    <div class="stat-label">Активных задач</div>
                </div>
            </div>
        </div>

        <!-- Воронка продаж -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-filter"></i> Воронка продаж
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h3 class="text-info"><?php echo $statusCounts['new']; ?></h3>
                                <p>Новые</p>
                            </div>
                            <div class="col-md-3">
                                <h3 class="text-warning"><?php echo $statusCounts['in_progress']; ?></h3>
                                <p>В работе</p>
                            </div>
                            <div class="col-md-3">
                                <h3 class="text-success"><?php echo $statusCounts['completed']; ?></h3>
                                <p>Завершены</p>
                            </div>
                            <div class="col-md-3">
                                <h3 class="text-danger"><?php echo $statusCounts['cancelled']; ?></h3>
                                <p>Отменены</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Последние сделки -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-handshake"></i> Последние сделки
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentDeals)): ?>
                            <p class="text-muted">Нет сделок</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Клиент</th>
                                            <th>Сумма</th>
                                            <th>Статус</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentDeals as $deal): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($deal['client_name']); ?></td>
                                                <td><?php echo number_format($deal['amount'], 0, ',', ' '); ?> ₽</td>
                                                <td>
                                                    <?php
                                                    $badges = [
                                                        'new' => 'badge-new',
                                                        'in_progress' => 'badge-in-progress',
                                                        'completed' => 'badge-completed',
                                                        'cancelled' => 'badge-cancelled'
                                                    ];
                                                    $labels = [
                                                        'new' => 'Новая',
                                                        'in_progress' => 'В работе',
                                                        'completed' => 'Завершена',
                                                        'cancelled' => 'Отменена'
                                                    ];
                                                    ?>
                                                    <span class="badge <?php echo $badges[$deal['status']]; ?>">
                                                        <?php echo $labels[$deal['status']]; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <a href="<?php echo getUrl('modules/deals/index.php'); ?>" class="btn btn-sm btn-primary mt-2">
                                <i class="fas fa-arrow-right"></i> Все сделки
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Ближайшие задачи -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-tasks"></i> Ближайшие задачи
                    </div>
                    <div class="card-body">
                        <?php if (empty($upcomingTasks)): ?>
                            <p class="text-muted">Нет активных задач</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($upcomingTasks as $task): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($task['title']); ?></h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar"></i>
                                                    <?php echo date('d.m.Y', strtotime($task['due_date'])); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($task['assigned_name']); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <a href="<?php echo getUrl('modules/tasks/index.php'); ?>" class="btn btn-sm btn-primary mt-2">
                                <i class="fas fa-arrow-right"></i> Все задачи
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
