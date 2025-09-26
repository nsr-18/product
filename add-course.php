<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add New Course</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body{min-height:100vh;background:url('background.jpg') no-repeat center/cover;display:flex;flex-direction:column;}
    header{position:fixed;top:0;left:0;width:100%;padding:20px 100px;display:flex;justify-content:space-between;align-items:center;background:rgba(255,255,255,0.1);backdrop-filter:blur(15px);border-bottom:2px solid rgba(255,255,255,0.25);z-index:99;}
    .logo{font-size:2em;color:#fff;}
    .navigation a{position:relative;font-size:1.1em;color:#fff;text-decoration:none;font-weight:500;margin-left:40px;}
    .navigation a::after{content:'';position:absolute;left:0;bottom:-6px;width:100%;height:3px;background:#fff;border-radius:5px;transform:scaleX(0);transform-origin:right;transition:.5s;}
    .navigation a:hover::after{transform-origin:left;transform:scaleX(1);}
    .btnLogout{width:120px;height:40px;background:transparent;border:2px solid #fff;color:#fff;border-radius:6px;cursor:pointer;font-weight:500;margin-left:40px;}
    .btnLogout:hover{background:#fff;color:#162938;}
    main{flex:1;padding:140px 20px 50px;display:flex;justify-content:center;align-items:flex-start;}
    .form-wrapper{width:100%;max-width:700px;background:rgba(255,255,255,0.15);border:2px solid rgba(255,255,255,0.25);border-radius:20px;backdrop-filter:blur(20px);box-shadow:0 0 25px rgba(0,0,0,0.3);padding:2.5rem;}
    .form-wrapper h1{font-size:2em;color:#162938;margin-bottom:.5rem;text-align:center;}
    .form-wrapper p{text-align:center;margin-bottom:1.5rem;color:#333;}
    label{display:block;margin-top:1.2rem;font-weight:600;color:#162938;}
    input,textarea,select,button{width:100%;margin-top:.4rem;padding:.8rem;border:2px solid rgba(22,41,56,0.3);border-radius:8px;font-size:1em;background:rgba(255,255,255,0.85);outline:none;}
    textarea{resize:vertical;}
    button{background:#162938;color:#fff;font-weight:600;margin-top:2rem;cursor:pointer;border:none;transition:.3s;display:inline-flex;align-items:center;justify-content:center;gap:.6rem;}
    button:hover{background:#fff;color:#162938;border:2px solid #162938;}
    .spinner{display:none;width:16px;height:16px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin 1s linear infinite;}
    @keyframes spin{to{transform:rotate(360deg)}}
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
    <a class="btnLogout" href="logout.php">Logout</a>
  </nav>
</header>

<main>
  <div class="form-wrapper">
    <h1>Create a New Course</h1>
    <p>Paste your notes, pick a pace & depth, and we’ll generate modules, lesson text, vocab, and a quiz.</p>

    <form id="newCourseForm" action="generate_course.php" method="POST">
      <label for="title">Course Title</label>
      <input type="text" id="title" name="title" required placeholder="e.g. The History of Quantum Mechanics">

      <label for="content">Course Content</label>
      <textarea id="content" name="content" rows="10" placeholder="Paste your notes, transcripts, or outlines here..."></textarea>

      <label for="duration">How long do you want the course to be?</label>
      <select id="duration" name="duration">
        <option value="Short">Short</option>
        <option value="Medium" selected>Medium</option>
        <option value="Long">Long</option>
      </select>

      <label for="complexity">How deep should we go?</label>
      <select id="complexity" name="complexity">
        <option value="Overview">Overview</option>
        <option value="Competent">Competent</option>
        <option value="Mastery">Mastery</option>
      </select>

      <button type="submit" id="submitBtn">
        <span class="spinner" id="spin"></span>
        <span id="btnText">Generate Modules and Save</span>
      </button>
    </form>
  </div>
</main>

<script>
  const form = document.getElementById('newCourseForm');
  const btn  = document.getElementById('submitBtn');
  const spin = document.getElementById('spin');
  const txt  = document.getElementById('btnText');

  form.addEventListener('submit', ()=>{
    btn.disabled = true;
    spin.style.display = 'inline-block';
    txt.textContent = 'Generating...';
  });
</script>
</body>
</html>
