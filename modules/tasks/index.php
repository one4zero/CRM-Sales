<?php
$pageTitle = 'Управление задачами';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$userId = getCurrentUserId();

// Фильтр по статусу
$completedFilter = isset($_GET['completed']) ? intval($_GET['completed']) : -1;

// Получение списка задач
if (isAdmin()) {
    if ($completedFilter >= 0) {
        $tasks = fetchResults("
            SELECT t.*, u.full_name as assigned_name
            FROM tasks t
            JOIN users u ON t.assigned_to = u.id
            WHERE t.completed = ?
            ORDER BY t.due_date ASC, t.created_at DESC
        ", [$completedFilter], 'i');
    } else {
        $tasks = fetchResults("
            SELECT t.*, u.full_name as assigned_name
            FROM tasks t
            JOIN users u ON t.assigned_to = u.id
            ORDER BY t.completed ASC, t.due_date ASC
        ");
    }
} else {
    if ($completedFilter >= 0) {
        $tasks = fetchResults("
            SELECT t.*, u.full_name as assigned_name
            FROM tasks t
            JOIN users u ON t.assigned_to = u.id
            WHERE t.assigned_to = ? AND t.completed = ?
            ORDER BY t.due_date ASC, t.created_at DESC
        ", [$userId, $completedFilter], 'ii');
    } else {
        $tasks = fetchResults("
            SELECT t.*, u.full_name as assigned_name
            FROM tasks t
            JOIN users u ON t.assigned_to = u.id
            WHERE t.assigned_to = ?
            ORDER BY t.completed ASC, t.due_date ASC
        ", [$userId], 'i');
    }
}
?>

<?php require_once '../../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="top-header">
        <div class="header-title">
            <h1><i class="fas fa-tasks"></i> Управление задачами</h1>
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
                    if ($_GET['success'] == 'added') echo 'Задача успешно добавлена';
                    elseif ($_GET['success'] == 'updated') echo 'Задача успешно обновлена';
                    elseif ($_GET['success'] == 'deleted') echo 'Задача успешно удалена';
                    elseif ($_GET['success'] == 'completed') echo 'Задача отмечена как выполненная';
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-tasks"></i> Список задач</span>
                <a href="add.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Добавить задачу
                </a>
            </div>
            <div class="card-body">
                <!-- Фильтр -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Фильтр по статусу:</label>
                            <select name="completed" class="form-select" onchange="this.form.submit()">
                                <option value="-1" <?php echo $completedFilter === -1 ? 'selected' : ''; ?>>Все задачи</option>
                                <option value="0" <?php echo $completedFilter === 0 ? 'selected' : ''; ?>>Активные</option>
                                <option value="1" <?php echo $completedFilter === 1 ? 'selected' : ''; ?>>Выполненные</option>
                            </select>
                        </div>
                        <?php if ($completedFilter >= 0): ?>
                            <div class="col-md-2 d-flex align-items-end">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Сбросить
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Таблица задач -->
                <?php if (empty($tasks)): ?>
                    <p class="text-muted">Нет задач</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Статус</th>
                                    <th>Название</th>
                                    <th>Срок</th>
                                    <?php if (isAdmin()): ?>
                                        <th>Ответственный</th>
                                    <?php endif; ?>
                                    <th>Создана</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasks as $task): ?>
                                    <?php
                                    $isOverdue = !$task['completed'] && strtotime($task['due_date']) < time();
                                    $rowClass = $task['completed'] ? 'table-secondary' : ($isOverdue ? 'table-danger' : '');
                                    ?>
                                    <tr class="<?php echo $rowClass; ?>">
                                        <td>
                                            <?php if ($task['completed']): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check"></i> Выполнена
                                                </span>
                                            <?php else: ?>
                                                <a href="complete.php?id=<?php echo $task['id']; ?>"
                                                   class="btn btn-sm btn-outline-success"
                                                   title="Отметить как выполненную">
                                                    <i class="far fa-square"></i> Активна
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($task['title']); ?></strong>
                                            <?php if ($task['description']): ?>
                                                <br><small class="text-muted">
                                                    <?php echo htmlspecialchars(substr($task['description'], 0, 100)); ?>
                                                    <?php echo strlen($task['description']) > 100 ? '...' : ''; ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('d.m.Y', strtotime($task['due_date'])); ?>
                                            <?php if ($isOverdue): ?>
                                                <br><span class="badge bg-danger">Просрочена</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if (isAdmin()): ?>
                                            <td><?php echo htmlspecialchars($task['assigned_name']); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo date('d.m.Y', strtotime($task['created_at'])); ?></td>
                                        <td class="table-actions">
                                            <?php if (!$task['completed']): ?>
                                                <a href="edit.php?id=<?php echo $task['id']; ?>"
                                                   class="btn btn-sm btn-warning" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="delete.php?id=<?php echo $task['id']; ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Вы уверены, что хотите удалить эту задачу?')"
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
