<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$module_id = (int)($_GET['module'] ?? 0);
if (!$module_id) exit("Invalid module.");

$stmt = $pdo->prepare("
  SELECT v.term, v.definition
  FROM module_vocab v
  JOIN modules m ON m.id = v.module_id
  JOIN courses c ON c.id = m.course_id
  WHERE v.module_id = ? AND c.user_id = ?
  ORDER BY v.id ASC
");
$stmt->execute([$module_id, $_SESSION['user_id']]);
$vocab = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><title>Vocabulary</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body{min-height:100vh;background:url('background.jpg') no-repeat center/cover;display:flex;flex-direction:column;}
    header{position:fixed;top:0;left:0;width:100%;padding:20px 100px;display:flex;justify-content:space-between;align-items:center;background:rgba(255,255,255,0.1);backdrop-filter:blur(15px);border-bottom:2px solid rgba(255,255,255,0.25);z-index:99;}
    .logo{font-size:2em;color:#fff;}
    .navigation a{margin-left:40px;color:#fff;text-decoration:none;font-weight:500;position:relative;}
    .navigation a::after{content:'';position:absolute;left:0;bottom:-6px;width:100%;height:3px;background:#fff;border-radius:5px;transform:scaleX(0);transform-origin:right;transition:.5s;}
    .navigation a:hover::after{transform-origin:left;transform:scaleX(1);}
    main{flex:1;padding:140px 100px 50px;}
    .module-wrapper{background:rgba(255,255,255,0.15);border:2px solid rgba(255,255,255,0.25);border-radius:20px;backdrop-filter:blur(20px);padding:2rem;color:#162938;}
    .module-wrapper h1{margin-bottom:1rem;}
    .vocab-list{list-style:none;}
    .vocab-list li{padding:.8rem;border-bottom:1px solid rgba(0,0,0,0.1);}
    .term{font-weight:600;color:#162938;}
    .definition{color:#333;margin-top:.3rem;}
  </style>
</head>
<body>
<header>
  <h2 class="logo">Logo</h2>
  <nav class="navigation">
    <a href="dashboard.php">Home</a>
    <a href="add-course.php">Add Course</a>
    <a href="#">Profile</a>
    <a href="#">Settings</a>
    <a href="logout.php" class="btnLogout" style="color:#fff;">Logout</a>
  </nav>
</header>
<main>
  <div class="module-wrapper">
    <h1>Vocabulary</h1>
    <?php if ($vocab): ?>
      <ul class="vocab-list">
        <?php foreach ($vocab as $v): ?>
          <li>
            <span class="term"><?= htmlspecialchars($v['term']) ?></span>
            <div class="definition"><?= htmlspecialchars($v['definition']) ?></div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p><em>No vocabulary items.</em></p>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
