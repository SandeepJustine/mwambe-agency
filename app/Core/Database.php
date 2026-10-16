<?php

declare(strict_types=1);

namespace Mwambe\Core;

use PDO;
use PDOException;

final class Database
{
    private ?PDO $pdo = null;

    public function __construct(private readonly Config $config) {}

    public function pdo(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $this->config->require('DB_HOST'),
            $this->config->get('DB_PORT', '3306'),
            $this->config->require('DB_NAME'),
            $this->config->get('DB_CHARSET', 'utf8mb4')
        );

        try {
            $this->pdo = new PDO(
                $dsn,
                (string) $this->config->require('DB_USER'),
                (string) $this->config->get('DB_PASS', ''),
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_STRINGIFY_FETCHES  => false,
                ]
            );
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }

        return $this->pdo;
    }
    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($this->filterParams($sql, $params));
        return $stmt->fetchAll();
    }

    public function selectOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($this->filterParams($sql, $params));
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($this->filterParams($sql, $params));
        return $stmt->rowCount();
    }

    public function insert(string $sql, array $params = []): int
    {
        $this->execute($sql, $params);
        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * Drop any keys from $params that don't appear as :named placeholders in
     * the SQL. Prevents "Invalid parameter number" when a caller merges a
     * broader data array into a narrower query.
     *
     * Only affects string keys. Positional (numeric-index) parameter arrays
     * are passed through untouched.
     */
    private function filterParams(string $sql, array $params): array
    {
        // If any key is numeric, assume positional binding — no filtering possible.
        foreach ($params as $k => $_) {
            if (is_int($k)) {
                return $params;
            }
        }

        // Extract every :name placeholder that appears in the SQL.
        preg_match_all('/:([a-zA-Z_][a-zA-Z0-9_]*)/', $sql, $m);
        $needed = array_flip($m[1]);

        return array_intersect_key($params, $needed);
    }
    
    public function transaction(callable $callback): mixed
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();
        try {
            $result = $callback($this);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}