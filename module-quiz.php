<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$module_id = (int)($_GET['module'] ?? 0);
if (!$module_id) exit("Invalid module.");

$stmt = $pdo->prepare("
  SELECT q.id AS qid, q.question, a.id AS aid, a.answer_text, a.is_correct
  FROM module_quiz q
  JOIN modules m ON m.id = q.module_id
  JOIN courses c ON c.id = m.course_id
  JOIN module_quiz_answers a ON a.question_id = q.id
  WHERE q.module_id = ? AND c.user_id = ?
  ORDER BY q.id ASC, a.id ASC
");
$stmt->execute([$module_id, $_SESSION['user_id']]);
$rows = $stmt->fetchAll();

$questions = [];
foreach ($rows as $r) {
  $qid = $r['qid'];
  if (!isset($questions[$qid])) {
    $questions[$qid] = ["id"=>$qid, "question"=>$r['question'], "answers"=>[]];
  }
  $questions[$qid]["answers"][] = [
    "text" => $r['answer_text'],
    "correct" => (bool)$r['is_correct']
  ];
}
$questions = array_values($questions);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><title>Module Quiz</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body{min-height:100vh;background:url('background.jpg') no-repeat center/cover;display:flex;flex-direction:column;}
    header{position:fixed;top:0;left:0;width:100%;padding:20px 100px;display:flex;justify-content:space-between;align-items:center;background:rgba(255,255,255,0.1);backdrop-filter:blur(15px);border-bottom:2px solid rgba(255,255,255,0.25);z-index:99;}
    .logo{font-size:2em;color:#fff;}
    .navigation a{margin-left:40px;color:#fff;text-decoration:none;font-weight:500;position:relative;}
    .navigation a::after{content:'';position:absolute;left:0;bottom:-6px;width:100%;height:3px;background:#fff;border-radius:5px;transform:scaleX(0);transform-origin:right;transition:.5s;}
    .navigation a:hover::after{transform-origin:left;transform:scaleX(1);}
    main{flex:1;padding:140px 100px 50px;}
    .module-wrapper{background:rgba(255,255,255,0.15);border:2px solid rgba(255,255,255,0.25);border-radius:20px;backdrop-filter:blur(20px);padding:2rem;box-shadow:0 0 20px rgba(0,0,0,0.3);}
    .module-wrapper h1{margin-bottom:1rem;color:#162938;}
    .question{font-size:1.2em;margin-bottom:1rem;color:#162938;}
    .btn{display:block;width:100%;margin:.5rem 0;padding:.8rem;border-radius:6px;border:2px solid #162938;background:#fff;color:#162938;cursor:pointer;font-weight:600;transition:.3s;text-align:left;}
    .btn:hover{background:#162938;color:#fff;}
    .btn.correct{background:#9aeabc;border-color:#9aeabc;color:#000;}
    .btn.incorrect{background:#ff9393;border-color:#ff9393;color:#000;}
    .next-btn{margin-top:1rem;background:#162938;color:#fff;padding:.8rem 1.2rem;border:none;border-radius:6px;cursor:pointer;display:none;}
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
    <h1>Module Quiz</h1>
    <div class="question" id="question"></div>
    <div id="answers"></div>
    <button class="next-btn" id="nextBtn">Next</button>
  </div>
</main>
<script>
const questions = <?= json_encode($questions, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
let current = 0, score = 0;
const qEl = document.getElementById("question");
const ansEl = document.getElementById("answers");
const nextBtn = document.getElementById("nextBtn");

function showQuestion(){
  ansEl.innerHTML = "";
  nextBtn.style.display = "none";
  if(current < questions.length){
    const q = questions[current];
    qEl.textContent = (current+1)+". "+q.question;
    q.answers.forEach(ans=>{
      const btn = document.createElement("button");
      btn.textContent = ans.text;
      btn.className = "btn";
      btn.onclick = ()=>{
        if(ans.correct){ btn.classList.add("correct"); score++; }
        else btn.classList.add("incorrect");
        Array.from(ansEl.children).forEach(b=>b.disabled=true);
        nextBtn.style.display = "inline-block";
      };
      ansEl.appendChild(btn);
    });
  } else {
    qEl.textContent = `You scored ${score} / ${questions.length}`;
    const retry = document.createElement("button");
    retry.className = "next-btn";
    retry.textContent = "Retake Quiz";
    retry.onclick = ()=>{ current=0; score=0; showQuestion(); };
    ansEl.appendChild(retry);
  }
}
nextBtn.onclick = ()=>{ current++; showQuestion(); };
showQuestion();
</script>
</body>
</html>
