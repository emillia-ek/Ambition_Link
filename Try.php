<?php
session_start();

// Ustawienia bazy danych
$host = 'localhost';
$dbname = 'nazwa_bazy';
$username = 'nazwa_uzytkownika';
$password = 'haslo';

try {
    // Połączenie z bazą danych
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Nie można połączyć z bazą danych: " . $e->getMessage());
}

// Generowanie tokena CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Obsługa rejestracji
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // Weryfikacja tokena CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }

    // Sanitizacja danych wejściowych
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Szyfrowanie hasła

    // Wstawienie użytkownika do bazy danych
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    try {
        $stmt->execute([$username, $password]);
        echo "Rejestracja zakończona sukcesem!";
    } catch (PDOException $e) {
        echo "Wystąpił błąd podczas rejestracji: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja</title>
</head>
<body>

<h2>Rejestracja</h2>
<form method="post">
    <label>
        Nazwa użytkownika: <input type="text" name="username" required>
    </label><br>
    <label>
        Hasło: <input type="password" name="password" required>
    </label><br>
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <button type="submit" name="register">Zarejestruj</button>
</form>

</body>
</html>