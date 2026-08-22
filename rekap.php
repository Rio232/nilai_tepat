<?php

session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit;
}

require_once "database.php";

// Ambil data nilai + siswa + mapel + semester
$query = mysqli_query($conn, "
    SELECT
        nilai.nis,
        siswa.nama_siswa,
        kelas.nama_kelas,
        mapel.nama_mapel,
        nilai.nilai_tugas,
        nilai.nilai_uts,
        nilai.nilai_uas,
        nilai.nilai_akhir,
        nilai.catatan_guru,
        akademik.tahun_ajaran,
        akademik.semester
    FROM nilai

    INNER JOIN siswa
        ON nilai.nis = siswa.nis

    INNER JOIN kelas
        ON siswa.id_kelas = kelas.id_kelas

    INNER JOIN mapel
        ON nilai.id_mapel = mapel.id_mapel

    INNER JOIN akademik
        ON nilai.id_semester = akademik.id_semester

    ORDER BY siswa.nama_siswa ASC
");

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Rekap Nilai - Nilai-Tepat</title>

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

    <a href="dashboard.php">
        Dashboard
    </a>

    <a href="penilaian.php">
        Penilaian
    </a>

    <a href="siswa.php">
        Data Siswa
    </a>

    <a href="rekap.php">
        Rekap Nilai
    </a>

    <a href="raport.php">
        Raport
    </a>

    <a href="logout.php">
        Logout
    </a>

</nav>


<main>

    <h2>Rekap Nilai</h2>

    <p>
        Rekapitulasi nilai siswa yang telah dimasukkan.
    </p>


    <table>

        <thead>

            <tr>

                <th>No</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Mata Pelajaran</th>
                <th>Tugas</th>
                <th>UTS</th>
                <th>UAS</th>
                <th>Nilai Akhir</th>
                <th>Catatan Guru</th>

            </tr>

        </thead>


        <tbody>

        <?php

        $no = 1;

        while ($data = mysqli_fetch_assoc($query)):

        ?>

            <tr>

                <td>
                    <?= $no++ ?>
                </td>

                <td>
                    <?= htmlspecialchars($data["nis"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($data["nama_siswa"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($data["nama_kelas"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($data["nama_mapel"]) ?>
                </td>

                <td>
                    <?= $data["nilai_tugas"] ?>
                </td>

                <td>
                    <?= $data["nilai_uts"] ?>
                </td>

                <td>
                    <?= $data["nilai_uas"] ?>
                </td>

                <td>
                    <strong>
                        <?= $data["nilai_akhir"] ?>
                    </strong>
                </td>

                <td>
                    <?= htmlspecialchars($data["catatan_guru"]) ?>
                </td>

            </tr>

        <?php endwhile; ?>

        </tbody>

    </table>

</main>

</body>

</html>