<?php
header('Content-Type: application/json');
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query('SELECT * FROM produtos ORDER BY id DESC');
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO produtos (nome, descricao, valor, estoque, imagem) VALUES (?, ?, ?, ?, ?)");
    $ok = $stmt->execute([
        $data['nome'],
        $data['descricao'],
        $data['valor'],
        $data['estoque'],
        $data['imagem']
    ]);
    echo json_encode(['success' => $ok]);
    exit;
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE produtos SET nome=?, descricao=?, valor=?, estoque=?, imagem=? WHERE id=?");
    $ok = $stmt->execute([
        $data['nome'],
        $data['descricao'],
        $data['valor'],
        $data['estoque'],
        $data['imagem'],
        $data['id']
    ]);
    echo json_encode(['success' => $ok]);
    exit;
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE id=?");
        $ok = $stmt->execute([$id]);
        echo json_encode(['success' => $ok]);
    }
    exit;
}
?>