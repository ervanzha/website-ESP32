<?php
session_start();

if (!isset($_SESSION['user'])) {
    // Jika belum login, kembalikan ke halaman login
    header("Location: login.php");
    exit;
}
