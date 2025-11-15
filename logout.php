<?php
/**
 * Выход из системы
 */
session_start();
require_once 'config/paths.php';
require_once 'includes/auth.php';

logout();
