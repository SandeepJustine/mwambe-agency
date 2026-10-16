<?php

declare(strict_types=1);

namespace Mwambe\Services;

use Mwambe\Core\Auth;
use Mwambe\Core\Config;
use Mwambe\Core\Database;

final class MediaUploader
{
    private const ALLOWED_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private readonly Database $db,
        private readonly Config $config,
        private readonly Auth $auth,
    ) {}

    /** @return array{ok:bool,error?:string,media?:array<string,mixed>} */
    public function storeImage(array $file, ?string $altText = null, ?string $caption = null): array
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'No file uploaded or upload failed.'];
        }

        $maxBytes = (int) $this->config->get('UPLOAD_MAX_BYTES', 5242880);
        if ($file['size'] > $maxBytes) {
            return ['ok' => false, 'error' => 'File exceeds maximum size of ' . round($maxBytes / 1024 / 1024, 1) . ' MB.'];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!isset(self::ALLOWED_MIME[$mime])) {
            return ['ok' => false, 'error' => 'Unsupported image type. Allowed: JPEG, PNG, WebP.'];
        }

        // Verify it really is an image (rejects polyglot files)
        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
            return ['ok' => false, 'error' => 'File is not a valid image.'];
        }

        [$width, $height] = $info;
        $extension = self::ALLOWED_MIME[$mime];

        // Store outside web root in storage/uploads, then symlink/copy into public.
        $uploadDir = BASE_PATH . '/public/uploads';
        if (!is_dir($uploadDir)) {
            if (!@mkdir($uploadDir, 0755, true)) {
                return ['ok' => false, 'error' => 'Cannot create uploads directory.'];
            }
        }

        $basename = date('Y/m') . '/' . bin2hex(random_bytes(12)) . '.' . $extension;
        $target   = $uploadDir . '/' . $basename;
        $targetDir = dirname($target);
        if (!is_dir($targetDir) && !@mkdir($targetDir, 0755, true)) {
            return ['ok' => false, 'error' => 'Cannot create upload directory.'];
        }

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return ['ok' => false, 'error' => 'Failed to store uploaded file.'];
        }

        $id = $this->db->insert("
            INSERT INTO media (filename, original_name, mime_type, size_bytes, width, height, alt_text, caption, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $basename,
            mb_substr((string) $file['name'], 0, 250),
            $mime,
            (int) $file['size'],
            (int) $width,
            (int) $height,
            $altText ? mb_substr($altText, 0, 250) : null,
            $caption ? mb_substr($caption, 0, 500) : null,
            $this->auth->id(),
        ]);

        $media = $this->db->selectOne('SELECT * FROM media WHERE id = ?', [$id]);

        return ['ok' => true, 'media' => $media];
    }
}