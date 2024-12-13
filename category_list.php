<?php
// Подключение к базе данных
try {
    $conn = new PDO('pgsql:host=localhost;dbname=prokof', 'postgres', '1');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

// Получение списка категорий
$stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id IS NULL");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category List</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<h1>Category List</h1>
<div class="category-list">
    <?php foreach ($categories as $category): ?>
        <div class="category-item">
            <h2><?php echo htmlspecialchars($category['name']); ?></h2>
            <a href="product_list.php?category_id=<?php echo $category['id']; ?>">View Products</a>
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
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>

