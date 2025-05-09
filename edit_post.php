<?php
require 'config.php';
session_start();

if (!isset($_GET['id'])) {
   header("Location: dashboard.php");
   exit();
}
//main
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND author_id = ?");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$post = $stmt->fetch();

if (!$post) {
   echo "Post not found";
   exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   $title = $_POST['title'];
   $content = $_POST['content'];

   $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
   $stmt->execute([$title, $content, $_GET['id']]);

   header("Location: dashboard.php");
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple CMS</title>
    <link rel="stylesheet" href="style.css">
</head>
<div class="container">
   <h1>Modificar POST</h1>
   <form method="POST">
      <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
      <textarea name="content" required><?= htmlspecialchars($post['content']) ?></textarea>
      <button type="submit">Update Post</button>
   </form>
</div>
