<?php
require 'config.php';
session_start();

if (isset($_GET['id'])) {
   $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND author_id = ?");
   $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
}

header("Location: dashboard.php");