<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$course_id = $_GET['id'] ?? null;
if (!$course_id) { die("Invalid course."); }

$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND user_id = ?");
$stmt->execute([$course_id, $_SESSION['user_id']]);
$course = $stmt->fetch();

if (!$course) { die("Course not found."); }

$stmt = $pdo->prepare("SELECT * FROM modules WHERE course_id = ?");
$stmt->execute([$course_id]);
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($course['title']) ?></title>
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
    <div class="course-header">
      <h1><?= htmlspecialchars($course['title']) ?></h1>
      <p class="info"><?= $course['duration'] ?> • <?= $course['complexity'] ?></p>
    </div>

    <div class="section">
      <h2>Uploaded Content</h2>
      <div><?= $course['content'] ?: "<em>No content uploaded yet.</em>" ?></div>
    </div>

    <div class="section">
      <h2>Modules</h2>
      <?php if ($modules): ?>
        <div class="modules-grid">
          <?php foreach ($modules as $i => $m): ?>
            <a href="module.php?course=<?= $course['id'] ?>&module=<?= $m['id'] ?>" class="module-card">
              <h3>Module <?= $i+1 ?></h3>
              <p><?= htmlspecialchars($m['title']) ?></p>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p><em>No modules generated yet.</em></p>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
