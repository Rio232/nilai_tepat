<?php
session_start();
require_once "database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];

    $query = "SELECT * FROM pengguna WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);

    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && $password === $user["password"]) {

        $_SESSION["id_user"] = $user["id_user"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["role"] = $user["role"];

        header("Location: dashboard.php");
        exit;

    } else {
        $error = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nilai-Tepat</title>
    <link rel="stylesheet" href="css.css">
    <!-- Font Inter dari Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="page-login">

    <div class="container">
        <!-- Panel Kiri (Biru) -->
        <div class="left-panel">
            <div class="left-content">
                <div class="badge">SPK</div>
                <h1>Sistem Penilaian<br>Kelas</h1>
                <p class="description">Platform manajemen penilaian siswa yang mudah digunakan oleh guru dan tenaga pengajar.</p>
                
                <ul class="features">
                    <li><span class="check-icon">&#10003;</span> Kelola nilai siswa dengan mudah</li>
                    <li><span class="check-icon">&#10003;</span> Rekap absensi & catatan guru</li>
                    <li><span class="check-icon">&#10003;</span> Cetak raport digital</li>
                </ul>
            </div>
        </div>

        <!-- Panel Kanan (Form Login) -->
        <div class="right-panel">
            <div class="login-wrapper">
                <h2>Selamat Datang</h2>
                <p class="subtitle">Masuk ke akun Anda untuk melanjutkan</p>

                <?php if ($error): ?>
                    <div class="error-msg"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" placeholder="Masukkan username" required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Masukkan password" required>
                    </div>

                    <button type="submit" class="btn-submit">Masuk</button>
                </form>

                <div class="footer-text">
                    &copy; 2024 Sistem Penilaian Kelas &middot; v2.1.0
                </div>
            </div>
            <div class="help-btn">?</div>
        </div>
    </div>

</body>
</html>