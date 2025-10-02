<?php
header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Leer API Key de Google Gemini desde variable de entorno
$apiKey = getenv('GEMINI_API_KEY');

if (!$apiKey) {
    echo json_encode(["reply" => "El restaurante La Chichipinga te responde: Error de configuración del servicio."]);
    exit;
}

// Leer el mensaje del usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userMessage = $input['message'] ?? "Hola";
} else {
    $userMessage = $_GET['message'] ?? "Hola";
}

// Sistema de respuestas predefinidas como fallback
function getPredefinedResponse($userMessage) {
    $message = strtolower(trim($userMessage));
    
    // Respuestas predefinidas
    $responses = [
        'hola' => 'El restaurante La Chichipinga te responde: ¡Hola! Bienvenido a nuestro restaurante mexicano. ¿En qué puedo ayudarte hoy?',
        'menu' => 'El restaurante La Chichipinga te ofrece: 
🍲 Especialidades: 
- Tacos al Pastor ($50)
- Enchiladas Verdes ($90) 
- Mole Poblano ($120)
- Pasta Especial ($110)
- Pizza Especial ($150)

🥗 Menú Ejecutivo también disponible
☕ Café y Postres
👨‍👩‍👧 Promociones Familiares',
        
        'tacos' => 'El restaurante La Chichipinga te ofrece: Tacos al Pastor con piña, salsa picante y carne de cerdo por $50',
        
        'horario' => 'El restaurante La Chichipinga te responde: 
Lunes a Viernes: 9:00 am – 6:00 pm
Sábados y Domingos: 9:00 am – 8:00 pm',
        
        'ubicacion' => 'El restaurante La Chichipinga te responde: Estamos ubicados en Av. Principal 123, Ciudad México. ¡Te esperamos!',
        
        'precio' => 'El restaurante La Chichipinga te ofrece: Nuestros precios van desde $50 por los Tacos al Pastor hasta $150 por la Pizza Especial.',
        
        'promociones' => 'El restaurante La Chichipinga te ofrece: 
👨‍👩‍👧 Promo Familiar ($300) - 2 platos principales + 2 entradas + postre grande
💑 Combo Pareja ($120) - 2 tacos + 2 bebidas + 1 postre pequeño'
    ];
    
    // Buscar coincidencias en el mensaje
    foreach ($responses as $key => $response) {
        if (strpos($message, $key) !== false) {
            return $response;
        }
    }
    
    // Búsqueda por patrones
    if (strpos($message, 'menu') !== false || strpos($message, 'plato') !== false || strpos($message, 'comida') !== false || strpos($message, 'qué tienen') !== false) {
        return $responses['menu'];
    }
    
    if (strpos($message, 'hora') !== false || strpos($message, 'cuándo') !== false || strpos($message, 'abren') !== false) {
        return $responses['horario'];
    }
    
    if (strpos($message, 'dónde') !== false || strpos($message, 'ubicacion') !== false || strpos($message, 'direccion') !== false) {
        return $responses['ubicacion'];
    }
    
    if (strpos($message, 'promo') !== false || strpos($message, 'ofert') !== false || strpos($message, 'combo') !== false) {
        return $responses['promociones'];
    }
    
    // Respuesta por defecto
    return 'El restaurante La Chichipinga te responde: ¡Hola! Somos un restaurante mexicano tradicional. ¿Te interesa conocer nuestro menú, horarios o promociones?';
}

// Usar Google Gemini API
$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $apiKey);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

// Prompt para Gemini
$systemPrompt = "Eres el asistente oficial del restaurante La Chichipinga, un restaurante tradicional mexicano. Siempre responde en español, de forma breve, amable y clara.

Reglas obligatorias:
- Si la pregunta es sobre platillos o menú → comienza con: 'El restaurante La Chichipinga te ofrece...'
- Si la pregunta es sobre horarios, ubicación u otra información → comienza con: 'El restaurante La Chichipinga te responde...'

Información del restaurante:
🍲 ESPECIALIDADES:
- Tacos al Pastor — $50
- Enchiladas Verdes — $90  
- Mole Poblano — $120
- Pasta Especial — $110
- Pizza Especial — $150

🥗 MENÚ EJECUTIVO:
- Menú del Día: plato principal + entrada + postre + bebida
- Ejecutivo Ligero: opción saludable

👨‍👩‍👧 PROMOCIONES:
- Promo Familiar — $300
- Combo Pareja — $120

☕ CAFÉ Y POSTRE:
- Tiramisú Clásico — $80
- Café Premium — $50
- Flan Napolitano — $60
- Pan de Elote — $70

HORARIOS:
- Lunes a Viernes: 9:00 am – 6:00 pm
- Sábados y Domingos: 9:00 am – 8:00 pm

Responde de forma muy breve y directa (máximo 2-3 líneas).";

$data = [
    "contents" => [
        [
            "parts" => [
                [
                    "text" => $systemPrompt . "\n\nEl cliente dice: \"" . $userMessage . "\""
                ]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.7,
        "maxOutputTokens" => 300,
        "topP" => 0.8,
        "topK" => 40
    ]
];

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Procesar respuesta
$reply = getPredefinedResponse($userMessage); // Fallback por defecto

if ($response !== false) {
    $result = json_decode($response, true);
    
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $reply = $result['candidates'][0]['content']['parts'][0]['text'];
    } else {
        // Si hay error con Gemini, usar respuestas predefinidas
        error_log("Error Gemini: " . json_encode($result));
    }
}

// Limpiar respuesta y asegurar formato
$reply = trim($reply);
if (empty($reply)) {
    $reply = getPredefinedResponse($userMessage);
}

// Devolver JSON
echo json_encode(["reply" => $reply]);
?>