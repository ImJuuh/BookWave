<?php
require_once __DIR__ . '/../app/auth.php';
start_session();
session_destroy();
header('Location: /bookwave/public/');
exit;
