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
    echo json_encode([
        "reply" => "El restaurante La Chichipinga te responde: Error de configuración del servicio.",
        "triggerRating" => false
    ]);
    exit;
}

// Leer el mensaje del usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = file_get_contents('php://input');
    
    if (strpos($input, 'message=') !== false) {
        parse_str($input, $postData);
        $userMessage = $postData['message'] ?? "Hola";
        $sessionId = $postData['session_id'] ?? null;
    } else {
        $jsonData = json_decode($input, true);
        $userMessage = $jsonData['message'] ?? "Hola";
        $sessionId = $jsonData['session_id'] ?? null;
    }
} else {
    $userMessage = $_GET['message'] ?? "Hola";
    $sessionId = $_GET['session_id'] ?? null;
}

// Función mejorada para detectar mensajes de despedida
function isFarewellMessage($message) {
    $message = strtolower(trim($message));
    
    $farewellKeywords = [
        'adios', 'adiós', 'chao', 'bye', 'goodbye', 'bye bye',
        'hasta luego', 'hasta pronto', 'hasta la vista', 'hasta mañana',
        'nos vemos', 'nos vemos luego', 'nos vemos pronto',
        'gracias', 'muchas gracias', 'thank you', 'thanks',
        'finalizar', 'terminar', 'acabar', 'concluir',
        'salir', 'me voy', 'me retiro', 'me despido',
        'fue todo', 'eso es todo', 'nada más', 'eso sería todo',
        'ya está', 'listo', 'listo gracias', 'está bien',
        'bueno ya', 'ok gracias', 'ok adios', 'vale gracias',
        'perfecto gracias', 'excelente gracias', 'genial gracias',
        'bien gracias', 'de acuerdo gracias'
    ];
    
    $farewellPatterns = [
        '/gracias.*(adios|adiós|chao|bye|hasta)/i',
        '/(adios|adiós|chao|bye).*gracias/i',
        '/^(gracias|thanks).*$/i',
        '/^(adios|adiós|chao|bye).*$/i',
        '/.*(me voy|me retiro|me despido).*$/i'
    ];
    
    foreach ($farewellKeywords as $keyword) {
        if (strpos($message, $keyword) !== false) {
            return true;
        }
    }
    
    foreach ($farewellPatterns as $pattern) {
        if (preg_match($pattern, $message)) {
            return true;
        }
    }
    
    return false;
}

// Función para generar mensaje de despedida
function getFarewellMessage() {
    $farewells = [
        "¡Ha sido un placer atenderte! 🎉 Esperamos verte pronto en La Chichipinga para que disfrutes de nuestros deliciosos platillos mexicanos. ¡Buen provecho! 🌮",
        "¡Gracias por contactarnos! 🤗 Te esperamos en José Dolores Pérez #3, Zacatlán, Puebla. ¡Ven a probar nuestros famosos Tacos al Pastor! 🌮",
        "¡Fue un gusto ayudarte! 😊 No olvides que tenemos promociones especiales todos los días. ¡Te esperamos en La Chichipinga! 🎊",
        "¡Hasta pronto! 👋 Esperamos que pronto nos visites para disfrutar de la auténtica comida mexicana en un ambiente familiar. ¡Te estamos esperando! 🏠",
        "¡Gracias por tu preferencia! ❤️ Recuerda que puedes llamarnos al 7971301139 para reservaciones o pedidos a domicilio. ¡Buen día! ☀️"
    ];
    
    return $farewells[array_rand($farewells)];
}

// Función para determinar si debe mostrar la valoración
function shouldTriggerRating($userMessage, $sessionId = null) {
    if (!isFarewellMessage($userMessage)) {
        return false;
    }
    return true;
}

// PROMPT DEL SISTEMA MEJORADO Y ESTRUCTURADO
$systemInstructions = "Eres el asistente virtual del restaurante La Chichipinga en Zacatlán, Puebla.

REGLAS DE FORMATO OBLIGATORIAS:
- Si hablas de platillos, menú o comida: inicia con 'El restaurante La Chichipinga te ofrece:'
- Si das información general (horarios, ubicación, servicios): inicia con 'El restaurante La Chichipinga te responde:'
- Responde SIEMPRE en español, de forma amable, breve y clara (máximo 3-4 líneas)
- Sé natural y conversacional

INFORMACIÓN DEL RESTAURANTE:

📍 UBICACIÓN: José Dolores Pérez #3, andador de los Jilgueros, Zacatlán, Puebla
📞 TELÉFONO: 7971301139

