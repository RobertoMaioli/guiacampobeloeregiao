<?php
require_once __DIR__ . '/../core/UserAuth.php';
UserAuth::logout();
header('Location: /');
exit;