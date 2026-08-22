<?php

session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit;
}

require_once "database.php";

$nis = $_GET["nis"] ?? "";

if ($nis === "") {
    die("NIS siswa tidak ditemukan.");
}

// =============================
// Ambil data siswa
// =============================

$query_siswa = mysqli_prepare(
    $conn,
    "SELECT
        siswa.nis,
        siswa.nama_siswa,
        siswa.jenis_kelamin,
        kelas.nama_kelas
     FROM siswa
     INNER JOIN kelas
        ON siswa.id_kelas = kelas.id_kelas
     WHERE siswa.nis = ?"
);

mysqli_stmt_bind_param($query_siswa, "s", $nis);
mysqli_stmt_execute($query_siswa);

$result_siswa = mysqli_stmt_get_result($query_siswa);
$siswa = mysqli_fetch_assoc($result_siswa);

if (!$siswa) {
    die("Data siswa tidak ditemukan.");
}

// =============================
// Ambil nilai siswa
// =============================

$query_nilai = mysqli_prepare(
    $conn,
    "SELECT
        mapel.nama_mapel,
        nilai.nilai_tugas,
        nilai.nilai_uts,
        nilai.nilai_uas,
        nilai.nilai_akhir,
        nilai.catatan_guru,
        akademik.tahun_ajaran,
        akademik.semester
     FROM nilai

     INNER JOIN mapel
        ON nilai.id_mapel = mapel.id_mapel

     INNER JOIN akademik
        ON nilai.id_semester = akademik.id_semester

     WHERE nilai.nis = ?

     ORDER BY mapel.nama_mapel ASC"
);

mysqli_stmt_bind_param($query_nilai, "s", $nis);
mysqli_stmt_execute($query_nilai);

$result_nilai = mysqli_stmt_get_result($query_nilai);

// =============================
// Ambil absensi
// =============================

$query_absensi = mysqli_prepare(
    $conn,
    "SELECT
        hadir,
        izin,
        sakit,
        alpa
     FROM absensi
     WHERE nis = ?
     ORDER BY id_semester DESC
     LIMIT 1"
);

mysqli_stmt_bind_param($query_absensi, "s", $nis);
mysqli_stmt_execute($query_absensi);

$result_absensi = mysqli_stmt_get_result($query_absensi);
$absensi = mysqli_fetch_assoc($result_absensi);

if (!$absensi) {
    $absensi = [
        "hadir" => 0,
        "izin" => 0,
        "sakit" => 0,
        "alpa" => 0
    ];
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Raport - Nilai-Tepat</title>

    <link
        rel="stylesheet"
        href="css.css"
    >

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

    <h2>Raport Siswa</h2>

    <p>
        Laporan hasil belajar siswa.
    </p>


    <!-- DATA SISWA -->

    <div class="raport-header">

        <div>
            <strong>Nama Siswa</strong>
            <p>
                <?= htmlspecialchars($siswa["nama_siswa"]) ?>
            </p>
        </div>

        <div>
            <strong>NIS</strong>
            <p>
                <?= htmlspecialchars($siswa["nis"]) ?>
            </p>
        </div>

        <div>
            <strong>Kelas</strong>
            <p>
                <?= htmlspecialchars($siswa["nama_kelas"]) ?>
            </p>
        </div>

        <div>
            <strong>Jenis Kelamin</strong>
            <p>
                <?= $siswa["jenis_kelamin"] === "L"
                    ? "Laki-laki"
                    : "Perempuan"
                ?>
            </p>
        </div>

    </div>


    <!-- NILAI -->

    <h3 class="section-title">
        Nilai Akademik
    </h3>


    <table>

        <thead>

            <tr>

                <th>No</th>
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

        while ($nilai = mysqli_fetch_assoc($result_nilai)):

        ?>

            <tr>

                <td>
                    <?= $no++ ?>
                </td>

                <td>
                    <?= htmlspecialchars($nilai["nama_mapel"]) ?>
                </td>

                <td>
                    <?= $nilai["nilai_tugas"] ?>
                </td>

                <td>
                    <?= $nilai["nilai_uts"] ?>
                </td>

                <td>
                    <?= $nilai["nilai_uas"] ?>
                </td>

                <td>

                    <strong>
                        <?= $nilai["nilai_akhir"] ?>
                    </strong>

                </td>

                <td>
                    <?= htmlspecialchars($nilai["catatan_guru"]) ?>
                </td>

            </tr>

        <?php endwhile; ?>

        </tbody>

    </table>


    <!-- ABSENSI -->

    <h3 class="section-title">
        Rekap Absensi
    </h3>


    <div class="attendance">

        <div>
            <strong>
                <?= $absensi["hadir"] ?>
            </strong>
            <span>Hadir</span>
        </div>

        <div>
            <strong>
                <?= $absensi["izin"] ?>
            </strong>
            <span>Izin</span>
        </div>

        <div>
            <strong>
                <?= $absensi["sakit"] ?>
            </strong>
            <span>Sakit</span>
        </div>

        <div>
            <strong>
                <?= $absensi["alpa"] ?>
            </strong>
            <span>Alpa</span>
        </div>

    </div>


    <br>


    <button
        onclick="window.print()"
        class="print-button"
    >
        Cetak Raport
    </button>

</main>

</body>

</html>