<?php
// Подключение к базе данных
try {
    $conn = new PDO('pgsql:host=localhost;dbname=prokof', 'postgres', '1');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $content = $_POST['content'];
    $user_id = 1; // Пример пользователя, замените на реального пользователя

    $stmt = $conn->prepare("INSERT INTO comments (product_id, user_id, content) VALUES (:product_id, :user_id, :content)");
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':content', $content, PDO::PARAM_STR);
    $stmt->execute();

    header("Location: product_detail.php?id=$product_id");
    exit();
}
