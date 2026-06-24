<?php
/**
 * api/search_anggota.php
 * -----------------------------------------------------------------
 * Endpoint pencarian data peminjaman berdasarkan nama anggota.
 *
 * Method : GET
 * Param  : nama (string, wajib)
 * Contoh : GET /api/search_anggota.php?nama=Panji
 *
 * Response JSON:
 * {
 *   "status"  : "success" | "empty" | "error",
 *   "message" : "...",
 *   "data"    : [ { nama_anggota, judul_buku, penulis,
 *                   tanggal_pinjam, tanggal_kembali,
 *                   status_peminjaman }, ... ]
 * }
 *
 * Keamanan:
 * - Menggunakan PDO Prepared Statement (bindValue) -> aman dari SQL Injection.
 * - Pencarian case-insensitive dengan LOWER() pada kolom nama & keyword.
 * -----------------------------------------------------------------
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// Batasi method yang diizinkan
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Method tidak diizinkan. Gunakan GET.',
        'data'    => [],
    ]);
    exit;
}

require_once __DIR__ . '/../config/database.php';

// ----- Ambil & validasi input -----
$nama = isset($_GET['nama']) ? trim((string) $_GET['nama']) : '';

if ($nama === '') {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Parameter nama anggota wajib diisi.',
        'data'    => [],
    ]);
    exit;
}

if (mb_strlen($nama) > 100) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Kata kunci pencarian terlalu panjang.',
        'data'    => [],
    ]);
    exit;
}

try {
    $pdo = getDbConnection();

    /**
     * Sesuaikan nama tabel & kolom berikut dengan struktur database
     * yang sudah Anda buat:
     *   - anggota   (id_anggota, nama_anggota, ...)
     *   - buku      (id_buku, judul_buku, penulis, ...)
     *   - peminjaman(id_peminjaman, id_anggota, id_buku,
     *                tanggal_pinjam, tanggal_kembali, status)
     */
    $sql = "
        SELECT
            a.nama_anggota    AS nama_anggota,
            b.judul_buku      AS judul_buku,
            b.penulis         AS penulis,
            p.tanggal_pinjam  AS tanggal_pinjam,
            p.tanggal_kembali AS tanggal_kembali,
            p.status          AS status_peminjaman
        FROM peminjaman p
        INNER JOIN anggota a ON a.id_anggota = p.id_anggota
        INNER JOIN buku    b ON b.id_buku    = p.id_buku
        WHERE LOWER(a.nama_anggota) LIKE LOWER(:keyword)
        ORDER BY p.tanggal_pinjam DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':keyword', '%' . $nama . '%', PDO::PARAM_STR);
    $stmt->execute();

    $rows = $stmt->fetchAll();

    if (count($rows) === 0) {
        echo json_encode([
            'status'  => 'empty',
            'message' => 'Anggota dengan nama "' . htmlspecialchars($nama, ENT_QUOTES, 'UTF-8')
                       . '" tidak ditemukan, atau belum pernah meminjam buku.',
            'data'    => [],
        ]);
        exit;
    }

    echo json_encode([
        'status'  => 'success',
        'message' => 'Ditemukan ' . count($rows) . ' data peminjaman.',
        'data'    => $rows,
    ]);

} catch (PDOException $e) {
    // Jangan tampilkan detail error database ke client (risiko keamanan).
    // error_log('search_anggota.php DB error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
        'data'    => [],
    ]);
}