<?php
// Подключение к базе данных
try {
    $conn = new PDO('pgsql:host=localhost;dbname=prokof', 'postgres', '1');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

// Получение списка категорий для навигации
$stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id IS NULL");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получение списка товаров с основным изображением
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : null;
$query = "
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
";
if ($category_id) {
    $query .= " WHERE p.category_id = :category_id OR p.category_id IN (SELECT id FROM categories WHERE parent_id = :category_id)";
}
$stmt = $conn->prepare($query);
if ($category_id) {
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
}
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
<div class="category-navigation">
    <h2>Categories</h2>
    <ul>
        <?php foreach ($categories as $category): ?>
            <li><a href="product_list.php?category_id=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a>
                <?php
                // Получение подкатегорий
                $stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id = :parent_id");
                $stmt->bindParam(':parent_id', $category['id'], PDO::PARAM_INT);
                $stmt->execute();
                $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <?php if (!empty($subcategories)): ?>
                    <ul>
                        <?php foreach ($subcategories as $subcategory): ?>
                            <li><a href="product_list.php?category_id=<?php echo $subcategory['id']; ?>"><?php echo htmlspecialchars($subcategory['name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<div class="product-list">
    <?php foreach ($products as $product): ?>
        <div class="product-item">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <?php if (!empty($product['main_image'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($product['main_image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <?php else: ?>
                <img src="uploads/default.jpg" alt="Default Image">
            <?php endif; ?>
            <p><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
            <a href="product_detail.php?id=<?php echo $product['id']; ?>">View Details</a>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>

