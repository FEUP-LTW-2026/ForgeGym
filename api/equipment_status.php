<?php
// api/equipment_status.php
// Devolve a disponibilidade de um equipamento em JSON.

header('Content-Type: application/json; charset=utf-8');
// Permite chamadas do mesmo domínio (ajustar em produção)
header('Access-Control-Allow-Origin: *');

// --- Configuração ---
define('DB_PATH', __DIR__ . '/../database/forge_gym.db');

// --- Validação do input ---
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de equipamento inválido.']);
    exit;
}

// --- Ligação à base de dados ---
try {
    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro de ligação à base de dados.']);
    exit;
}

// --- Consulta ---
$stmt = $pdo->prepare(
    'SELECT id, name, zone, total_units, in_use FROM equipment WHERE id = :id'
);
$stmt->execute([':id' => $id]);
$equipment = $stmt->fetch();

if (!$equipment) {
    http_response_code(404);
    echo json_encode(['error' => 'Equipamento não encontrado.']);
    exit;
}

// --- Cálculo de disponibilidade ---
$available  = $equipment['total_units'] - $equipment['in_use'];
$percentage = $equipment['total_units'] > 0
    ? round(($equipment['in_use'] / $equipment['total_units']) * 100)
    : 0;

// --- Resposta ---
echo json_encode([
    'id'          => (int) $equipment['id'],
    'name'        => $equipment['name'],
    'zone'        => $equipment['zone'],
    'total_units' => (int) $equipment['total_units'],
    'in_use'      => (int) $equipment['in_use'],
    'available'   => (int) $available,
    'percentage'  => $percentage,
    'status'      => $percentage >= 80 ? 'busy' : ($percentage >= 50 ? 'moderate' : 'free'),
]);