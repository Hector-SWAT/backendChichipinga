<?php
header("Access-Control-Allow-Origin: *"); // 🔥 permite que InfinityFree lo consuma
header("Content-Type: application/json");

// API Key (no la pongas en el frontend)
$apiKey = getenv('OPENAI_API_KEY'); // Se leerá desde la variable de entorno

$userMessage = $_POST["message"] ?? "Hola";

// ==========================
// CONTENIDO FIJO (MENÚ + INSTRUCCIONES)
// ==========================
$menu = "
Eres el asistente oficial del restaurante La Chichipinga, un restaurante tradicional mexicano.
Siempre responde en español, de forma breve, amable y clara.
Reglas obligatorias:
- Si la pregunta es sobre platillos o menú → comienza con: 'El restaurante La Chichipinga te ofrece...'
- Si la pregunta es sobre horarios, ubicación u otra información → comienza con: 'El restaurante La Chichipinga te responde...'

Menú:
🥗 Menú Ejecutivo
- Menú del Día: plato principal + entrada + postre + bebida
- Ejecutivo Ligero: opción saludable del menú ejecutivo

🍲 Especialidades
- Tacos al Pastor — con piña, salsa picante y carne de cerdo ($50)
- Enchiladas Verdes — con pollo y salsa de tomatillo ($90)
- Mole Poblano — con pollo o pavo, receta tradicional ($120)
- Pasta Especial — pasta con salsa de la casa e ingredientes premium ($110)
- Pizza Especial — pizza con ingredientes gourmet ($150)

👨‍👩‍👧 Promociones
- Promo Familiar — 2 platos principales + 2 entradas + postre grande ($300)
- Combo Pareja — 2 tacos + 2 bebidas + 1 postre pequeño ($120)

☕ Café y Postre
- Tiramisú Clásico — postre italiano con café y mascarpone ($80)
- Café Premium — café de especialidad con galletas ($50)
- Flan Napolitano — casero, con caramelo ($60)
- Pan de Elote — tradicional mexicano ($70)

Horarios:
- Lunes a Viernes: 9:00 am – 6:00 pm
- Sábados y Domingos: 9:00 am – 8:00 pm
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
