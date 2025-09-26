<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$course_id = $_GET['course'] ?? null;
$module_id = $_GET['module'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND user_id = ?");
$stmt->execute([$course_id, $_SESSION['user_id']]);
$course = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ? AND course_id = ?");
$stmt->execute([$module_id, $course_id]);
$module = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($module['title']) ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h2 class="logo">Logo</h2>
    <nav class="navigation">
      <a href="dashboard.php">Home</a>
      <a href="add-course.php">Add Course</a>
      <a href="#">Profile</a>
      <a href="#">Settings</a>
      <a href="logout.php" class="btnLogout">Logout</a>
    </nav>
  </header>

  <main>
    <div class="module-wrapper">
      <h1><?= htmlspecialchars($module['title']) ?></h1>
      <p><?= htmlspecialchars($module['description']) ?></p>
      <div class="actions">
        <a class="btn" href="module-text.php?module=<?= $module['id'] ?>">📖 Lesson Text</a>
        <a class="btn" href="module-vocab.php?module=<?= $module['id'] ?>">📚 Vocabulary</a>
        <a class="btn" href="module-quiz.php?module=<?= $module['id'] ?>">📝 Quiz</a>
      </div>
    </div>
  </main>
</body>
</html>
