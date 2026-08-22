<?php

session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit;
}

require_once "database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: penilaian.php");
    exit;
}

// =============================
// Ambil data dari form
// =============================

$id_mapel = (int) ($_POST["id_mapel"] ?? 0);
$id_semester = (int) ($_POST["id_semester"] ?? 0);
$tanggal = $_POST["tanggal"] ?? date("Y-m-d");

$absensi = $_POST["absensi"] ?? [];
$tugas = $_POST["tugas"] ?? [];
$uts = $_POST["uts"] ?? [];
$uas = $_POST["uas"] ?? [];
$catatan = $_POST["catatan"] ?? [];


// =============================
// Validasi
// =============================

if ($id_mapel <= 0 || $id_semester <= 0) {
    die("Mapel atau semester belum dipilih.");
}


// =============================
// Ambil ID guru
// =============================

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

if (!$data_guru) {
    die("Data guru tidak ditemukan.");
}

$id_guru = $data_guru["id_guru"];


// =============================
// Proses setiap siswa
// =============================

foreach ($tugas as $nis => $nilai_tugas) {

    $nilai_tugas = (int) $nilai_tugas;
    $nilai_uts = (int) ($uts[$nis] ?? 0);
    $nilai_uas = (int) ($uas[$nis] ?? 0);

    $catatan_siswa = $catatan[$nis] ?? "";
    $status_absensi = $absensi[$nis] ?? "Hadir";


    // Validasi nilai

    if (
        $nilai_tugas < 0 || $nilai_tugas > 100 ||
        $nilai_uts < 0 || $nilai_uts > 100 ||
        $nilai_uas < 0 || $nilai_uas > 100
    ) {
        continue;
    }


    // =============================
    // Hitung nilai akhir
    // =============================

    $nilai_akhir =
        ($nilai_tugas * 0.30) +
        ($nilai_uts * 0.30) +
        ($nilai_uas * 0.40);


    // =============================
    // Cek nilai yang sudah ada
    // =============================

    $cek = mysqli_prepare(
        $conn,
        "SELECT id_nilai
         FROM nilai
         WHERE nis = ?
         AND id_mapel = ?
         AND id_guru = ?
         AND id_semester = ?"
    );

    mysqli_stmt_bind_param(
        $cek,
        "siii",
        $nis,
        $id_mapel,
        $id_guru,
        $id_semester
    );

    mysqli_stmt_execute($cek);

    $hasil_cek = mysqli_stmt_get_result($cek);

    $data_nilai = mysqli_fetch_assoc($hasil_cek);


    // =============================
    // UPDATE nilai
    // =============================

    if ($data_nilai) {

        $update = mysqli_prepare(
            $conn,
            "UPDATE nilai SET
                nilai_tugas = ?,
                nilai_uts = ?,
                nilai_uas = ?,
                nilai_akhir = ?,
                catatan_guru = ?
             WHERE id_nilai = ?"
        );

        mysqli_stmt_bind_param(
            $update,
            "iiidsi",
            $nilai_tugas,
            $nilai_uts,
            $nilai_uas,
            $nilai_akhir,
            $catatan_siswa,
            $data_nilai["id_nilai"]
        );

        mysqli_stmt_execute($update);

    }


    // =============================
    // INSERT nilai baru
    // =============================

    else {

        $insert = mysqli_prepare(
            $conn,
            "INSERT INTO nilai
            (
                nis,
                id_mapel,
                id_guru,
                nilai_tugas,
                nilai_uts,
                nilai_uas,
                nilai_akhir,
                catatan_guru,
                id_semester
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $insert,
            "siiiiidsi",
            $nis,
            $id_mapel,
            $id_guru,
            $nilai_tugas,
            $nilai_uts,
            $nilai_uas,
            $nilai_akhir,
            $catatan_siswa,
            $id_semester
        );

        mysqli_stmt_execute($insert);
    }


   // =============================
// SIMPAN ABSENSI HARIAN
// =============================

$status_absensi = $absensi[$nis] ?? "Hadir";


// Cek apakah siswa sudah memiliki absensi
// pada tanggal tersebut

$cek_absensi = mysqli_prepare(
    $conn,
    "SELECT id_absensi
     FROM absensi_harian
     WHERE nis = ?
     AND tanggal = ?"
);

mysqli_stmt_bind_param(
    $cek_absensi,
    "ss",
    $nis,
    $tanggal
);

mysqli_stmt_execute($cek_absensi);

$result_absensi =
    mysqli_stmt_get_result($cek_absensi);

$data_absensi =
    mysqli_fetch_assoc($result_absensi);


// Jika sudah ada → UPDATE

if ($data_absensi) {

    $update_absensi = mysqli_prepare(
        $conn,
        "UPDATE absensi_harian
         SET status = ?,
             id_semester = ?
         WHERE id_absensi = ?"
    );

    mysqli_stmt_bind_param(
        $update_absensi,
        "sii",
        $status_absensi,
        $id_semester,
        $data_absensi["id_absensi"]
    );

    mysqli_stmt_execute($update_absensi);

}


// Jika belum ada → INSERT

else {

    $insert_absensi = mysqli_prepare(
        $conn,
        "INSERT INTO absensi_harian
        (
            nis,
            id_semester,
            tanggal,
            status
        )
        VALUES (?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $insert_absensi,
        "siss",
        $nis,
        $id_semester,
        $tanggal,
        $status_absensi
    );

    mysqli_stmt_execute($insert_absensi);

}

    // =============================
    // Cek absensi
    // =============================

    $cek_absensi = mysqli_prepare(
        $conn,
        "SELECT id_absensi
         FROM absensi
         WHERE nis = ?
         AND id_semester = ?"
    );

    mysqli_stmt_bind_param(
        $cek_absensi,
        "si",
        $nis,
        $id_semester
    );

    mysqli_stmt_execute($cek_absensi);

    $hasil_absensi =
        mysqli_stmt_get_result($cek_absensi);

    $data_absensi =
        mysqli_fetch_assoc($hasil_absensi);


    // =============================
    // Update absensi
    // =============================

    if ($data_absensi) {

        $update_absensi = mysqli_prepare(
            $conn,
            "UPDATE absensi SET
                hadir = hadir + ?,
                izin = izin + ?,
                sakit = sakit + ?,
                alpa = alpa + ?
             WHERE id_absensi = ?"
        );

        mysqli_stmt_bind_param(
            $update_absensi,
            "iiiii",
            $hadir,
            $izin,
            $sakit,
            $alpa,
            $data_absensi["id_absensi"]
        );

        mysqli_stmt_execute($update_absensi);

    }


    // =============================
    // Insert absensi
    // =============================

    else {

        $insert_absensi = mysqli_prepare(
            $conn,
            "INSERT INTO absensi
            (
                nis,
                id_semester,
                hadir,
                izin,
                sakit,
                alpa
            )
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $insert_absensi,
            "siiiii",
            $nis,
            $id_semester,
            $hadir,
            $izin,
            $sakit,
            $alpa
        );

        mysqli_stmt_execute($insert_absensi);
    }

}


// =============================
// Selesai
// =============================

header(
    "Location: penilaian.php?status=success"
);

exit;

?>