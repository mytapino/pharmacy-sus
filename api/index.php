<?php
/*
 * Router ตัวเดียวสำหรับ Vercel
 * Vercel แพลน Hobby จำกัด 12 functions ต่อการ deploy จึงให้ทุก request เข้าไฟล์นี้
 * แล้วเรียกไฟล์หน้าเว็บจริงในโฟลเดอร์ pages/ ตาม URL เช่น /medicines.php -> pages/medicines.php
 */

$pages_dir = realpath(__DIR__ . '/../pages');
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = trim(rawurldecode($path), '/');

if ($path === '' || $path === 'index' || $path === 'api/index') {
    $path = 'index.php';
}

$file = realpath($pages_dir . '/' . $path);

// อนุญาตเฉพาะไฟล์ .php ที่อยู่ในโฟลเดอร์ pages/ เท่านั้น
if ($file === false || strpos($file, $pages_dir . DIRECTORY_SEPARATOR) !== 0 || substr($file, -4) !== '.php') {
    http_response_code(404);
    echo 'ไม่พบหน้านี้';
    exit;
}

chdir(dirname($file));
$_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = '/' . $path;
require $file;
