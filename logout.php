<?php
/**
 * Выход из системы
 */
session_start();
require_once 'includes/auth.php';

logout();
