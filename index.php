<?php
// index.php para Render
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>API La Chichipinga</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .status { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>API Chatbot La Chichipinga</h1>
    <div class="status">
        Servicio funcionando correctamente
    </div>
    <p><strong>Endpoint:</strong> POST /chat.php</p>
    <p><strong>URL:</strong>https://backendchichipinga.onrender.com/chat.php</p>
    
    <h3>Probar API:</h3>
    <pre id="result"></pre>
    
    <script>
        // Probar automáticamente la API
        fetch('https://backendchichipinga.onrender.com/chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({message: 'hola'})
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('result').textContent = 
                'Respuesta: ' + JSON.stringify(data, null, 2);
        })
        .catch(error => {
            document.getElementById('result').textContent = 
                'Error: ' + error.message;
        });
    </script>
</body>
</html>