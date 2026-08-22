<?php

session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit;
}

require_once "database.php";

$kelas = mysqli_query(
    $conn,
    "SELECT * FROM kelas ORDER BY nama_kelas ASC"
);

$mapel = mysqli_query(
    $conn,
    "SELECT * FROM mapel ORDER BY nama_mapel ASC"
);

$semester = mysqli_query(
    $conn,
    "SELECT * FROM akademik ORDER BY id_semester DESC"
);

$id_kelas = $_GET["kelas"] ?? "";
$id_mapel = $_GET["mapel"] ?? "";
$id_semester = $_GET["semester"] ?? "";
$tanggal = $_GET["tanggal"] ?? date("Y-m-d");

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Penilaian - Nilai-Tepat</title>

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

    <h2>Penilaian Siswa</h2>

    <p>
        Pilih kelas, mata pelajaran, dan semester.
    </p>


    <!-- FILTER -->

    <form method="GET" class="filter-form">
        <div>

          <div>

    <label>Tanggal</label>

    <input
        type="date"
        name="tanggal"
        value="<?= htmlspecialchars($_GET["tanggal"] ?? date("Y-m-d")) ?>"
        required
    >

</div>

        <div>

            <label>Kelas</label>

            <select name="kelas" required>

                <option value="">
                    Pilih Kelas
                </option>

                <?php while ($data = mysqli_fetch_assoc($kelas)): ?>

                    <option
                        value="<?= $data["id_kelas"] ?>"
                        <?= $id_kelas == $data["id_kelas"] ? "selected" : "" ?>
                    >

                        <?= htmlspecialchars($data["nama_kelas"]) ?>

                    </option>

                <?php endwhile; ?>

            </select>

        </div>


        <div>

            <label>Mata Pelajaran</label>

            <select name="mapel" required>

                <option value="">
                    Pilih Mata Pelajaran
                </option>

                <?php while ($data = mysqli_fetch_assoc($mapel)): ?>

                    <option
                        value="<?= $data["id_mapel"] ?>"
                        <?= $id_mapel == $data["id_mapel"] ? "selected" : "" ?>
                    >

                        <?= htmlspecialchars($data["nama_mapel"]) ?>

                    </option>

                <?php endwhile; ?>

            </select>

        </div>


        <div>

            <label>Semester</label>

            <select name="semester" required>

                <option value="">
                    Pilih Semester
                </option>

                <?php while ($data = mysqli_fetch_assoc($semester)): ?>

                    <option
                        value="<?= $data["id_semester"] ?>"
                        <?= $id_semester == $data["id_semester"] ? "selected" : "" ?>
                    >

                        <?= htmlspecialchars($data["tahun_ajaran"]) ?>
                        -
                        <?= htmlspecialchars($data["semester"]) ?>

                    </option>

                <?php endwhile; ?>

            </select>

        </div>


        <button type="submit">
            Tampilkan
        </button>

    </form>


<?php

if (
    $id_kelas !== "" &&
    $id_mapel !== "" &&
    $id_semester !== ""
):

    /*
     * Ambil ID guru yang sedang login
     */

    $id_user = $_SESSION["id_user"];

    $query_guru = mysqli_prepare(
        $conn,
        "SELECT id_guru
         FROM guru
         WHERE id_user = ?"
    );

    mysqli_stmt_bind_param(
        $query_guru,
        "i",
        $id_user
    );

    mysqli_stmt_execute($query_guru);

    $result_guru = mysqli_stmt_get_result($query_guru);

    $data_guru = mysqli_fetch_assoc($result_guru);

    $id_guru = $data_guru["id_guru"] ?? 0;


    /*
     * Ambil siswa + nilai yang sudah ada
     */

    $query_siswa = mysqli_prepare(
        $conn,
        "SELECT

            siswa.nis,
            siswa.nama_siswa,

            nilai.nilai_tugas,
            nilai.nilai_uts,
            nilai.nilai_uas,
            nilai.catatan_guru

         FROM siswa

         LEFT JOIN nilai
            ON siswa.nis = nilai.nis
            AND nilai.id_mapel = ?
            AND nilai.id_guru = ?
            AND nilai.id_semester = ?

         WHERE siswa.id_kelas = ?

         ORDER BY siswa.nama_siswa ASC"
    );

    mysqli_stmt_bind_param(
        $query_siswa,
        "iiii",
        $id_mapel,
        $id_guru,
        $id_semester,
        $id_kelas
    );

    mysqli_stmt_execute($query_siswa);

    $result_siswa =
        mysqli_stmt_get_result($query_siswa);

?>

    <br>

    <form
        action="simpan_nilai.php"
        method="POST"
    >

        <input
            type="hidden"
            name="id_mapel"
            value="<?= htmlspecialchars($id_mapel) ?>"
        >

        <input
            type="hidden"
            name="id_semester"
            value="<?= htmlspecialchars($id_semester) ?>"
        >

        <input 
            type="hidden"
            name="tanggal"
            value="<?= htmlspecialchars($tanggal) ?>"
        >


        <table>

            <thead>

                <tr>

                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Absensi</th>
                    <th>Tugas</th>
                    <th>UTS</th>
                    <th>UAS</th>
                    <th>Catatan Guru</th>

                </tr>

            </thead>


            <tbody>

            <?php

            $no = 1;

            while (
                $siswa =
                mysqli_fetch_assoc($result_siswa)
            ):

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

                        <select
                            name="absensi[<?= htmlspecialchars($siswa["nis"]) ?>]"
                        >

                            <option value="Hadir">
                                Hadir
                            </option>

                            <option value="Izin">
                                Izin
                            </option>

                            <option value="Sakit">
                                Sakit
                            </option>

                            <option value="Alpa">
                                Alpa
                            </option>

                        </select>

                    </td>


                    <td>

                        <input
                            type="number"
                            name="tugas[<?= htmlspecialchars($siswa["nis"]) ?>]"
                            min="0"
                            max="100"
                            value="<?= $siswa["nilai_tugas"] !== null ? $siswa["nilai_tugas"] : "" ?>"
                            required
                        >

                    </td>


                    <td>

                        <input
                            type="number"
                            name="uts[<?= htmlspecialchars($siswa["nis"]) ?>]"
                            min="0"
                            max="100"
                            value="<?= $siswa["nilai_uts"] !== null ? $siswa["nilai_uts"] : "" ?>"
                            required
                        >

                    </td>


                    <td>

                        <input
                            type="number"
                            name="uas[<?= htmlspecialchars($siswa["nis"]) ?>]"
                            min="0"
                            max="100"
                            value="<?= $siswa["nilai_uas"] !== null ? $siswa["nilai_uas"] : "" ?>"
                            required
                        >

                    </td>


                    <td>

                        <input
                            type="text"
                            name="catatan[<?= htmlspecialchars($siswa["nis"]) ?>]"
                            value="<?= htmlspecialchars($siswa["catatan_guru"] ?? "") ?>"
                            placeholder="Catatan..."
                        >

                    </td>

                </tr>

            <?php endwhile; ?>

            </tbody>

        </table>


        <br>


        <button type="submit">
            Simpan Semua Nilai
        </button>

    </form>

<?php endif; ?>

</main>

</body>

</html>