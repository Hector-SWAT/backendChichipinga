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
🍲 ESPECIALIDADES: 
- Tacos al Pastor ($50)
- Enchiladas Verdes ($90) 
- Mole Poblano ($120)
- Pasta Especial ($110)
- Pizza Especial ($150)

🥗 MENÚ EJECUTIVO:
- Menú del Día ($80)
- Ejecutivo Ligero ($70)

👨‍👩‍👧 PROMOCIONES:
- Promo Familiar ($300)
- Combo Pareja ($120)

☕ CAFÉ Y POSTRE:
- Tiramisú ($80)
- Café Premium ($50)
- Flan Napolitano ($60)
- Pan de Elote ($70)',
        
        'tacos' => 'El restaurante La Chichipinga te ofrece: Tacos al Pastor con piña, salsa picante y carne de cerdo por $50. ¡Nuestra especialidad de la casa!',
        
        'horario' => 'El restaurante La Chichipinga te responde: 
📅 Lunes a Viernes: 9:00 am – 6:00 pm
📅 Sábados y Domingos: 9:00 am – 8:00 pm',
        
        'ubicacion' => 'El restaurante La Chichipinga te responde: Estamos ubicados en José Dolores Pérez #3, andador de los Jilgueros, Zacatlán, Puebla. ¡Te esperamos! 🗺️',
        
        'precio' => 'El restaurante La Chichipinga te ofrece: Nuestros precios van desde $50 por los Tacos al Pastor hasta $150 por la Pizza Especial. Tenemos opciones para todos los presupuestos.',
        
        'promociones' => 'El restaurante La Chichipinga te ofrece: 
👨‍👩‍👧 PROMO FAMILIAR ($300) - 2 platos principales + 2 entradas + postre grande
💑 COMBO PAREJA ($120) - 2 tacos + 2 bebidas + 1 postre pequeño
🎉 MENÚ EJECUTIVO ($80) - Plato principal + entrada + postre + bebida',
        
        'recomendacion' => 'El restaurante La Chichipinga te ofrece: Te recomendamos nuestros famosos Tacos al Pastor si buscas algo tradicional, o el Mole Poblano si quieres probar un platillo emblemático de Puebla. ¡Ambos son excelentes!',
        
        'vegetariano' => 'El restaurante La Chichipinga te ofrece: Tenemos opciones vegetarianas como las Enchiladas Verdes (sin pollo), Pasta Especial y Pizza Especial. También ofrecemos el Menú Ejecutivo Ligero con opciones saludables.',
        
        'postres' => 'El restaurante La Chichipinga te ofrece: 
🍰 Tiramisú Clásico - $80
☕ Café Premium - $50
🍮 Flan Napolitano - $60
🌽 Pan de Elote - $70',
        
        'bebidas' => 'El restaurante La Chichipinga te ofrece: 
