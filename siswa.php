<?php

session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit;
}

require_once "database.php";

$query = mysqli_query($conn, "
    SELECT
        siswa.nis,
        siswa.nama_siswa,
        siswa.jenis_kelamin,
        kelas.nama_kelas
    FROM siswa
    INNER JOIN kelas
        ON siswa.id_kelas = kelas.id_kelas
    ORDER BY siswa.nama_siswa ASC
");

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Siswa - Nilai-Tepat</title>

    <link rel="stylesheet" href="css.css">

</head>

<body>

<header>

    <h1>Nilai-Tepat</h1>

    <div>
        <strong>
            <?= htmlspecialchars($_SESSION["username"]) ?>
        </strong>

        <span>
            (<?= htmlspecialchars($_SESSION["role"]) ?>)
        </span>
    </div>

</header>


<nav>

    <a href="dashboard.php">Dashboard</a>

    <a href="penilaian.php">Penilaian</a>

    <a href="siswa.php">Data Siswa</a>

    <a href="rekap.php">Rekap Nilai</a>

    <a href="raport.php">Raport</a>

    <a href="logout.php">Logout</a>

</nav>


<main>

    <h2>Data Siswa</h2>

    <p>
        Daftar siswa yang terdaftar dalam sistem.
    </p>


    <table>

        <thead>

            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Jenis Kelamin</th>
                <th>Aksi</th>
            </tr>

        </thead>


        <tbody>

        <?php

        $no = 1;

        while ($siswa = mysqli_fetch_assoc($query)):

        ?>

            <tr>

                <td>
                    <?= $no++ ?>
                </td>

                <td>
                    <?= htmlspecialchars($siswa["nis"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($siswa["nama_siswa"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($siswa["nama_kelas"]) ?>
                </td>

                <td>
                    <?= $siswa["jenis_kelamin"] === "L"
                        ? "Laki-laki"
                        : "Perempuan"
                    ?>
                </td>

                <td>

                    <a
                        href="raport.php?nis=<?= urlencode($siswa["nis"]) ?>"
                        class="action-button"
                    >
                        Lihat Raport
                    </a>

                </td>

            </tr>

        <?php endwhile; ?>

        </tbody>

    </table>

</main>

</body>

</html>