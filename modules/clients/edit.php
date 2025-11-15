<?php
$pageTitle = 'Редактировать клиента';
require_once '../../config/paths.php';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$clientId = intval($_GET['id'] ?? 0);
$userId = getCurrentUserId();

// Получение данных клиента
if (isAdmin()) {
    $client = fetchRow("SELECT * FROM clients WHERE id = ?", [$clientId], 'i');
} else {
    $client = fetchRow("SELECT * FROM clients WHERE id = ? AND created_by = ?", [$clientId, $userId], 'ii');
}

if (!$client) {
    header('Location: ' . getUrl('modules/clients/index.php');
    exit();
}

$errors = [];
$formData = $client;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение и валидация данных
    $formData['full_name'] = sanitizeInput($_POST['full_name'] ?? '');
    $formData['phone'] = sanitizeInput($_POST['phone'] ?? '');
    $formData['email'] = sanitizeInput($_POST['email'] ?? '');
    $formData['company'] = sanitizeInput($_POST['company'] ?? '');

    // Валидация
    if (empty($formData['full_name'])) {
        $errors[] = 'ФИО обязательно для заполнения';
    }

    if (empty($formData['phone'])) {
        $errors[] = 'Телефон обязателен для заполнения';
    } elseif (!validatePhone($formData['phone'])) {
        $errors[] = 'Неверный формат телефона';
    }

    if (!empty($formData['email']) && !validateEmail($formData['email'])) {
        $errors[] = 'Неверный формат email';
    }

    // Если нет ошибок, обновляем данные
    if (empty($errors)) {
        $query = "UPDATE clients SET full_name = ?, phone = ?, email = ?, company = ? WHERE id = ?";
        $email = !empty($formData['email']) ? $formData['email'] : null;
        $company = !empty($formData['company']) ? $formData['company'] : null;

        $stmt = executeQuery($query, [
            $formData['full_name'],
            $formData['phone'],
            $email,
            $company,
            $clientId
        ], 'ssssi');

        if ($stmt) {
            header('Location: ' . getUrl('modules/clients/index.php?success=updated');
            exit();
        } else {
            $errors[] = 'Ошибка при обновлении данных';
        }
    }
}
?>

<?php require_once '../../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="top-header">
        <div class="header-title">
            <h1><i class="fas fa-user-edit"></i> Редактировать клиента</h1>
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
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-edit"></i> Редактирование данных клиента
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
                            <label for="full_name" class="form-label">
                                <i class="fas fa-user"></i> ФИО <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                   value="<?php echo htmlspecialchars($formData['full_name']); ?>"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone"></i> Телефон <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                   value="<?php echo htmlspecialchars($formData['phone']); ?>"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="company" class="form-label">
                                <i class="fas fa-building"></i> Компания
                            </label>
                            <input type="text" class="form-control" id="company" name="company"
                                   value="<?php echo htmlspecialchars($formData['company'] ?? ''); ?>">
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Сохранить изменения
                        </button>
                        <a href="<?php echo getUrl('modules/clients/index.php'); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
