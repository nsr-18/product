<?php
session_start();
require 'db.php';
require 'openai_client.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit("POST only"); }

$title      = trim($_POST['title'] ?? '');
$content    = trim($_POST['content'] ?? '');
$duration   = $_POST['duration'] ?? 'Medium';    
$complexity = $_POST['complexity'] ?? 'Overview'; 

if ($title === '' || $duration === '' || $complexity === '') {
  http_response_code(400);
  exit("Missing required fields: title/duration/complexity.");
}

try {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare("INSERT INTO courses (user_id, title, content, duration, complexity) VALUES (?,?,?,?,?)");
  $stmt->execute([$_SESSION['user_id'], $title, $content, $duration, $complexity]);
  $course_id = (int)$pdo->lastInsertId();

  $jsonSchema = [
    "type" => "object",
    "properties" => [
      "modules" => [
        "type" => "array",
        "items" => [
          "type" => "object",
          "properties" => [
            "title"       => ["type"=>"string"],
            "description" => ["type"=>"string"],
            "lesson_text" => ["type"=>"string"],
            "vocab"       => [
              "type"=>"array",
              "items"=>[
                "type"=>"object",
                "properties"=>[
                  "term"=>["type"=>"string"],
                  "definition"=>["type"=>"string"]
                ],
                "required"=>["term","definition"],
                "additionalProperties"=>false
              ]
            ],
            "quiz"        => [
              "type"=>"array",
              "items"=>[
                "type"=>"object",
                "properties"=>[
                  "question"=>["type"=>"string"],
                  "answers"=>[
                    "type"=>"array",
                    "items"=>[
                      "type"=>"object",
                      "properties"=>[
                        "text"=>["type"=>"string"],
                        "is_correct"=>["type"=>"boolean"]
                      ],
                      "required"=>["text","is_correct"],
                      "additionalProperties"=>false
                    ],
                    "minItems"=>2
                  ]
                ],
                "required"=>["question","answers"],
                "additionalProperties"=>false
              ]
            ]
          ],
          "required"=>["title","description","lesson_text","vocab","quiz"],
          "additionalProperties"=>false
        ]
      ]
    ],
    "required"=>["modules"],
    "additionalProperties"=>false
  ];

  $textFormat = [
    "format" => [
      "type" => "json_schema",
      "name" => "CoursePackage",
      "schema" => $jsonSchema,
      "strict" => true
    ]
  ];

  $messages = [
    [
      "role" => "system",
      "content" => "You are an educational content generator. When asked for JSON, output valid JSON only with no extra prose."
    ],
    [
      "role" => "user",
      "content" =>
        "Output JSON only.\n".
        "Source content:\n".$content."\n\n".
        "Target duration: {$duration}. Target depth: {$complexity}.\n".
        "Rules:\n".
        "- Short: 3-4 modules; Medium: 5-6; Long: 7-8.\n".
        "- Depth mapping: Overview (high-level), Competent (concise yet thorough), Mastery (technical depth).\n".
        "- For each module: include title, description (1-2 sentences), lesson_text (structured paragraphs), vocab (6-12 items), quiz (4-6 MCQs, exactly one correct per question)."
    ]
  ];

  $json = openai_generate($messages, $textFormat, "gpt-4o-mini");

  $data = json_decode($json, true);
  if (!$data) {
    $start = strpos($json, '{'); $end = strrpos($json, '}');
    if ($start !== false && $end !== false) {
      $data = json_decode(substr($json, $start, $end - $start + 1), true);
    }
  }
  if (!$data || !isset($data['modules']) || !is_array($data['modules'])) {
    throw new RuntimeException("Model did not return valid JSON.\nFirst 400 chars:\n" . substr($json, 0, 400));
  }

  $stmtMod = $pdo->prepare("INSERT INTO modules (course_id, title, description) VALUES (?,?,?)");
  $stmtTxt = $pdo->prepare("INSERT INTO module_texts (module_id, content) VALUES (?,?)");
  $stmtVoc = $pdo->prepare("INSERT INTO module_vocab (module_id, term, definition) VALUES (?,?,?)");
  $stmtQ   = $pdo->prepare("INSERT INTO module_quiz (module_id, question) VALUES (?,?)");
  $stmtA   = $pdo->prepare("INSERT INTO module_quiz_answers (question_id, answer_text, is_correct) VALUES (?,?,?)");

  foreach ($data['modules'] as $m) {
    $stmtMod->execute([$course_id, $m['title'], $m['description']]);
    $module_id = (int)$pdo->lastInsertId();

    $stmtTxt->execute([$module_id, $m['lesson_text']]);

    foreach ($m['vocab'] as $v) {
      $stmtVoc->execute([$module_id, $v['term'], $v['definition']]);
    }

    foreach ($m['quiz'] as $q) {
      $stmtQ->execute([$module_id, $q['question']]);
      $question_id = (int)$pdo->lastInsertId();
      foreach ($q['answers'] as $ans) {
        $stmtA->execute([$question_id, $ans['text'], (int)$ans['is_correct']]);
      }
    }
  }

  $pdo->commit();
  header("Location: course-detail.php?id=".$course_id);
  exit;

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  $msg = "Generation failed: ".$e->getMessage();


  $log = "/tmp/generate_course_".date('Ymd_His').".log";
  file_put_contents($log, $msg);

  http_response_code(500);
  echo "<pre style='white-space:pre-wrap;'>$msg\nLog: $log</pre>";
}
