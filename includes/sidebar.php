<nav class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-chart-line"></i> CRM-система</h3>
        <small>Управление продажами</small>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo getUrl('dashboard.php'); ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Главная панель
            </a>
        </li>
        <li>
            <a href="<?php echo getUrl('modules/clients/index.php'); ?>" class="<?php echo strpos($_SERVER['PHP_SELF'], 'clients') !== false ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Клиенты
            </a>
        </li>
        <li>
            <a href="<?php echo getUrl('modules/deals/index.php'); ?>" class="<?php echo strpos($_SERVER['PHP_SELF'], 'deals') !== false ? 'active' : ''; ?>">
                <i class="fas fa-handshake"></i> Сделки
            </a>
        </li>
        <li>
            <a href="<?php echo getUrl('modules/tasks/index.php'); ?>" class="<?php echo strpos($_SERVER['PHP_SELF'], 'tasks') !== false ? 'active' : ''; ?>">
                <i class="fas fa-tasks"></i> Задачи
            </a>
        </li>
        <li>
            <a href="<?php echo getUrl('modules/reports/index.php'); ?>" class="<?php echo strpos($_SERVER['PHP_SELF'], 'reports') !== false ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> Отчеты
            </a>
        </li>
        <li>
            <a href="<?php echo getUrl('modules/profile/index.php'); ?>" class="<?php echo strpos($_SERVER['PHP_SELF'], 'profile') !== false ? 'active' : ''; ?>">
                <i class="fas fa-user-circle"></i> Личный кабинет
            </a>
        </li>
        <li>
            <a href="<?php echo getUrl('logout.php'); ?>">
                <i class="fas fa-sign-out-alt"></i> Выход
            </a>
        </li>
    </ul>
</nav>
