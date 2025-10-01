

function getProgress() {
  return JSON.parse(localStorage.getItem("progress") || "{}");
}

function saveProgress(progress) {
  localStorage.setItem("progress", JSON.stringify(progress));
}

// Render modules on course-detail.html
function renderModules(courseId) {
  const courses = JSON.parse(localStorage.getItem("courses") || "[]");
  const course = courses[courseId];
  const list = document.getElementById("modulesBlock");
  list.innerHTML = "";

  if (!course || !course.modules) {
    list.innerHTML = "<p>No modules available.</p>";
    return;
  }

  course.modules.forEach((moduleTitle, i) => {
    const item = document.createElement("li");
    item.textContent = moduleTitle;
    item.addEventListener("click", () => {
      window.location.href = `module.html?course=${courseId}&module=${i}`;
    });
    list.appendChild(item);
  });
}

// Quiz handling
function runQuiz(questions, moduleId) {
  const container = document.getElementById("quizContainer");
  let score = 0;
  let index = 0;

  function renderQuestion() {
    if (index >= questions.length) {
      container.innerHTML = `<h2>Your Score: ${score}/${questions.length}</h2>`;
      if (score >= Math.ceil(questions.length * 0.7)) {
        container.innerHTML += `<p>🎉 You leveled up!</p>`;
        const progress = getProgress();
        progress[moduleId] = "completed";
        saveProgress(progress);
      }
      return;
    }

    const q = questions[index];
    container.innerHTML = `<div class="quiz-question"><h3>${q.question}</h3></div>`;
    q.answers.forEach(ans => {
      const btn = document.createElement("button");
      btn.className = "quiz-option";
      btn.textContent = ans.text;
      btn.onclick = () => {
        if (ans.correct) score++;
        index++;
        renderQuestion();
      };
      container.appendChild(btn);
    });
  }

  renderQuestion();
}