🥤 Refrescos ($25)
💧 Aguas frescas ($30)
☕ Café Premium ($50)
🍺 Cervezas ($40)
🍷 Vino de la casa ($60)',
        
        'reservacion' => 'El restaurante La Chichipinga te responde: Para reservaciones puedes llamarnos al 7971301139. Recomendamos reservar con anticipación los fines de semana.',
        
        'delivery' => 'El restaurante La Chichipinga te responde: Sí, hacemos delivery en Zacatlán y áreas cercanas. Llámanos al 123-456-7890 para realizar tu pedido.',
        
        'estacionamiento' => 'El restaurante La Chichipinga te responde: Contamos con estacionamiento gratuito para nuestros clientes.',
        
        'especial' => 'El restaurante La Chichipinga te ofrece: Nuestro platillo más especial es el Mole Poblano, una receta tradicional de Puebla con más de 20 ingredientes. ¡Una verdadera experiencia culinaria!',
        
        'popular' => 'El restaurante La Chichipinga te ofrece: Nuestros Tacos al Pastor son los más populares, seguidos del Mole Poblano y la Pizza Especial. ¡Todos son deliciosos!',
        
        'picante' => 'El restaurante La Chichipinga te ofrece: Si te gusta lo picante, te recomendamos los Tacos al Pastor con nuestra salsa picante especial o las Enchiladas Verdes. ¡Tenemos diferentes niveles de picor!',
        
        'familiar' => 'El restaurante La Chichipinga te responde: Somos un restaurante familiar con ambiente acogedor. Tenemos área para niños y la Promo Familiar perfecta para compartir.',
        
        'eventos' => 'El restaurante La Chichipinga te responde: Sí, organizamos eventos especiales. Contamos con espacio para celebraciones. Contáctanos para más información.',
        
        'pago' => 'El restaurante La Chichipinga te responde: Aceptamos efectivo, tarjetas de crédito/débito y transferencias bancarias.',
        
        'wifi' => 'El restaurante La Chichipinga te responde: Sí, ofrecemos WiFi gratuito a nuestros clientes.'
    ];
    
    // Buscar coincidencias en el mensaje
    foreach ($responses as $key => $response) {
        if (strpos($message, $key) !== false) {
            return $response;
        }
    }
    
    // Búsqueda por patrones mejorada
    if (strpos($message, 'menu') !== false || strpos($message, 'plato') !== false || strpos($message, 'comida') !== false || strpos($message, 'qué tienen') !== false || strpos($message, 'carta') !== false) {
        return $responses['menu'];
    }
    
    if (strpos($message, 'hora') !== false || strpos($message, 'cuándo') !== false || strpos($message, 'abren') !== false || strpos($message, 'cierran') !== false) {
        return $responses['horario'];
    }
    
    if (strpos($message, 'dónde') !== false || strpos($message, 'ubicacion') !== false || strpos($message, 'direccion') !== false || strpos($message, 'local') !== false) {
        return $responses['ubicacion'];
    }
    
    if (strpos($message, 'promo') !== false || strpos($message, 'ofert') !== false || strpos($message, 'combo') !== false || strpos($message, 'descuento') !== false) {
        return $responses['promociones'];
    }
    
    if (strpos($message, 'recomienda') !== false || strpos($message, 'recomendación') !== false || strpos($message, 'sugerencia') !== false) {
        return $responses['recomendacion'];
    }
    
    if (strpos($message, 'vegetariano') !== false || strpos($message, 'vegano') !== false || strpos($message, 'sin carne') !== false) {
        return $responses['vegetariano'];
    }
    
    if (strpos($message, 'postre') !== false || strpos($message, 'dulce') !== false || strpos($message, 'postres') !== false) {
        return $responses['postres'];
    }
    
    if (strpos($message, 'bebida') !== false || strpos($message, 'refresco') !== false || strpos($message, 'cerveza') !== false || strpos($message, 'vino') !== false) {
        return $responses['bebidas'];
    }
    
    if (strpos($message, 'reserva') !== false || strpos($message, 'reservar') !== false || strpos($message, 'mesa') !== false) {
        return $responses['reservacion'];
    }
    
    if (strpos($message, 'delivery') !== false || strpos($message, 'domicilio') !== false || strpos($message, 'a domicilio') !== false || strpos($message, 'entrega') !== false) {
        return $responses['delivery'];
    }
    
    if (strpos($message, 'estacionamiento') !== false || strpos($message, 'parqueo') !== false || strpos($message, 'aparcar') !== false) {
        return $responses['estacionamiento'];
    }
    
    if (strpos($message, 'especial') !== false || strpos($message, 'especialidad') !== false || strpos($message, 'famoso') !== false) {
        return $responses['especial'];
    }
    
    if (strpos($message, 'popular') !== false || strpos($message, 'más pedido') !== false || strpos($message, 'favorito') !== false) {
        return $responses['popular'];
    }
    
    if (strpos($message, 'picante') !== false || strpos($message, 'picoso') !== false) {
        return $responses['picante'];
    }
    
    if (strpos($message, 'familiar') !== false || strpos($message, 'niños') !== false || strpos($message, 'infantil') !== false) {
        return $responses['familiar'];
    }
    
    if (strpos($message, 'evento') !== false || strpos($message, 'fiesta') !== false || strpos($message, 'celebración') !== false) {
        return $responses['eventos'];
    }
    
    if (strpos($message, 'pago') !== false || strpos($message, 'tarjeta') !== false || strpos($message, 'efectivo') !== false || strpos($message, 'pagar') !== false) {
        return $responses['pago'];
    }
    
    if (strpos($message, 'wifi') !== false || strpos($message, 'internet') !== false) {
        return $responses['wifi'];
    }
    
    // Respuesta por defecto mejorada
    return 'El restaurante La Chichipinga te responde: ¡Hola! Somos un restaurante mexicano tradicional en Zacatlán, Puebla. ¿Te interesa conocer nuestro menú, horarios, promociones, hacer una reservación o tienes alguna pregunta específica?';
}

