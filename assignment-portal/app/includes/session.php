<?php
// Sessions on Vercel: serverless functions have no permanent disk, so PHP's normal file-based
// sessions would forget you between requests. This stores sessions in the database instead.
// Include this file instead of calling session_start().

require_once __DIR__ . '/db.php';   // creates $conn

class DbSessionHandler implements SessionHandlerInterface {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function open(string $path, string $name): bool {
        return true;
    }
    public function close(): bool { return true; }
    public function read(string $id): string|false {
        $st = pgc_prepare($this->conn, "SELECT data FROM php_sessions WHERE id=? AND expires>?");
        $now = time();
        pgc_stmt_bind_param($st, "si", $id, $now);
        pgc_stmt_execute($st);
        $row = pgc_fetch_assoc(pgc_stmt_get_result($st));
        return $row ? $row['data'] : '';
    }
    public function write(string $id, string $data): bool {
        $st = pgc_prepare($this->conn, "INSERT INTO php_sessions (id, data, expires) VALUES (?,?,?) ON CONFLICT (id) DO UPDATE SET data=EXCLUDED.data, expires=EXCLUDED.expires");
        $exp = time() + 86400;
        pgc_stmt_bind_param($st, "ssi", $id, $data, $exp);
        return pgc_stmt_execute($st);
    }
    public function destroy(string $id): bool {
        $st = pgc_prepare($this->conn, "DELETE FROM php_sessions WHERE id=?");
        pgc_stmt_bind_param($st, "s", $id);
        return pgc_stmt_execute($st);
    }
    public function gc(int $max_lifetime): int|false {
        pgc_query($this->conn, "DELETE FROM php_sessions WHERE expires<" . time());
        return 0;
    }
}

if (session_status() === PHP_SESSION_NONE && !defined('NO_SESSION')) {
    session_set_save_handler(new DbSessionHandler($conn), true);
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'secure' => $https,
        'httponly' => true, 'samesite' => 'Lax',
    ]);
    session_start();
}
