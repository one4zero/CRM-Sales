<?php
$pageTitle = 'Отчеты и аналитика';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$userId = getCurrentUserId();

// Фильтр по периоду
$period = sanitizeInput($_GET['period'] ?? 'month');

// Определение дат для фильтра
$dateFrom = '';
$dateTo = date('Y-m-d');

switch ($period) {
    case 'month':
        $dateFrom = date('Y-m-01');
        $periodLabel = 'За текущий месяц';
        break;
    case 'quarter':
        $currentMonth = date('n');
        $quarterStartMonth = floor(($currentMonth - 1) / 3) * 3 + 1;
        $dateFrom = date('Y-' . str_pad($quarterStartMonth, 2, '0', STR_PAD_LEFT) . '-01');
        $periodLabel = 'За текущий квартал';
        break;
    case 'year':
        $dateFrom = date('Y-01-01');
        $periodLabel = 'За текущий год';
        break;
    default:
        $dateFrom = date('Y-m-01');
        $periodLabel = 'За текущий месяц';
}

// Общая статистика
if (isAdmin()) {
    $totalClients = fetchRow("SELECT COUNT(*) as count FROM clients WHERE created_at >= ?", [$dateFrom], 's')['count'];
    $totalDeals = fetchRow("SELECT COUNT(*) as count FROM deals WHERE created_at >= ?", [$dateFrom], 's')['count'];
    $totalRevenue = fetchRow("SELECT SUM(amount) as total FROM deals WHERE status = 'completed' AND created_at >= ?", [$dateFrom], 's')['total'] ?? 0;
    $totalTasks = fetchRow("SELECT COUNT(*) as count FROM tasks WHERE created_at >= ?", [$dateFrom], 's')['count'];
} else {
    $totalClients = fetchRow("SELECT COUNT(*) as count FROM clients WHERE created_by = ? AND created_at >= ?", [$userId, $dateFrom], 'is')['count'];
    $totalDeals = fetchRow("SELECT COUNT(*) as count FROM deals WHERE manager_id = ? AND created_at >= ?", [$userId, $dateFrom], 'is')['count'];
    $totalRevenue = fetchRow("SELECT SUM(amount) as total FROM deals WHERE manager_id = ? AND status = 'completed' AND created_at >= ?", [$userId, $dateFrom], 'is')['total'] ?? 0;
    $totalTasks = fetchRow("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = ? AND created_at >= ?", [$userId, $dateFrom], 'is')['count'];
}

