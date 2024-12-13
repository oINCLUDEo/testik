<?php
// Подключение к базе данных
try {
    $conn = new PDO('pgsql:host=localhost;dbname=prokof', 'postgres', '1');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

// Получение информации о товаре
$product_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
$stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Получение изображений товара
$stmt = $conn->prepare("SELECT * FROM images WHERE product_id = :id");
$stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
$stmt->execute();
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получение комментариев товара
$stmt = $conn->prepare("
    SELECT c.*, u.username
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.product_id = :id
    ORDER BY c.created_at DESC
");
$stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<h1><?php echo htmlspecialchars($product['name']); ?></h1>
<div class="product-detail">
    <img src="uploads/<?php echo htmlspecialchars($product['main_image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
    <p><?php echo htmlspecialchars($product['description']); ?></p>
    <h3>Gallery</h3>
    <div class="product-gallery">
        <?php foreach ($images as $image): ?>
            <img src="uploads/<?php echo htmlspecialchars($image['path']); ?>" alt="Gallery Image">
        <?php endforeach; ?>
    </div>
    <h3>Comments</h3>
    <div class="comments">
        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <p><strong><?php echo htmlspecialchars($comment['username']); ?></strong> - <?php echo htmlspecialchars($comment['created_at']); ?></p>
                <p><?php echo htmlspecialchars($comment['content']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <h3>Leave a Comment</h3>
    <form action="add_comment.php" method="post">
        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
        <textarea name="content" rows="4" cols="50"></textarea>
        <button type="submit">Submit</button>
    </form>
</div>
</body>
</html>
