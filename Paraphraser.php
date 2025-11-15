<?php 
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prompt'])) {

    // Removed the file_path and $data loading, as it's not needed for simple paraphrasing.

    // *** MODIFIED PROMPT FOR PARAPHRASING ***
    $prompt = <<<EOT
You are a concise paraphrasing bot. Your task is to take the user's text and provide a single, reworded version of the text. Do not add any new information or conversational phrases. Only output the paraphrased text.

Text to Paraphrase: {$_POST['prompt']}
Paraphrased Text:
EOT;

    $payload = json_encode([
        'model' => 'qwen3:0.6b', // Keep your model selection
        'prompt' => $prompt,
        'stream' => false
    ]);

    $url = "http://127.0.0.1:11434/api/generate";

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => [
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
    <title>Paraphraser Bot</title>
</head>
<body>
    <h3>AI Paraphraser Bot</h3>
    <p>Enter text below to receive a paraphrased version.</p>

    <div id ="chat"></div>

    <input id="input" type="text" placeholder="Enter text to paraphrase...">
    <button onclick="send_to_chat()">Paraphrase</button>

    <script> 
        function send_to_chat() {
            const chat = document.getElementById('chat');
            const input = document.getElementById('input');
            const text = input.value;
            if (!text) return;

            // Updated label for user input
            chat.innerHTML += "<p><b>Original Text:</b> " + text + "</p>";
            input.value = '';
            
            const reply_id = "reply_" + Date.now();
            // Updated label for AI response
            chat.innerHTML += "<p id='" + reply_id + "'><b>Paraphraser:</b> (thinking...)</p>";

            fetch("Paraphraser.php", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "prompt=" + encodeURIComponent(text)
            })
            .then(res => res.text())
            .then(reply => {
                document.getElementById(reply_id).innerHTML = "<b>Paraphraser:</b> " + reply;
            })
            .catch(() => {
                document.getElementById(reply_id).innerHTML = "<b>Paraphraser:</b> error sending request.";
            });
        }
    </script>
</body>
</html>


     