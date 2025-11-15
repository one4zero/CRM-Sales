<?php
$pageTitle = 'Добавить сделку';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$userId = getCurrentUserId();

// Получение списка клиентов
if (isAdmin()) {
    $clients = fetchResults("SELECT id, full_name, company FROM clients ORDER BY full_name");
    $managers = fetchResults("SELECT id, full_name FROM users WHERE role = 'manager' ORDER BY full_name");
} else {
    $clients = fetchResults("SELECT id, full_name, company FROM clients WHERE created_by = ? ORDER BY full_name", [$userId], 'i');
}

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['client_id'] = intval($_POST['client_id'] ?? 0);
    $formData['title'] = sanitizeInput($_POST['title'] ?? '');
    $formData['amount'] = floatval($_POST['amount'] ?? 0);
    $formData['description'] = sanitizeInput($_POST['description'] ?? '');
    $formData['status'] = sanitizeInput($_POST['status'] ?? 'new');
    $formData['manager_id'] = isAdmin() ? intval($_POST['manager_id'] ?? 0) : $userId;

    // Валидация
    if ($formData['client_id'] <= 0) {
        $errors[] = 'Выберите клиента';
    }

    if (empty($formData['title'])) {
        $errors[] = 'Название сделки обязательно для заполнения';
    }

    if ($formData['amount'] <= 0) {
        $errors[] = 'Сумма сделки должна быть больше 0';
    }

    if (isAdmin() && $formData['manager_id'] <= 0) {
        $errors[] = 'Выберите менеджера';
    }

    // Если нет ошибок, добавляем сделку
    if (empty($errors)) {
        $query = "INSERT INTO deals (client_id, title, amount, description, status, manager_id) VALUES (?, ?, ?, ?, ?, ?)";
        $description = !empty($formData['description']) ? $formData['description'] : null;

        $stmt = executeQuery($query, [
            $formData['client_id'],
            $formData['title'],
            $formData['amount'],
            $description,
            $formData['status'],
            $formData['manager_id']
        ], 'isdssi');

        if ($stmt) {
            header('Location: index.php?success=added');
            exit();
        } else {
            $errors[] = 'Ошибка при добавлении сделки';
        }
    }
}
?>

<?php require_once '../../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="top-header">
        <div class="header-title">
            <h1><i class="fas fa-plus"></i> Добавить сделку</h1>
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
        <div class="card">
            <div class="card-header">
                <i class="fas fa-plus"></i> Добавление новой сделки
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="client_id" class="form-label">
                                <i class="fas fa-user"></i> Клиент <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="client_id" name="client_id" required>
                                <option value="">Выберите клиента</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?php echo $client['id']; ?>"
                                            <?php echo ($formData['client_id'] ?? 0) == $client['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($client['full_name']); ?>
                                        <?php if ($client['company']): ?>
                                            (<?php echo htmlspecialchars($client['company']); ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if (isAdmin()): ?>
                            <div class="col-md-6 mb-3">
                                <label for="manager_id" class="form-label">
                                    <i class="fas fa-user-tie"></i> Менеджер <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="manager_id" name="manager_id" required>
                                    <option value="">Выберите менеджера</option>
                                    <?php foreach ($managers as $manager): ?>
                                        <option value="<?php echo $manager['id']; ?>"
                                                <?php echo ($formData['manager_id'] ?? 0) == $manager['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($manager['full_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">
                                <i class="fas fa-heading"></i> Название сделки <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="title" name="title"
                                   value="<?php echo htmlspecialchars($formData['title'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">
                                <i class="fas fa-ruble-sign"></i> Сумма (₽) <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="amount" name="amount"
                                   value="<?php echo htmlspecialchars($formData['amount'] ?? ''); ?>"
                                   min="0" step="0.01" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">
                                <i class="fas fa-flag"></i> Статус
                            </label>
                            <select class="form-select" id="status" name="status">
                                <option value="new" <?php echo ($formData['status'] ?? 'new') == 'new' ? 'selected' : ''; ?>>Новая</option>
                                <option value="in_progress" <?php echo ($formData['status'] ?? '') == 'in_progress' ? 'selected' : ''; ?>>В работе</option>
                                <option value="completed" <?php echo ($formData['status'] ?? '') == 'completed' ? 'selected' : ''; ?>>Завершена</option>
                                <option value="cancelled" <?php echo ($formData['status'] ?? '') == 'cancelled' ? 'selected' : ''; ?>>Отменена</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left"></i> Описание
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Сохранить
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
