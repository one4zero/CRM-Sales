<?php
$pageTitle = 'Личный кабинет';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$userId = getCurrentUserId();

// Получение данных пользователя
$user = fetchRow("SELECT * FROM users WHERE id = ?", [$userId], 'i');

$errors = [];
$success = [];
$formData = $user;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        // Обновление профиля
        $formData['full_name'] = sanitizeInput($_POST['full_name'] ?? '');
        $formData['email'] = sanitizeInput($_POST['email'] ?? '');

        // Валидация
        if (empty($formData['full_name'])) {
            $errors[] = 'ФИО обязательно для заполнения';
        }

        if (empty($formData['email'])) {
            $errors[] = 'Email обязателен для заполнения';
        } elseif (!validateEmail($formData['email'])) {
            $errors[] = 'Неверный формат email';
        }

        // Если нет ошибок, обновляем данные
        if (empty($errors)) {
            $query = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";
            $stmt = executeQuery($query, [
                $formData['full_name'],
                $formData['email'],
                $userId
            ], 'ssi');

            if ($stmt) {
                $_SESSION['user_name'] = $formData['full_name'];
                $_SESSION['user_email'] = $formData['email'];
                $success[] = 'Профиль успешно обновлен';
                $user = fetchRow("SELECT * FROM users WHERE id = ?", [$userId], 'i');
                $formData = $user;
            } else {
                $errors[] = 'Ошибка при обновлении профиля';
            }
        }
    } elseif ($action === 'change_password') {
        // Смена пароля
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Валидация
        if (empty($currentPassword)) {
            $errors[] = 'Введите текущий пароль';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $errors[] = 'Неверный текущий пароль';
        }

        if (empty($newPassword)) {
            $errors[] = 'Введите новый пароль';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'Пароль должен содержать минимум 6 символов';
        }

        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Пароли не совпадают';
        }

        // Если нет ошибок, обновляем пароль
        if (empty($errors)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = executeQuery($query, [$hashedPassword, $userId], 'si');

            if ($stmt) {
                $success[] = 'Пароль успешно изменен';
            } else {
                $errors[] = 'Ошибка при смене пароля';
            }
        }
    }
}
?>

<?php require_once '../../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="top-header">
        <div class="header-title">
            <h1><i class="fas fa-user-circle"></i> Личный кабинет</h1>
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
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                <?php echo implode('<br>', $success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Информация о пользователе -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user"></i> Информация о пользователе
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-circle" style="font-size: 5rem; color: var(--primary-color);"></i>
                        </div>
                        <h5><?php echo htmlspecialchars($user['full_name']); ?></h5>
                        <p class="text-muted">
                            <i class="fas fa-at"></i> <?php echo htmlspecialchars($user['username']); ?>
                        </p>
                        <span class="badge <?php echo $user['role'] == 'admin' ? 'badge-admin' : 'badge-manager'; ?>">
                            <?php echo $user['role'] == 'admin' ? 'Администратор' : 'Менеджер'; ?>
                        </span>
                        <hr>
                        <p class="text-muted mb-0">
                            <small>
                                <i class="fas fa-calendar"></i>
                                Зарегистрирован: <?php echo date('d.m.Y', strtotime($user['created_at'])); ?>
                            </small>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Редактирование профиля -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-edit"></i> Редактирование профиля
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_profile">

                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user"></i> Логин
                                </label>
                                <input type="text" class="form-control" id="username"
                                       value="<?php echo htmlspecialchars($user['username']); ?>"
                                       disabled>
                                <small class="text-muted">Логин нельзя изменить</small>
                            </div>

                            <div class="mb-3">
                                <label for="full_name" class="form-label">
                                    <i class="fas fa-id-card"></i> ФИО <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="full_name" name="full_name"
                                       value="<?php echo htmlspecialchars($formData['full_name']); ?>"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo htmlspecialchars($formData['email']); ?>"
                                       required>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Смена пароля -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-key"></i> Смена пароля
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="change_password">

                            <div class="mb-3">
                                <label for="current_password" class="form-label">
                                    <i class="fas fa-lock"></i> Текущий пароль <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">
                                    <i class="fas fa-lock"></i> Новый пароль <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control" id="new_password" name="new_password"
                                       minlength="6" required>
                                <small class="text-muted">Минимум 6 символов</small>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock"></i> Подтвердите пароль <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                       minlength="6" required>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i> Изменить пароль
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
