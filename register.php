<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = trim($_POST['firstname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Hash password securely
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $sql = "INSERT INTO users (firstname, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $firstname, $email, $hashedPassword);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['firstname'] = $firstname;
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
body: JSON.stringify({
  model: "gpt-4o-mini",
  input: [
    { role: "system", content: "Split this into course modules. Return ONLY valid JSON array of objects like {title, summary}." },
    { role: "user", content: rawContent }
  ],
  text: {
    format: {
      type: "json_schema",
      name: "Modules",
      schema: {
        type: "array",
        items: {
          type: "object",
          properties: {
            title: { type: "string" },
            summary: { type: "string" }
          },
          required: ["title", "summary"]
        }
      },
      strict: true
    }
  }
})

?>
