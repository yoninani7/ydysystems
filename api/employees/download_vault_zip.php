<?php
declare(strict_types=1);
define('IS_API', true);
require_once '../../config.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo 'Unauthorized';
    exit;
}

$empId = $_GET['emp_id'] ?? '';
if (!$empId) {
    http_response_code(400);
    echo 'Missing employee ID.';
    exit;
}

/**
 * Pure PHP ZIP creation (no ZipArchive required).
 * @param array  $files   Array of ['path' => full disk path, 'name' => entry name]
 * @param string $output  Full path for the temporary zip file
 * @return bool True on success
 */
function createZipFile(array $files, string $output): bool {
    $fp = fopen($output, 'wb');
    if (!$fp) return false;

    $entries = '';
    $directory = '';
    $offset = 0;

    foreach ($files as $file) {
        $path = $file['path'];
        $name = $file['name'];
        if (!is_readable($path)) continue;

        $data = file_get_contents($path);
        $crc = crc32($data);
        $size = strlen($data);
        $compressed = function_exists('gzcompress') ? gzcompress($data) : $data;
        $method = function_exists('gzcompress') ? 8 : 0; // 8 = deflate, 0 = store
        $compressedSize = strlen($compressed);

        // Local file header
        $localHeader = "\x50\x4b\x03\x04";              // signature
        $localHeader .= "\x14\x00";                    // version needed to extract (2.0)
        $localHeader .= "\x00\x00";                    // general purpose bit flag
        $localHeader .= pack('v', $method);            // compression method
        $localHeader .= pack('v', 0x0000);             // last mod time (ignored)
        $localHeader .= pack('v', 0x0000);             // last mod date
        $localHeader .= pack('V', $crc);               // crc-32
        $localHeader .= pack('V', $compressedSize);    // compressed size
        $localHeader .= pack('V', $size);              // uncompressed size
        $localHeader .= pack('v', strlen($name));      // file name length
        $localHeader .= pack('v', 0);                  // extra field length
        $localHeader .= $name;
        fwrite($fp, $localHeader);
        fwrite($fp, $compressed);
        $entries .= $localHeader . $compressed;

        // Central directory header
        $directory .= "\x50\x4b\x01\x02";              // central file header signature
        $directory .= "\x14\x00";                      // version made by
        $directory .= "\x14\x00";                      // version needed to extract
        $directory .= "\x00\x00";                      // general purpose bit flag
        $directory .= pack('v', $method);
        $directory .= pack('v', 0x0000);               // last mod time
        $directory .= pack('v', 0x0000);               // last mod date
        $directory .= pack('V', $crc);
        $directory .= pack('V', $compressedSize);
        $directory .= pack('V', $size);
        $directory .= pack('v', strlen($name));
        $directory .= pack('v', 0);                    // extra field length
        $directory .= pack('v', 0);                    // file comment length
        $directory .= pack('v', 0);                    // disk number start
        $directory .= pack('v', 0);                    // internal file attributes
        $directory .= pack('V', 0);                    // external file attributes
        $directory .= pack('V', $offset);              // relative offset of local header
        $directory .= $name;
        // (disk number start, internal attributes, external attributes are 0)

        $offset += strlen($localHeader) + $compressedSize;
    }

    // End of central directory record
    $eocd = "\x50\x4b\x05\x06";                       // end of central dir signature
    $eocd .= "\x00\x00";                               // number of this disk
    $eocd .= "\x00\x00";                               // disk where central directory starts
    $eocd .= pack('v', count($files));                // total entries on this disk
    $eocd .= pack('v', count($files));                // total entries in central directory
    $eocd .= pack('V', strlen($directory));           // size of central directory
    $eocd .= pack('V', $offset);                      // offset of start of central directory
    $eocd .= "\x00\x00";                               // zero-length comment

    fwrite($fp, $directory);
    fwrite($fp, $eocd);
    fclose($fp);
    return true;
}

// Helper to output error messages in plain text (not JSON, because this is a download endpoint)
function zipError(int $code, string $message): void {
    http_response_code($code);
    header('Content-Type: text/plain');
    echo $message;
    exit;
}

try {
    $pdo = get_pdo();

    // Find internal employee ID
    $stmt = $pdo->prepare("SELECT id FROM employees WHERE employee_id = ? AND deleted_at IS NULL");
    $stmt->execute([$empId]);
    $emp = $stmt->fetch();
    if (!$emp) {
        zipError(404, 'Employee not found.');
    }
    $internalEmpId = (int)$emp['id'];

    // Get all uploaded documents for this employee
    $stmt = $pdo->prepare("
        SELECT ed.file_path, ed.file_name, dt.name AS doc_type_name
        FROM employee_documents ed
        JOIN document_types dt ON ed.document_type_id = dt.id
        WHERE ed.employee_id = ? AND ed.status = 'Uploaded'
        ORDER BY dt.name ASC
    ");
    $stmt->execute([$internalEmpId]);
    $docs = $stmt->fetchAll();

    if (empty($docs)) {
        zipError(404, 'This employee has no uploaded documents.');
    }

    // Build array of files for the zip function
    $baseDir = __DIR__ . '/../../';
    $files = [];
    $missing = [];

    foreach ($docs as $doc) {
        $fullPath = $baseDir . $doc['file_path'];
        if (file_exists($fullPath) && is_readable($fullPath)) {
            $entryName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $doc['doc_type_name']) . '_' . $doc['file_name'];
            $files[] = ['path' => $fullPath, 'name' => $entryName];
        } else {
            $missing[] = $doc['file_name'];
        }
    }

    if (empty($files)) {
        zipError(404, 'None of the uploaded files could be read. They may have been moved or deleted.');
    }

    $zipFileName = 'vault_' . $empId . '_' . date('Ymd_His') . '.zip';
    $zipPath = sys_get_temp_dir() . '/' . $zipFileName;

    if (!createZipFile($files, $zipPath)) {
        zipError(500, 'Failed to create ZIP file. Please check server disk space.');
    }

    // Audit log
    $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, user_name, action, module, record_ref, ip_address, logged_at) 
                                VALUES (?, ?, 'EXPORT', 'Document Vault', ?, ?, NOW())");
    $auditStmt->execute([
        $_SESSION['user_id'],
        $_SESSION['username'] ?? 'System',
        "Download zip for $empId",
        $_SERVER['REMOTE_ADDR']
    ]);

    // If some files were missing, log that but still serve the zip with available ones
    if (!empty($missing)) {
        error_log("ZIP for employee $empId: missing files: " . implode(', ', $missing));
    }

    // Send zip
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
    header('Content-Length: ' . filesize($zipPath));
    if (ob_get_level()) ob_end_clean();
    flush();
    readfile($zipPath);
    unlink($zipPath);
    exit;

} catch (Exception $e) {
    if (isset($zipPath) && file_exists($zipPath)) {
        unlink($zipPath);
    }
    zipError(500, 'An unexpected error occurred while creating the ZIP. Please try again.');
}