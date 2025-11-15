<?php  
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prompt'])) {


    $file_path = __DIR__ . '/data.txt';

    $data = file_exists($file_path) ? file_get_contents($file_path) : '';

    $prompt = <<<EOT
You are an assistant that must answer questions using *only* the information provided below. Do not use any outside knowledge and assumptions, but make the information as clear and concise as possible for readers. You can also shorten your responses for better readability.
if the information is insufficient to answer the question, respond with exactly: "No Data specified about the topic."

--- BEGIN DATA ---
$data
--- END DATA ---

Question: {$_POST['prompt']}
Answer:
EOT;

$payload = json_encode([
    'model' => 'qwen3:0.6b',
    'prompt' => $prompt,
    'stream' => false

]);

$url = "http://127.0.0.1:11434/api/generate";

$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => [
            'Content-Type: application/json'
        ],
        'content' => $payload,
        'ignore_errors' => true
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

$data = json_decode($response, true);
$reply = $data['response'] ?? $data['output'] ?? '(no reply)';

echo htmlspecialchars($reply);
exit;

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Integration Test</title>
</head>
<body>
    <h3>Chat Application</h3>

    <div id ="chat"></div>

    <input id="input" type="text" placeholder="type your text here">
    <button onclick="send_to_chat()">send</button>

    <script> 
        function send_to_chat() {
            const chat = document.getElementById('chat');
            const input = document.getElementById('input');
            const text = input.value;
            if (!text) return;

            chat.innerHTML += "<p><b>You:</b> " + text + "</p>";
            input.value = '';
            
            const reply_id = "reply_" + Date.now();
            chat.innerHTML += "<p id='" + reply_id + "'><b>ollama:</b> (thinking...)</p>";

            fetch("chat.php", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "prompt=" + encodeURIComponent(text)
            })
            .then(res => res.text())
            .then(reply => {
                document.getElementById(reply_id).innerHTML = "<b>ollama:</b> " + reply;
            })
            .catch(() => {
                document.getElementById(reply_id).innerHTML = "<b>ollama:</b> error sending request.";
            });
        }
    </script>
</body>


     