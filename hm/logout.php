<?php
  require __DIR__ . '/includes/auth.php';
  auth_logout();
  header('Location: /hm/');
  exit;
