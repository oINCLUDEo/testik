<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Подключение к базе данных
    try {
        $conn = new PDO('pgsql:host=localhost;dbname=prokof', 'postgres', '1');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
    }

    $name = $_POST['name'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];

    $stmt = $conn->prepare("INSERT INTO products (name, description, category_id) VALUES (:name, :description, :category_id)");
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->execute();

    $product_id = $conn->lastInsertId();

    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['images']['name'][$key];
            $file_size = $_FILES['images']['size'][$key];
            $file_tmp = $_FILES['images']['tmp_name'][$key];
            $file_type = $_FILES['images']['type'][$key];
            $file_ext = strtolower(end(explode('.', $_FILES['images']['name'][$key])));

            $extensions = array("jpeg", "jpg", "png");

            if (in_array($file_ext, $extensions) === false) {
                $errors[] = "Extension not allowed, please choose a JPEG or PNG file.";
            }

            if ($file_size > 2097152) {
                $errors[] = 'File size must be less than 2 MB';
            }

            if (empty($errors) == true) {
                move_uploaded_file($file_tmp, "uploads/" . $file_name);
                $stmt = $conn->prepare("INSERT INTO images (product_id, path) VALUES (:product_id, :path)");
                $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                $stmt->bindParam(':path', $file_name, PDO::PARAM_STR);
                $stmt->execute();
            } else {
                print_r($errors);
            }
        }
    }

    header('Location: admin_panel.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<h1>Add New Product</h1>
<form action="add_product.php" method="post" enctype="multipart/form-data">
    <label for="name">Product Name:</label>
    <input type="text" id="name" name="name" required>
    <label for="description">Description:</label>
    <textarea id="description" name="description" rows="4" cols="50" required></textarea>
    <label for="category_id">Category ID:</label>
    <input type="number" id="category_id" name="category_id" required>
    <label for="images">Images:</label>
    <input type="file" id="images" name="images[]" multiple required>
    <button type="submit">Add Product</button>
</form>
</body>
</html>
