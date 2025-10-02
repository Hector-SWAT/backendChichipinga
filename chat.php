<?php
header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Leer API Key desde variable de entorno
$apiKey = getenv('OPENAI_API_KEY');

if (!$apiKey) {
    echo json_encode(["reply" => "API Key no configurada en el entorno."]);
    exit;
}

// Leer el mensaje del usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userMessage = $input['message'] ?? "Hola";
} else {
    $userMessage = $_GET['message'] ?? "Hola";
}

// Contenido fijo del menú
$systemPrompt = "
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

// Inicializar cURL
$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey"
]);

// Formato correcto para la API de OpenAI
$data = [
    "model" => "gpt-3.5-turbo", // o "gpt-4" si tienes acceso
    "messages" => [
        [
            "role" => "system",
            "content" => $systemPrompt
        ],
        [
            "role" => "user",
            "content" => $userMessage
        ]
    ],
    "temperature" => 0.7,
    "max_tokens" => 500
];

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Procesar respuesta
$reply = "El restaurante La Chichipinga te responde: Lo siento, no entendí tu pedido.";

if ($response === false) {
    $reply = "El restaurante La Chichipinga te responde: Error de conexión. " . $curlError;
} else {
    $result = json_decode($response, true);
    
    // Debug: para ver la respuesta completa de la API
    // file_put_contents('debug.log', print_r($result, true));
    
    if (isset($result['choices'][0]['message']['content'])) {
        $reply = $result['choices'][0]['message']['content'];
    } elseif (isset($result['error']['message'])) {
        $reply = "El restaurante La Chichipinga te responde: Error - " . $result['error']['message'];
    }
}

// Devolver JSON
echo json_encode(["reply" => $reply]);
?>