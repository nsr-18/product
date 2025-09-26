<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM courses WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
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
    <h1>Welcome back, <?= htmlspecialchars($_SESSION['firstname']) ?> 👋</h1>
    <div class="course-grid">
      <?php foreach ($courses as $c): ?>
        <a href="course-detail.php?id=<?= $c['id'] ?>" class="course-card">
          <h3><?= htmlspecialchars($c['title']) ?></h3>
          <p><?= $c['duration'] ?> • <?= $c['complexity'] ?></p>
        </a>
      <?php endforeach; ?>
      <a href="add-course.php" class="course-card">➕ Add New Course</a>
    </div>
  </main>
</body>
</html>
