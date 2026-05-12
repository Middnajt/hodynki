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
$msg_type = ""; // Pro barevné odlišení chyb a úspěchů

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
        $msg_type = "error";
    }
}

// ================= REGISTER =================
if (isset($_POST['register'])) {
    $username = $_POST['reg_username'];
    $email    = $_POST['reg_email'];
    $phone    = $_POST['reg_phone'];
    $pass1    = $_POST['reg_password'];
    $pass2    = $_POST['reg_password2'];

    if ($pass1 !== $pass2) {
        $msg = "Hesla se neshodují";
        $msg_type = "error";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users_sys WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $msg = "Uživatel nebo email už existuje";
            $msg_type = "error";
        } else {
            $hash = password_hash($pass1, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users_sys (username, email, password, phone) VALUES (?, ?, ?, ?)");

            if ($stmt->execute([$username, $email, $hash, $phone])) {
                $msg = "Registrace úspěšná. Nyní se můžete přihlásit.";
                $msg_type = "success";
            } else {
                $msg = "Chyba při registraci";
                $msg_type = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>MIKE SOFT | Přihlášení</title>
  <link rel="stylesheet" href="style-login.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
      /* Rychlá oprava pro zobrazení zpráv */
      .msg { padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; }
      .error { background: #ffcfcf; color: #a30000; border: 1px solid #a30000; }
      .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
  </style>
</head>
<body>

<header class="navbar">
  <div class="top-bar">
    <div class="search">
      <input type="text" placeholder="Napište, co hledáte">
    </div>
    <div class="logo">
      <a href="index.php" style="text-decoration: none; color: black; font-weight: bold;">MIKE SOFT</a>
    </div>
    <div class="actions"></div>
  </div>

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

    <!-- Společné zobrazení zpráv -->
    <?php if ($msg): ?>
        <div class="msg <?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <h2>PŘIHLÁŠENÍ</h2>
    <form method="post" action="">
      <input type="text" name="login_username" placeholder="Uživatel" required>
      <input type="password" name="login_password" placeholder="Heslo" required>
      <button type="submit" name="login">Přihlásit</button>
    </form>

    <hr style="margin: 25px 0; border: 0; border-top: 1px solid #eee;">

    <h2>REGISTRACE</h2>
    <form method="post" action="" id="regForm">
      <input type="text" name="reg_username" placeholder="Uživatelské jméno" required>
      <input type="email" name="reg_email" placeholder="E-mail" required>
      <!-- Změna: pattern upraven, aby neblokoval odeslání s mezerami -->
      <input type="text" name="reg_phone" id="phone" placeholder="777 123 456" required maxlength="11">
      <input type="password" name="reg_password" placeholder="Heslo" required>
      <input type="password" name="reg_password2" placeholder="Znovu heslo" required>
      <button type="submit" name="register">Registrovat</button>
    </form>

  </div>
</div>

<script>
const phoneInput = document.getElementById('phone');
const regForm = document.getElementById('regForm');

// Dynamické formátování čísla
phoneInput.addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, ''); // Odstraní vše kromě čísel
    value = value.substring(0, 9); // Max 9 číslic

    if (value.length > 6) {
        value = value.replace(/(\d{3})(\d{3})(\d+)/, '$1 $2 $3');
    } else if (value.length > 3) {
        value = value.replace(/(\d{3})(\d+)/, '$1 $2');
    }
    e.target.value = value;
});

// Očištění mezer a přidání předvolby před odesláním
regForm.addEventListener('submit', function () {
    let rawValue = phoneInput.value.replace(/\s/g, ''); // Odstraní mezery
    if (rawValue.length === 9 && !rawValue.startsWith('+')) {
        phoneInput.value = '+420' + rawValue;
    }
});
</script>

</body>
</html>