<?php
header('Content-Type: application/json');
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['pedido_id'])) {
        $stmt = $pdo->prepare(
            'SELECT itens_pedido.*, produtos.nome, produtos.imagem
            FROM itens_pedido
            JOIN produtos ON itens_pedido.produto_id = produtos.id
            WHERE pedido_id=?'
        );
        $stmt->execute([$_GET['pedido_id']]);
        echo json_encode($stmt->fetchAll());
    } else {
        echo json_encode([]);
    }
    exit;
}
?>