// Función para detectar mensajes de despedida
function isFarewellMessage($message) {
    $farewellKeywords = [
        'adios', 'adiós', 'chao', 'bye', 'hasta luego', 'hasta pronto', 
        'nos vemos', 'gracias', 'finalizar', 'terminar', 'salir',
        'fue todo', 'eso es todo', 'nada más', 'me voy'
    ];
    
    $message = strtolower(trim($message));
    
    foreach ($farewellKeywords as $keyword) {
        if (strpos($message, $keyword) !== false) {
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
        
        "¡Gracias por tu preferencia! ❤️ Recuerda que puedes llamarnos al 123-456-7890 para reservaciones o pedidos a domicilio. ¡Buen día! ☀️"
    ];
    
    return $farewells[array_rand($farewells)];
}

// Función para generar mensaje de valoración
function getRatingMessage() {
    return "🌟 **Valoración de la conversación**\n\n¿Cómo calificarías tu experiencia con nuestro asistente virtual?\n\n⭐ ⭐ ⭐ ⭐ ⭐\n\n*Tu feedback nos ayuda a mejorar nuestro servicio. ¡Gracias!*";
}

// Usar Google Gemini API
$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $apiKey);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

// Prompt mejorado para Gemini
$systemPrompt = "Eres el asistente oficial del restaurante La Chichipinga, un restaurante tradicional mexicano ubicado en Zacatlán, Puebla. Siempre responde en español, de forma breve, amable y clara.

Reglas obligatorias:
- Si la pregunta es sobre platillos, menú, recomendaciones o comida → comienza con: 'El restaurante La Chichipinga te ofrece...'
- Si la pregunta es sobre horarios, ubicación, reservaciones, delivery u otra información del restaurante → comienza con: 'El restaurante La Chichipinga te responde...'

INFORMACIÓN DEL RESTAURANTE:
📍 UBICACIÓN: José Dolores Pérez #3, andador de los Jilgueros, Zacatlán, Puebla
📞 TELÉFONO: 123-456-7890

🍲 ESPECIALIDADES:
- Tacos al Pastor — $50 (nuestra especialidad)
- Enchiladas Verdes — $90  
- Mole Poblano — $120 (platillo emblemático)
- Pasta Especial — $110
- Pizza Especial — $150

🥗 MENÚ EJECUTIVO:
- Menú del Día: $80 (plato principal + entrada + postre + bebida)
- Ejecutivo Ligero: $70 (opción saludable)

👨‍👩‍👧 PROMOCIONES:
- Promo Familiar — $300 (2 platos principales + 2 entradas + postre grande)
- Combo Pareja — $120 (2 tacos + 2 bebidas + 1 postre pequeño)

☕ CAFÉ Y POSTRE:
- Tiramisú Clásico — $80
- Café Premium — $50
- Flan Napolitano — $60
- Pan de Elote — $70

🥤 BEBIDAS:
- Refrescos — $25
- Aguas frescas — $30
- Cervezas — $40
- Vino de la casa — $60

📅 HORARIOS:
- Lunes a Viernes: 9:00 am – 6:00 pm
- Sábados y Domingos: 9:00 am – 8:00 pm

SERVICIOS:
✅ Delivery en Zacatlán
✅ Reservaciones
✅ Estacionamiento gratuito
✅ WiFi gratuito
✅ Eventos y celebraciones
✅ Opciones vegetarianas

Responde de forma muy breve y directa (máximo 2-3 líneas), siendo amable y servicial.";

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

// Verificar si es un mensaje de despedida
$isFarewell = isFarewellMessage($userMessage);
$ratingMessage = "";

if ($isFarewell) {
    $farewellMessage = getFarewellMessage();
    $ratingMessage = getRatingMessage();
    
    // Combinar el reply normal con el mensaje de despedida y valoración
    $reply = $reply . "\n\n" . $farewellMessage . "\n\n" . $ratingMessage;
}

// Devolver JSON
echo json_encode([
    "reply" => $reply,
    "isFarewell" => $isFarewell,
    "ratingMessage" => $isFarewell ? $ratingMessage : ""
]);
?>
