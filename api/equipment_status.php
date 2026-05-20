<?php
// api/equipment_status.php
// Devolve a disponibilidade de TODOS os equipamentos em JSON.

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

define('DB_PATH', __DIR__ . '/../database/forge_gym.db');

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

// --- Consulta: todos os equipamentos de uma vez ---
$stmt = $pdo->query('SELECT id, name, zone, total_units, in_use FROM equipment');
$rows = $stmt->fetchAll();

// --- Calcular disponibilidade para cada um ---
$result = [];
foreach ($rows as $eq) {
    $available  = $eq['total_units'] - $eq['in_use'];
    $percentage = $eq['total_units'] > 0
        ? round(($eq['in_use'] / $eq['total_units']) * 100)
        : 0;

    $result[] = [
        'id'          => (int) $eq['id'],
        'name'        => $eq['name'],
        'zone'        => $eq['zone'],
        'total_units' => (int) $eq['total_units'],
        'in_use'      => (int) $eq['in_use'],
        'available'   => (int) $available,
        'percentage'  => $percentage,
        'status'      => $percentage >= 80 ? 'busy' : ($percentage >= 50 ? 'moderate' : 'free'),
    ];
}

echo json_encode($result);