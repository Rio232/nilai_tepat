<?php

session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit;
}

require_once "database.php";

$username = $_SESSION["username"];
$role = $_SESSION["role"];

// Mengambil jumlah siswa
$query_siswa = mysqli_query($conn, "SELECT COUNT(*) AS total FROM siswa");
$data_siswa = mysqli_fetch_assoc($query_siswa);
$total_siswa = $data_siswa["total"];

// Mengambil jumlah guru
$query_guru = mysqli_query($conn, "SELECT COUNT(*) AS total FROM guru");
$data_guru = mysqli_fetch_assoc($query_guru);
$total_guru = $data_guru["total"];

// Mengambil jumlah kelas
$query_kelas = mysqli_query($conn, "SELECT COUNT(*) AS total FROM kelas");
$data_kelas = mysqli_fetch_assoc($query_kelas);

// Mengambil jumlah mata pelajaran
$query_mapel = mysqli_query($conn, "SELECT COUNT(*) AS total FROM mapel");
$data_mapel = mysqli_fetch_assoc($query_mapel);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Nilai-Tepat</title>

    <link rel="stylesheet" href="css.css">
</head>

<body class="page-dashboard">

    <header>
        <h1>Nilai-Tepat</h1>

        <div>
            <strong><?= htmlspecialchars($username) ?></strong>
            <span>(<?= htmlspecialchars($role) ?>)</span>
        </div>
    </header>

    <nav div="card">
        <a href="dashboard.php">Dashboard</a>
        <a href="penilaian.php">Penilaian</a>
        <a href="siswa.php">Data Siswa</a>
        <a href="rekap.php">Rekap Nilai</a>
        <a href="raport.php">Raport</a>
        <a href="logout.php">Logout</a>
        <a href="siswa.php"> Raport Siswa<a>
    </nav>

    <main>

        <h2>Dashboard</h2>

        <p>
            Selamat datang, <?= htmlspecialchars($username) ?>.
        </p>

        <section>

            <div>
                <h3><?= $total_siswa ?></h3>
                <p>Total Siswa</p>
            </div>

            <div>
                <h3><?= $data_guru["total"] ?></h3>
                <p>Total Guru</p>
            </div>

            <div>
                <h3><?= $data_kelas["total"] ?></h3>
                <p>Total Kelas</p>
            </div>

            <div>
                <h3><?= $data_mapel["total"] ?></h3>
                <p>Mata Pelajaran</p>
            </div>

        </section>

    </main>

</body>

</html>