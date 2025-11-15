<?php
$pageTitle = 'Добавить задачу';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$userId = getCurrentUserId();

// Получение списка менеджеров (только для администратора)
if (isAdmin()) {
    $managers = fetchResults("SELECT id, full_name FROM users WHERE role = 'manager' ORDER BY full_name");
}

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['title'] = sanitizeInput($_POST['title'] ?? '');
    $formData['description'] = sanitizeInput($_POST['description'] ?? '');
    $formData['due_date'] = sanitizeInput($_POST['due_date'] ?? '');
    $formData['assigned_to'] = isAdmin() ? intval($_POST['assigned_to'] ?? 0) : $userId;

    // Валидация
    if (empty($formData['title'])) {
        $errors[] = 'Название задачи обязательно для заполнения';
    }

    if (empty($formData['due_date'])) {
        $errors[] = 'Укажите срок выполнения';
    }

    if (isAdmin() && $formData['assigned_to'] <= 0) {
        $errors[] = 'Выберите ответственного';
    }

    // Если нет ошибок, добавляем задачу
    if (empty($errors)) {
        $query = "INSERT INTO tasks (title, description, due_date, assigned_to) VALUES (?, ?, ?, ?)";
        $description = !empty($formData['description']) ? $formData['description'] : null;

        $stmt = executeQuery($query, [
            $formData['title'],
            $description,
            $formData['due_date'],
            $formData['assigned_to']
        ], 'sssi');

        if ($stmt) {
            header('Location: index.php?success=added');
            exit();
        } else {
            $errors[] = 'Ошибка при добавлении задачи';
        }
    }
}
?>

<?php require_once '../../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="top-header">
        <div class="header-title">
            <h1><i class="fas fa-plus"></i> Добавить задачу</h1>
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
                <i class="fas fa-plus"></i> Добавление новой задачи
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
                        <div class="col-md-8 mb-3">
                            <label for="title" class="form-label">
                                <i class="fas fa-heading"></i> Название задачи <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="title" name="title"
                                   value="<?php echo htmlspecialchars($formData['title'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="due_date" class="form-label">
                                <i class="fas fa-calendar"></i> Срок выполнения <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="due_date" name="due_date"
                                   value="<?php echo htmlspecialchars($formData['due_date'] ?? ''); ?>"
                                   required>
                        </div>

                        <?php if (isAdmin()): ?>
                            <div class="col-md-12 mb-3">
                                <label for="assigned_to" class="form-label">
                                    <i class="fas fa-user-tie"></i> Ответственный <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="assigned_to" name="assigned_to" required>
                                    <option value="">Выберите ответственного</option>
                                    <?php foreach ($managers as $manager): ?>
                                        <option value="<?php echo $manager['id']; ?>"
                                                <?php echo ($formData['assigned_to'] ?? 0) == $manager['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($manager['full_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

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
