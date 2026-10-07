<?php
// Database connection (Supabase / PostgreSQL) + a tiny mysqli-style compatibility layer.
// The pages were written for mysqli; they now call pgc_* functions with the same behaviour,
// backed by PDO (pdo_pgsql). Settings come from Environment Variables:
//   DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_NAME, DB_SCHEMA

function env_value($key, $default = null) {
    $v = getenv($key);
    if ($v === false || $v === '') $v = $_ENV[$key] ?? $_SERVER[$key] ?? '';
    return ($v === '' || $v === null) ? $default : $v;
}

class PgcConn {
    public $pdo;
    public $lastId = 0;
    public $error = '';
}

class PgcResult implements IteratorAggregate {
    public $rows = [];
    public $pos = 0;
    public $num_rows = 0;
    public function __construct($rows) { $this->rows = $rows; $this->num_rows = count($rows); }
    public function getIterator(): Iterator { return new ArrayIterator($this->rows); }
}

class PgcStmt {
    public $conn;
    public $sql;
    public $params = [];
    public $types = '';
    public $result = false;
    public $affected = 0;
}

function db_connect($defaultSchema) {
    $host   = env_value('DB_HOST', 'localhost');
    $user   = env_value('DB_USER', 'postgres');
    $pass   = env_value('DB_PASS', '');
    $name   = env_value('DB_NAME', 'postgres');
    $port   = (int) env_value('DB_PORT', 5432);
    $schema = env_value('DB_SCHEMA', $defaultSchema);

    $c = new PgcConn();
    $lastErr = '';
    // DB_HOST may hold several comma-separated hosts (e.g. different Supabase pooler regions); first that works wins
    foreach (array_map('trim', explode(',', $host)) as $h) {
        try {
            $c->pdo = new PDO("pgsql:host=$h;port=$port;dbname=$name;sslmode=require;connect_timeout=8", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $c->pdo->exec('SET search_path TO "' . str_replace('"', '', $schema) . '"');
            return $c;
        } catch (Throwable $ex) {
            $lastErr = $ex->getMessage();
        }
    }
    die("Database connection failed: " . $lastErr);

}

// MySQL -> PostgreSQL differences used by these projects
function pgc_translate($sql) {
    return preg_replace('/\bLIKE\b/i', 'ILIKE', $sql);   // MySQL LIKE is case-insensitive
}

function pgc_clean_rows($rows) {
    foreach ($rows as &$r) {
        foreach ($r as $k => $v) {
            if (is_resource($v)) $r[$k] = stream_get_contents($v);   // bytea comes back as a stream
        }
    }
    return $rows;
}

function pgc_is_insert_with_id($sql) {
    return preg_match('/^\s*INSERT\s+INTO\s+(?!php_sessions\b)/i', $sql) && !preg_match('/\bRETURNING\b/i', $sql);
}

function pgc_run($conn, $sql, $params = [], $types = '') {
    $sql = pgc_translate($sql);
    $withId = pgc_is_insert_with_id($sql);
    if ($withId) $sql = rtrim(rtrim($sql), ';') . ' RETURNING id';

    // index of the binary "data" column in INSERT INTO uploaded_files (...)
    $blobIdx = -1;
    if (preg_match('/^\s*INSERT\s+INTO\s+uploaded_files\s*\(([^)]*)\)/i', $sql, $m)) {
        $cols = array_map('trim', explode(',', strtolower($m[1])));
        $blobIdx = array_search('data', $cols, true);
        if ($blobIdx === false) $blobIdx = -1;
    }

    try {
        $st = $conn->pdo->prepare($sql);
        foreach (array_values($params) as $i => $v) {
            $t = $types[$i] ?? 's';
            if ($v === null) {
                $st->bindValue($i + 1, null, PDO::PARAM_NULL);
            } elseif ($i === $blobIdx) {
                $fh = fopen('php://memory', 'w+');
                fwrite($fh, (string) $v);
                rewind($fh);
                $st->bindValue($i + 1, $fh, PDO::PARAM_LOB);
            } elseif ($t === 'i') {
                $st->bindValue($i + 1, (int) $v, PDO::PARAM_INT);
            } else {
                $st->bindValue($i + 1, (string) $v, PDO::PARAM_STR);
            }
        }
        $st->execute();
    } catch (Throwable $ex) {
        $conn->error = $ex->getMessage();
        throw $ex;
    }

    $out = ['affected' => $st->rowCount(), 'result' => false];
    if ($withId) {
        $row = $st->fetch(PDO::FETCH_NUM);
        $conn->lastId = $row ? (int) $row[0] : 0;
    } elseif ($st->columnCount() > 0) {
        $out['result'] = new PgcResult(pgc_clean_rows($st->fetchAll(PDO::FETCH_ASSOC)));
    }
    return $out;
}

function pgc_query($conn, $sql) {
    $r = pgc_run($conn, $sql);
    return $r['result'] !== false ? $r['result'] : true;
}
function pgc_prepare($conn, $sql) { $s = new PgcStmt(); $s->conn = $conn; $s->sql = $sql; return $s; }
function pgc_stmt_bind_param($st, $types, &...$params) { $st->types = $types; $st->params = &$params; return true; }
function pgc_stmt_execute($st) {
    $r = pgc_run($st->conn, $st->sql, $st->params, $st->types);
    $st->result = $r['result'];
    $st->affected = $r['affected'];
    return true;
}
function pgc_stmt_get_result($st) { return $st->result; }
function pgc_stmt_affected_rows($st) { return $st->affected; }
function pgc_fetch_assoc($res) {
    if (!$res instanceof PgcResult || $res->pos >= $res->num_rows) return null;
    return $res->rows[$res->pos++];
}
function pgc_fetch_row($res) {
    $r = pgc_fetch_assoc($res);
    return $r === null ? null : array_values($r);
}
function pgc_fetch_all($res) { return $res instanceof PgcResult ? $res->rows : []; }
function pgc_num_rows($res) { return $res instanceof PgcResult ? $res->num_rows : 0; }
function pgc_insert_id($conn) { return $conn->lastId; }
function pgc_error($conn) { return $conn->error; }
function pgc_begin_transaction($conn) { return $conn->pdo->beginTransaction(); }
function pgc_commit($conn) { return $conn->pdo->inTransaction() ? $conn->pdo->commit() : true; }
function pgc_rollback($conn) { return $conn->pdo->inTransaction() ? $conn->pdo->rollBack() : true; }
