<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Подключение к базе данных
    try {
        $conn = new PDO('pgsql:host=dpg-ctimca0gph6c7389h7bg-a.frankfurt-postgres.render.com;dbname=prokof', 'postgre', 'DN7tYjKxaxvDFdlv4csVNnH84WCKbcu2');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND password = :password AND role = 'admin'");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['admin_id'] = $user['id'];
        header('Location: admin_panel.php');
        exit();
    } else {
        $error = "Неправильное имя пользователя или пароль";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<h1>Admin Login</h1>
<?php if (isset($error)): ?>
    <p style="color: red;"><?php echo $error; ?></p>
<?php endif; ?>
<form action="admin_login.php" method="post">
    <label for="username">Имя пользователя:</label>
    <input type="text" id="username" name="username" required>
    <label for="password">Пароль:</label>
    <input type="password" id="password" name="password" required>
    <button type="submit">Войти</button>
</form>
</body>
</html>
