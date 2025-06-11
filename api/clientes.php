<?php
header('Content-Type: application/json');
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query('SELECT id, nome, email, cpf, endereco, cep, tipo FROM clientes');
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['login'])) {
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email=? AND senha=?");
        $stmt->execute([$data['email'], $data['senha']]);
        $user = $stmt->fetch();
        if ($user) {
            unset($user['senha']);
            echo json_encode($user); // Inclui 'tipo'
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Login inválido']);
        }
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, cpf, endereco, cep, senha, tipo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $ok = $stmt->execute([
        $data['nome'], $data['email'], $data['cpf'], $data['endereco'], $data['cep'], $data['senha'], $data['tipo'] ?? 'cliente'
    ]);
    echo json_encode(['success' => $ok]);
    exit;
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE clientes SET nome=?, endereco=?, cep=? WHERE id=?");
    $ok = $stmt->execute([
        $data['nome'], $data['endereco'], $data['cep'], $data['id']
    ]);
    echo json_encode(['success' => $ok]);
    exit;
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM clientes WHERE id=?");
        $ok = $stmt->execute([$id]);
        echo json_encode(['success' => $ok]);
    }
    exit;
}
?>