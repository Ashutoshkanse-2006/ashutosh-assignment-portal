<?php
// Vercel has no permanent disk, so submitted files are stored in the database
// (table uploaded_files) instead of being moved into a folder. download.php serves them back.
// Note: Vercel limits a request body to about 4.5 MB, so uploads must be smaller than that.

function save_upload($tmp_name, $file_name, $enrollment_number) {
    global $conn;
    if (!is_uploaded_file($tmp_name)) return false;
    $data = file_get_contents($tmp_name);
    $mime = function_exists('mime_content_type') ? (mime_content_type($tmp_name) ?: 'application/octet-stream') : 'application/octet-stream';
    $st = pgc_prepare($conn, "INSERT INTO uploaded_files (enrollment_number, filename, mime, data) VALUES (?,?,?,?)");
    pgc_stmt_bind_param($st, "ssss", $enrollment_number, $file_name, $mime, $data);
    return pgc_stmt_execute($st);
}
