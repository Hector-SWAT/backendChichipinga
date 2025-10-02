<?php
header("Access-Control-Allow-Origin: *"); // 🔥 permite que InfinityFree lo consuma
header("Content-Type: application/json");

// API Key (no la pongas en el frontend)
$apiKey = "TU_API_KEY_DE_OPENAI";

$userMessage = $_POST["message"] ?? "Hola";

// Menú y reglas
$menu = "
Eres el asistente oficial del restaurante La Chichipinga, un restaurante tradicional mexicano.
Siempre responde en español, breve y amable.
Reglas:
- Pregunta sobre platillos → comienza con: 'El restaurante La Chichipinga te ofrece...'
- Pregunta sobre horarios/servicio → comienza con: 'El restaurante La Chichipinga te responde...'

... (aquí todo tu menú)
";

$ch = curl_init("https://api.openai.com/v1/responses");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Content-Type: application/json",
  "Authorization: Bearer $apiKey"
]);

$data = [
  "model" => "gpt-5-nano",
  "input" => $menu . "\n\nEl cliente dijo: " . $userMessage
];

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$reply = $result["output"][0]["content"][0]["text"] 
  ?? "El restaurante La Chichipinga te responde: Lo siento, no entendí tu pedido.";

echo json_encode(["reply" => $reply]);
