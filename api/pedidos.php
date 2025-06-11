<?php
header('Content-Type: application/json');
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['cliente_id'])) {
        $stmt = $pdo->prepare('SELECT * FROM pedidos WHERE cliente_id=? ORDER BY data DESC');
        $stmt->execute([$_GET['cliente_id']]);
        echo json_encode($stmt->fetchAll());
    } else {
        $stmt = $pdo->query('SELECT * FROM pedidos ORDER BY data DESC');
        echo json_encode($stmt->fetchAll());
    }
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    // $data deve conter: cliente_id, forma_pagamento, itens [{produto_id, quantidade, valor_unitario}]
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO pedidos (cliente_id, forma_pagamento, valor_total) VALUES (?, ?, ?)");
        $ok = $stmt->execute([
            $data['cliente_id'],
            $data['forma_pagamento'],
            $data['valor_total']
        ]);
        $pedido_id = $pdo->lastInsertId();
        foreach ($data['itens'] as $item) {
            $stmt2 = $pdo->prepare("INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, valor_unitario) VALUES (?, ?, ?, ?)");
            $stmt2->execute([
                $pedido_id,
                $item['produto_id'],
                $item['quantidade'],
                $item['valor_unitario']
            ]);
            // Atualiza estoque
            $pdo->prepare("UPDATE produtos SET estoque = estoque - ? WHERE id=?")->execute([$item['quantidade'], $item['produto_id']]);
        }
        $pdo->commit();
        echo json_encode(['success' => true, 'pedido_id' => $pedido_id]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);

    $fields = [];
    $values = [];

    if (isset($data['status_pagamento'])) {
        $fields[] = "status_pagamento=?";
        $values[] = $data['status_pagamento'];
    }
    if (isset($data['forma_pagamento'])) {
        $fields[] = "forma_pagamento=?";
        $values[] = $data['forma_pagamento'];
    }
    if (isset($data['data_aprovacao'])) {
        $fields[] = "data_aprovacao=?";
        $values[] = $data['data_aprovacao'];
    }
    if (isset($data['data_envio'])) {
        $fields[] = "data_envio=?";
        $values[] = $data['data_envio'];
    }
    if (isset($data['data_entrega'])) {
        $fields[] = "data_entrega=?";
        $values[] = $data['data_entrega'];
    }
    if (isset($data['data_cancelado'])) {
        $fields[] = "data_cancelado=?";
        $values[] = $data['data_cancelado'];
    }
    $values[] = $data['id'];
    $sql = "UPDATE pedidos SET " . implode(',', $fields) . " WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute($values);
    echo json_encode(['success' => $ok]);
    exit;
}
?>