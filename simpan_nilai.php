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

$absensi = $_POST["absensi"] ?? [];
$tugas = $_POST["tugas"] ?? [];
$uts = $_POST["uts"] ?? [];
$uas = $_POST["uas"] ?? [];
$catatan = $_POST["catatan"] ?? [];

// =============================
// Ambil ID guru yang login
// =============================

$id_user = $_SESSION["id_user"];

$query_guru = mysqli_prepare(
    $conn,
    "SELECT id_guru FROM guru WHERE id_user = ?"
);

mysqli_stmt_bind_param($query_guru, "i", $id_user);
mysqli_stmt_execute($query_guru);

$result_guru = mysqli_stmt_get_result($query_guru);
$data_guru = mysqli_fetch_assoc($result_guru);

if (!$data_guru) {
    die("Data guru tidak ditemukan.");
}

$id_guru = $data_guru["id_guru"];

// =============================
// Ambil semester
// =============================

$query_semester = mysqli_query(
    $conn,
    "SELECT id_semester FROM akademik
     ORDER BY id_semester DESC
     LIMIT 1"
);

$data_semester = mysqli_fetch_assoc($query_semester);

if (!$data_semester) {
    die("Data semester belum tersedia.");
}

$id_semester = $data_semester["id_semester"];

// =============================
// Ambil mata pelajaran
// =============================

$query_mapel = mysqli_query(
    $conn,
    "SELECT id_mapel FROM mapel
     ORDER BY id_mapel ASC
     LIMIT 1"
);

$data_mapel = mysqli_fetch_assoc($query_mapel);

if (!$data_mapel) {
    die("Data mata pelajaran belum tersedia.");
}

$id_mapel = $data_mapel["id_mapel"];

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
    // Tugas 30%
    // UTS   30%
    // UAS   40%
    // =============================

    $nilai_akhir =
        ($nilai_tugas * 0.30) +
        ($nilai_uts * 0.30) +
        ($nilai_uas * 0.40);

    // =============================
    // SIMPAN NILAI
    // =============================

    $cek = mysqli_prepare(
        $conn,
        "SELECT id_nilai FROM nilai
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

    } else {

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
    // SIMPAN ABSENSI
    // =============================

    $hadir = 0;
    $izin = 0;
    $sakit = 0;
    $alpa = 0;

    if ($status_absensi === "Hadir") {
        $hadir = 1;
    } elseif ($status_absensi === "Izin") {
        $izin = 1;
    } elseif ($status_absensi === "Sakit") {
        $sakit = 1;
    } elseif ($status_absensi === "Alpa") {
        $alpa = 1;
    }

    // Cek apakah data absensi sudah ada
    $cek_absensi = mysqli_prepare(
        $conn,
        "SELECT id_absensi FROM absensi
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

    $hasil_absensi = mysqli_stmt_get_result($cek_absensi);
    $data_absensi = mysqli_fetch_assoc($hasil_absensi);

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

    } else {

        $insert_absensi = mysqli_prepare(
            $conn,
            "INSERT INTO absensi
            (nis, id_semester, hadir, izin, sakit, alpa)
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

// Selesai
header("Location: penilaian.php?status=success");
exit;

?>