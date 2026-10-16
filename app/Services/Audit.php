<?php

declare(strict_types=1);

namespace Mwambe\Services;

use Mwambe\Core\Auth;
use Mwambe\Core\Database;

final class Audit
{
    public function __construct(private readonly Database $db, private readonly Auth $auth) {}

    public function log(string $action, ?string $entityType = null, ?int $entityId = null, ?string $summary = null, array $metadata = []): void
    {
        $this->db->execute("
            INSERT INTO audit_logs (user_id, action, entity_type, entity_id, summary, metadata, ip_address)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ", [
            $this->auth->id(),
            $action,
            $entityType,
            $entityId,
            $summary,
            $metadata ? json_encode($metadata, JSON_UNESCAPED_SLASHES) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}