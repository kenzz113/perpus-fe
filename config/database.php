<?php
/**
 * config/database.php
 * -----------------------------------------------------------------
 * File koneksi database (PDO + MySQL) untuk Libra Tech.
 * Dipakai bersama oleh semua endpoint di folder /api.
 *
 * Sesuaikan DB_HOST, DB_NAME, DB_USER, DB_PASS dengan konfigurasi
 * server/XAMPP/hosting masing-masing.
 * -----------------------------------------------------------------
 */

declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_NAME = 'perpustakaan_digital'; // ganti sesuai nama database Anda
const DB_USER = 'root';                 // ganti sesuai user MySQL Anda
const DB_PASS = '';                     // ganti sesuai password MySQL Anda
const DB_CHARSET = 'utf8mb4';

/**
 * Membuat dan mengembalikan koneksi PDO ke database.
 * Menggunakan exception mode supaya error mudah ditangkap di endpoint.
 */
function getDbConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // pakai prepared statement asli MySQL
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
}