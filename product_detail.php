<?php
session_start();

// Подключение к базе данных
try {
    $conn = new PDO('pgsql:host=localhost;dbname=prokof', 'postgres', '1');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

// Получение информации о товаре
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = intval($_GET['id']);
} else {
    echo 'Invalid product ID';
    exit;
}

try {
    // Получение информации о товаре
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo 'Product not found';
        exit;
    }

    // Получение изображений товара
    $stmt = $conn->prepare("SELECT * FROM images WHERE product_id = :id");
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Получение комментариев товара с именем пользователя и иерархией
    $stmt = $conn->prepare("
        SELECT c.*, u.username, c.parent_id AS parent
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.product_id = :id
        ORDER BY c.created_at DESC
    ");
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Database query failed: ' . $e->getMessage();
    exit;
}

// Функция для рекурсивного отображения комментариев
function displayComments($comments, $product, $parent_id = null) {
    foreach ($comments as $comment) {
        if ($comment['parent'] == $parent_id) {
            echo '<div class="comment">';
            echo '<p><strong>' . htmlspecialchars($comment['username']) . '</strong> - ' . htmlspecialchars($comment['created_at']) . '</p>';
            echo '<p>' . htmlspecialchars($comment['content']) . '</p>';
            echo '<form action="add_comment.php" method="post" style="margin-top: 10px;">';
            $parentId = intval($comment['id']); // Приводим значение к целому числу
            echo '<input type="hidden" name="parent_id" value="' . htmlspecialchars($parentId) . '">';
            echo '<input type="hidden" name="product_id" value="' . htmlspecialchars($product['id']) . '">'; // Добавляем product_id
            echo '<textarea name="content" rows="2" cols="40" required placeholder="Reply to this comment"></textarea>';
            echo '<button type="submit">Reply</button>';
            echo '</form>';
            displayComments($comments, $product, $comment['id']);
            echo '</div>';
        }
    }
}

// Закрытие соединения с базой данных
$conn = null;
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
<div class="user-info">
    <?php if (isset($_SESSION['username'])): ?>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <p>Please <a href="login.php">login</a> to access the full functionality.</p>
    <?php endif; ?>
</div>
<div class="return-link">
    <a href="product_list.php">Return to Home</a>
</div>
<h1><?php echo htmlspecialchars($product['name']); ?></h1>
<div class="product-detail">
    <?php if (!empty($images[0]['path'])): ?>
        <img src="uploads/<?php echo htmlspecialchars($images[0]['path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
    <?php else: ?>
        <img src="uploads/default.jpg" alt="Default Image">
    <?php endif; ?>
    <p><?php echo htmlspecialchars($product['description']); ?></p>
    <h3>Gallery</h3>
    <div class="product-gallery">
        <?php foreach ($images as $image): ?>
            <img src="uploads/<?php echo htmlspecialchars($image['path']); ?>" alt="Gallery Image">
        <?php endforeach; ?>
    </div>
    <h3>Comments</h3>
    <div class="comments">
        <?php displayComments($comments, $product); ?>
    </div>
    <?php if (isset($_SESSION['user_id'])): ?>
        <h3>Leave a Comment</h3>
        <form action="add_comment.php" method="post">
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
            <textarea name="content" rows="4" cols="50" required></textarea>
            <button type="submit">Submit</button>
        </form>
    <?php else: ?>
        <p>Please <a href="login.php">login</a> to leave a comment.</p>
    <?php endif; ?>
</div>
</body>
</html>