🍲 MENÚ Y PRECIOS:

Especialidades:
• Tacos al Pastor - \$50 (especialidad de la casa con piña, salsa picante y carne de cerdo)
• Enchiladas Verdes - \$90 (con queso y crema)
• Mole Poblano - \$120 (platillo emblemático con más de 20 ingredientes)
• Pasta Especial - \$110
• Pizza Especial - \$150

Menú Ejecutivo:
• Menú del Día - \$80 (plato principal + entrada + postre + bebida)
• Ejecutivo Ligero - \$70 (opción saludable)

Promociones:
• Promo Familiar - \$300 (2 platos principales + 2 entradas + postre grande)
• Combo Pareja - \$120 (2 tacos + 2 bebidas + 1 postre pequeño)

Postres:
• Tiramisú Clásico - \$80
• Café Premium - \$50
• Flan Napolitano - \$60
• Pan de Elote - \$70

Bebidas:
• Refrescos - \$25
• Aguas frescas - \$30
• Cervezas - \$40
• Vino de la casa - \$60

📅 HORARIOS:
• Lunes a Viernes: 9:00 am – 6:00 pm
• Sábados y Domingos: 9:00 am – 8:00 pm

✅ SERVICIOS:
• Delivery en Zacatlán y áreas cercanas
• Reservaciones (recomendadas para fin de semana)
• Estacionamiento gratuito
• WiFi gratuito
• Eventos y celebraciones
• Opciones vegetarianas disponibles
• Aceptamos efectivo, tarjetas y transferencias

EJEMPLOS DE RESPUESTAS:

Usuario: 'Hola'
Tú: 'El restaurante La Chichipinga te responde: ¡Hola! Bienvenido a nuestro restaurante mexicano. ¿Te gustaría conocer nuestro menú, hacer una reservación o tienes alguna pregunta?'

Usuario: '¿Qué me recomiendas?'
Tú: 'El restaurante La Chichipinga te ofrece: Te recomiendo nuestros famosos Tacos al Pastor (\$50) si buscas algo clásico, o el Mole Poblano (\$120) si quieres una experiencia única de Puebla. ¡Ambos son deliciosos!'

Usuario: '¿Cuál es tu horario?'
Tú: 'El restaurante La Chichipinga te responde: Estamos abiertos de lunes a viernes de 9:00 am a 6:00 pm, y sábados y domingos de 9:00 am a 8:00 pm. ¡Te esperamos!'";

// Usar Google Gemini API con el formato correcto
$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $apiKey);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

// ESTRUCTURA CORRECTA: System instructions + User message separados
$data = [
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                [
                    "text" => $userMessage
                ]
            ]
        ]
    ],
    "systemInstruction" => [
        "parts" => [
            [
                "text" => $systemInstructions
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.9,
        "maxOutputTokens" => 250,
        "topP" => 0.95,
        "topK" => 40
    ]
];

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Fallback response
$reply = "El restaurante La Chichipinga te responde: Disculpa, tengo problemas técnicos. Por favor llámanos al 7971301139 para atenderte mejor.";

if ($response !== false && $httpCode == 200) {
    $result = json_decode($response, true);
    
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $reply = trim($result['candidates'][0]['content']['parts'][0]['text']);
        
        // Validar que la respuesta siga el formato correcto
        if (!empty($reply) && 
            (strpos($reply, 'El restaurante La Chichipinga') !== false || 
             strpos($reply, 'La Chichipinga') !== false)) {
            // Respuesta válida
        } else {
            // Si no sigue el formato, forzar una respuesta genérica
            $reply = "El restaurante La Chichipinga te responde: " . $reply;
        }
    } else {
        error_log("Error en respuesta de Gemini: " . json_encode($result));
    }
} else {
    error_log("Error de conexión con Gemini. HTTP Code: $httpCode, Error: $curlError");
}

// Verificar si es un mensaje de despedida
$isFarewell = isFarewellMessage($userMessage);
$triggerRating = shouldTriggerRating($userMessage, $sessionId ?? null);

if ($isFarewell) {
    $farewellMessage = getFarewellMessage();
    $reply = $reply . "\n\n" . $farewellMessage;
}

// Devolver JSON con información para el frontend
echo json_encode([
    "reply" => $reply,
    "isFarewell" => $isFarewell,
    "triggerRating" => $triggerRating,
    "sessionId" => $sessionId ?? session_id()
], JSON_UNESCAPED_UNICODE);
?>
