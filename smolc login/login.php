<?php
session_start();

// DB připojení
$servername = "dbs.spskladno.cz";
$username   = "student17";
$password   = "spsnet";
$dbname     = "vyuka17";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Chyba připojení: " . $e->getMessage());
}

$msg = "";

// ================= LOGIN =================
if (isset($_POST['login'])) {
    $user = $_POST['login_username'];
    $pass = $_POST['login_password'];

    $stmt = $pdo->prepare("SELECT * FROM users_sys WHERE username = ?");
    $stmt->execute([$user]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($pass, $row['password'])) {
        $_SESSION['user'] = $row['username'];
        header("Location: index.php");
        exit;
    } else {
        $msg = "Špatné přihlašovací údaje";
    }
}

// ================= REGISTER =================
if (isset($_POST['register'])) {
    $username = $_POST['reg_username'];
    $email    = $_POST['reg_email'];
    $phone    = $_POST['reg_phone'];
    $pass1    = $_POST['reg_password'];
    $pass2    = $_POST['reg_password2'];

    // kontrola hesel
    if ($pass1 !== $pass2) {
        $msg1 = "Hesla se neshodují";
    } else {

        // kontrola existujícího user/emailu
        $stmt = $pdo->prepare("SELECT id FROM users_sys WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $msg1 = "Uživatel nebo email už existuje";
        } else {

            $hash = password_hash($pass1, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users_sys (username, email, password, phone)
                VALUES (?, ?, ?, ?)
            ");

            if ($stmt->execute([$username, $email, $hash, $phone])) {
                $msg1 = "Registrace úspěšná";
            } else {
                $msg1 = "Chyba při registraci";
            }
        }
    }
}
?>




<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>MIKE SOFT</title>
  <link rel="stylesheet" href="style-login.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<header class="navbar">

  <!-- Top bar -->
  <div class="top-bar">
    <div class="search">
      <input type="text" placeholder="Napište, co hledáte">
    </div>

    <div class="logo">
      <a href="index.php" style="text-decoration: none; color: black">MIKE SOFT</a>
    </div>

    <div class="actions">

    </div>
  </div>

  <!-- Bottom menu -->
  <nav class="menu">
    <a href="#">ROLEX</a>
    <a href="#">ROLEX MODELY</a>
    <a href="#">NAŠE NABÍDKA</a>
    <a href="#">VÝKUP HODINEK</a>
    <a href="#">FAQ</a>
    <a href="#">KONTAKTY</a>
    <a href="#">O NÁS</a>
    <a href="#">PŘÍSLUŠENSTVÍ</a>
  </nav>
</header>

<div class="login-wrapper">
  <div class="login-box">

    <h2>PŘIHLÁŠENÍ</h2>

    <?php if (!empty($msg)) echo "<p>$msg</p>"; ?>

    <form method="post">
      <input type="text" name="login_username" placeholder="Uživatel" required>
      <input type="password" name="login_password" placeholder="Heslo" required>
      <button type="submit" name="login">Přihlásit</button>
    </form>

    <hr style="margin: 25px 0;">

    <h2>REGISTRACE</h2>

    <?php if (!empty($msg1)) echo "<p>$msg1</p>"; ?>

    <form method="post">
      <input type="text" name="reg_username" placeholder="Uživatelské jméno" required>
      <input type="email" name="reg_email" placeholder="E-mail" required>
      <input type="text" name="reg_phone" id="phone" placeholder="777 123 456" required maxlength="11" pattern="\d{3}\s?\d{3}\s?\d{3}">
      <input type="password" name="reg_password" placeholder="Heslo" required>
      <input type="password" name="reg_password2" placeholder="Znovu heslo" required>

      <button type="submit" name="register">Registrovat</button>
    </form>

  </div>
</div>
<script>
const phoneInput = document.getElementById('phone');

// formátování při psaní
phoneInput.addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, ''); // jen čísla

    // max 9 číslic (CZ číslo)
    value = value.substring(0, 9);

    // formát: 777 123 456
    if (value.length > 6) {
        value = value.replace(/(\d{3})(\d{3})(\d+)/, '$1 $2 $3');
    } else if (value.length > 3) {
        value = value.replace(/(\d{3})(\d+)/, '$1 $2');
    }

    e.target.value = value;
});

// při odeslání přidáme +420
document.querySelector('form[name="register"], form').addEventListener('submit', function () {
    let value = phoneInput.value.replace(/\s/g, '');
    phoneInput.value = '+420' + value;
});
</script>

</body>
</html>