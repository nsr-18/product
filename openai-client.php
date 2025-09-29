<?php
function openai_generate(array $messages, array $textFormat = null, string $model = 'gpt-4o-mini'): string {
  $apiKey = getenv('OPENAI_API_KEY');
  if (!$apiKey) throw new RuntimeException("Missing OPENAI_API_KEY");

  // Messages: [['role'=>'system'|'user'|'assistant','content'=>'...'], ...]
  $payload = [
    "model" => $model,
    "input" => array_map(fn($m)=>["role"=>$m['role'], "content"=>$m['content']], $messages),
  ];

  if ($textFormat) {
    // e.g. ["format" => ["type"=>"json_schema","name"=>"CoursePackage","schema"=>[...],"strict"=>true]]
    $payload["text"] = $textFormat;
  }

  $ch = curl_init("https://api.openai.com/v1/responses");
  curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => [
      "Authorization: Bearer $apiKey",
      "Content-Type: application/json"
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 120
  ]);
  $raw = curl_exec($ch);
  if ($raw === false) throw new RuntimeException("OpenAI cURL error: " . curl_error($ch));
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($code < 200 || $code >= 300) {
    throw new RuntimeException("OpenAI HTTP $code: $raw");
  }

  $res = json_decode($raw, true);
  // Aggregate output_text parts from REST wire format
  $parts = [];
  foreach (($res['output'] ?? []) as $out) {
    foreach (($out['content'] ?? []) as $part) {
      if (($part['type'] ?? '') === 'output_text' && isset($part['text'])) {
        $parts[] = $part['text'];
      }
    }
  }
  return implode("\n", $parts);
}
