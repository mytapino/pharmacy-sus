<?php
/*
 * เชื่อมต่อฐานข้อมูล PostgreSQL (Supabase)
 *
 * ค่าการเชื่อมต่ออ่านจาก Environment Variable (ตั้งใน Vercel > Settings > Environment Variables)
 *   DATABASE_URL = postgresql://USER:PASSWORD@HOST:PORT/postgres
 *   (ใช้ Connection string แบบ "Transaction pooler" ของ Supabase, port 6543)
 *
 * ถ้ารันในเครื่อง ให้ตั้ง DATABASE_URL ก่อนรัน เช่น
 *   DATABASE_URL="postgresql://postgres:password@localhost:5432/pharmacy" php -S localhost:8000 api/index.php
 */

date_default_timezone_set('Asia/Bangkok');

// ซ่อนข้อความ Deprecated ของ PHP 8.x (เช่น htmlspecialchars(null)) ไม่ให้โผล่บนหน้าเว็บระหว่างทดสอบ
error_reporting(E_ALL & ~E_DEPRECATED);

// ไม่มีระบบ login: ทุกการขายจะถูกบันทึกเป็นพนักงานคนนี้ (ดู database/supabase_schema.sql)
define('DEFAULT_EMPLOYEE_ID', (int)(getenv('DEFAULT_EMPLOYEE_ID') ?: 21));
define('DEFAULT_EMPLOYEE_NAME', 'Admin');

$database_url = getenv('DATABASE_URL') ?: getenv('POSTGRES_URL');

if (!$database_url) {
    die("ยังไม่ได้ตั้งค่า DATABASE_URL");
}

$parts = parse_url($database_url);
$host = $parts['host'] ?? 'localhost';
$port = $parts['port'] ?? 5432;
$db_name = ltrim($parts['path'] ?? '/postgres', '/');
$username = urldecode($parts['user'] ?? '');
$password = urldecode($parts['pass'] ?? '');
$is_local = in_array($host, ['localhost', '127.0.0.1'], true);
$sslmode = getenv('PGSSLMODE') ?: ($is_local ? 'prefer' : 'require');

try {
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$db_name;sslmode=$sslmode",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Supabase Transaction pooler (PgBouncer) ไม่รองรับ prepared statement ฝั่งเซิร์ฟเวอร์
            PDO::ATTR_EMULATE_PREPARES => true,
        ]
    );
    $conn->exec("SET TIME ZONE 'Asia/Bangkok'");
} catch (PDOException $e) {
    die("เชื่อมต่อฐานข้อมูลไม่ได้: " . $e->getMessage());
}
