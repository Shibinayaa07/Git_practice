<?php
require_once __DIR__ . '/config.php';

// Destroy session and redirect to login
session_destroy();
header('Location: login.php');
exit;