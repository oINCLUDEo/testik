<?php
// Подключение к базе данных
try {
    $conn = new PDO('pgsql:host=localhost;dbname=prokof', 'postgres', '1');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

// Получение списка товаров с основным изображением
$stmt = $conn->prepare("
    SELECT p.*, i.path AS main_image
    FROM products p
    LEFT JOIN (
        SELECT product_id, path
        FROM images
        WHERE id IN (
            SELECT MIN(id)
            FROM images
            GROUP BY product_id
        )
    ) i ON p.id = i.product_id
");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<h1>Product List</h1>
<div class="product-list">
    <?php foreach ($products as $product): ?>
        <div class="product-item">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <img src="uploads/<?php echo htmlspecialchars($product['main_image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <p><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
            <a href="product_detail.php?id=<?php echo $product['id']; ?>">View Details</a>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