// Статистика по статусам сделок
if (isAdmin()) {
    $dealsByStatus = fetchResults("
        SELECT status, COUNT(*) as count, SUM(amount) as total
        FROM deals
        WHERE created_at >= ?
        GROUP BY status
    ", [$dateFrom], 's');
} else {
    $dealsByStatus = fetchResults("
        SELECT status, COUNT(*) as count, SUM(amount) as total
        FROM deals
        WHERE manager_id = ? AND created_at >= ?
        GROUP BY status
    ", [$userId, $dateFrom], 'is');
}

// Топ менеджеров по продажам (только для администратора)
if (isAdmin()) {
    $topManagers = fetchResults("
        SELECT u.full_name, COUNT(d.id) as deals_count, SUM(d.amount) as total_revenue
        FROM users u
        LEFT JOIN deals d ON u.id = d.manager_id AND d.status = 'completed' AND d.created_at >= ?
        WHERE u.role = 'manager'
        GROUP BY u.id, u.full_name
        ORDER BY total_revenue DESC
        LIMIT 5
    ", [$dateFrom], 's');
}

// Топ клиентов по сумме сделок
if (isAdmin()) {
    $topClients = fetchResults("
        SELECT c.full_name, c.company, SUM(d.amount) as total_amount, COUNT(d.id) as deals_count
        FROM clients c
        LEFT JOIN deals d ON c.id = d.client_id AND d.status = 'completed' AND d.created_at >= ?
        GROUP BY c.id, c.full_name, c.company
        HAVING total_amount > 0
        ORDER BY total_amount DESC
        LIMIT 5
    ", [$dateFrom], 's');
} else {
    $topClients = fetchResults("
        SELECT c.full_name, c.company, SUM(d.amount) as total_amount, COUNT(d.id) as deals_count
        FROM clients c
        LEFT JOIN deals d ON c.id = d.client_id AND d.status = 'completed' AND d.manager_id = ? AND d.created_at >= ?
        WHERE c.created_by = ?
        GROUP BY c.id, c.full_name, c.company
        HAVING total_amount > 0
        ORDER BY total_amount DESC
        LIMIT 5
    ", [$userId, $dateFrom, $userId], 'isi');
}

$statusLabels = [
    'new' => 'Новые',
    'in_progress' => 'В работе',
    'completed' => 'Завершены',
    'cancelled' => 'Отменены'
];
?>

<?php require_once '../../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="top-header">
        <div class="header-title">
            <h1><i class="fas fa-chart-bar"></i> Отчеты и аналитика</h1>
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
        <!-- Фильтр по периоду -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row align-items-center">
                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-calendar"></i> Период отчета:</label>
                        <select name="period" class="form-select" onchange="this.form.submit()">
                            <option value="month" <?php echo $period == 'month' ? 'selected' : ''; ?>>Текущий месяц</option>
                            <option value="quarter" <?php echo $period == 'quarter' ? 'selected' : ''; ?>>Текущий квартал</option>
                            <option value="year" <?php echo $period == 'year' ? 'selected' : ''; ?>>Текущий год</option>
                        </select>
                    </div>
                    <div class="col-md-8 text-muted">
                        <i class="fas fa-info-circle"></i> Отчет: <?php echo $periodLabel; ?>
                        (<?php echo date('d.m.Y', strtotime($dateFrom)); ?> - <?php echo date('d.m.Y'); ?>)
                    </div>
                </form>
            </div>
        </div>

        <!-- Общая статистика -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $totalClients; ?></div>
                    <div class="stat-label">Новых клиентов</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card dark">
                    <div class="stat-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="stat-value"><?php echo $totalDeals; ?></div>
                    <div class="stat-label">Сделок</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-ruble-sign"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($totalRevenue, 0, ',', ' '); ?></div>
                    <div class="stat-label">Выручка (₽)</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card dark">
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-value"><?php echo $totalTasks; ?></div>
                    <div class="stat-label">Задач создано</div>
                </div>
            </div>
        </div>

        <!-- Статистика по статусам сделок -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-pie"></i> Сделки по статусам
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Статус</th>
                                        <th>Количество</th>
                                        <th>Сумма (₽)</th>
                                        <th>Процент от общего числа</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dealsByStatus as $status): ?>
                                        <tr>
                                            <td><?php echo $statusLabels[$status['status']]; ?></td>
                                            <td><?php echo $status['count']; ?></td>
                                            <td><?php echo number_format($status['total'] ?? 0, 0, ',', ' '); ?> ₽</td>
                                            <td><?php echo $totalDeals > 0 ? round($status['count'] / $totalDeals * 100, 1) : 0; ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Топ менеджеров (только для администратора) -->
            <?php if (isAdmin()): ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-trophy"></i> Топ менеджеров по продажам
                        </div>
                        <div class="card-body">
                            <?php if (empty($topManagers) || !array_filter($topManagers, fn($m) => $m['total_revenue'] > 0)): ?>
                                <p class="text-muted">Нет данных за выбранный период</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Менеджер</th>
                                                <th>Сделок</th>
                                                <th>Выручка (₽)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($topManagers as $idx => $manager): ?>
                                                <?php if ($manager['total_revenue'] > 0): ?>
                                                    <tr>
                                                        <td>
                                                            <?php if ($idx == 0): ?>
                                                                <i class="fas fa-medal text-warning"></i>
                                                            <?php endif; ?>
                                                            <?php echo htmlspecialchars($manager['full_name']); ?>
                                                        </td>
                                                        <td><?php echo $manager['deals_count']; ?></td>
                                                        <td><?php echo number_format($manager['total_revenue'], 0, ',', ' '); ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Топ клиентов -->
            <div class="<?php echo isAdmin() ? 'col-md-6' : 'col-md-12'; ?>">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-star"></i> Топ клиентов по сумме сделок
                    </div>
                    <div class="card-body">
                        <?php if (empty($topClients)): ?>
                            <p class="text-muted">Нет данных за выбранный период</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Клиент</th>
                                            <th>Сделок</th>
                                            <th>Сумма (₽)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($topClients as $idx => $client): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($idx == 0): ?>
                                                        <i class="fas fa-crown text-warning"></i>
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($client['full_name']); ?>
                                                    <?php if ($client['company']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($client['company']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $client['deals_count']; ?></td>
                                                <td><?php echo number_format($client['total_amount'], 0, ',', ' '); ?></td>
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
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
