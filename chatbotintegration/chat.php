<?php  
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prompt'])) {


    $file_path = __DIR__ . '/data.txt';

    $data = file_exists($file_path) ? file_get_contents($file_path) : '';

    $prompt = <<<EOT
    You are an assistant that must answer questions using only the information provided below. 
    Do not use any outside knowledge or assumptions. Make the information as clear and concise as possible.
    Do not use any asterisks to emphasize or bolden the words for better readability. You also dont need to say "No data is missing" if the data is available in the provided information.
    Do not emphasize any dates with asterisks.
    If the information is insufficient to answer a question, respond with exactly: "No Data specified about the topic."
    If the user says "hi", "hello", or any greeting, respond with: "Hi! What can I do for you?"


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
    <title>AI Chat</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: white;
            overflow: hidden;
        }

        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }

        .chat-container {
            position: absolute;
            top: 0;
            left: 0;     
            width: 100%;
            height: 100%;
            background: white;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
        }

        .header {
            background: #51565bff;
            padding: 15px;
            text-align: center;
            color: white;
            font-size: 18px;
            font-weight: bold;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 10;
        }

        #chat {
            flex: 1;
            margin-top: 60px;        
            margin-bottom: 75px;     
            padding: 15px;
            overflow-y: auto;
        }

        .bubble {
            max-width: 80%;
            padding: 10px 15px;
            margin-bottom: 12px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.4;
        }

        .you {
            background: #d2b48c;
            align-self: flex-end;
        }

        .ai {
            background: #9a9a9aff;
            align-self: flex-start;
        }

        .input-area {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 10px;
            background: #f7f7f7;
            border-top: 1px solid #ddd;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 10;
        }

        #input {
            flex: 1;
            padding: 10px;
            border-radius: 20px;
            border: 1px solid #ccc;
            outline: none;
        }

        .send-btn {
            padding: 10px 15px;
            background: #d2b48c;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="chat-container">
        <div class="header">AI Assistant</div>

        <div id="chat"></div>

        <div class="input-area">
            <input id="input" type="text" placeholder="Type your message...">
            <button class="send-btn" onclick="send_to_chat()">Send</button>
        </div>
    </div>

    <script>
    function send_to_chat() {
        const chat = document.getElementById('chat');
        const input = document.getElementById('input');
        const sendBtn = document.querySelector('.send-btn');
        let text = input.value.trim();

        if (!text) return;

        // User bubble
        const userBubble = document.createElement("div");
        userBubble.className = "bubble you";
        userBubble.innerHTML = text;
        chat.appendChild(userBubble);

        input.value = '';
        chat.scrollTop = chat.scrollHeight;

        input.disabled = true;
        sendBtn.disabled = true;

        // AI bubble placeholder
        const aiBubble = document.createElement("div");
        aiBubble.className = "bubble ai";
        aiBubble.innerHTML = "(thinking...)";
        chat.appendChild(aiBubble);
        chat.scrollTop = chat.scrollHeight;

        fetch("chat.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "prompt=" + encodeURIComponent(text)
        })
        .then(res => res.text())
        .then(reply => {
            aiBubble.innerHTML = reply;
            chat.scrollTop = chat.scrollHeight;

            input.disabled = false;
            sendBtn.disabled = false;
            input.focus();
        })
        .catch(() => {
            aiBubble.innerHTML = "Error connecting to AI.";

            input.disabled = false;
            sendBtn.disabled = false;
            input.focus();
        });
    }
    </script>

    </body>
</html>


     