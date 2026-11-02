<?php

final class MysqlSessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    private const SESSION_LIFETIME_SECONDS = 2592000;

    public function __construct(private PDO $pdo)
    {
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $statement = $this->pdo->prepare(
            'SELECT session_data FROM app_sessions
             WHERE session_id = :session_id AND expires_at > :now'
        );
        $statement->execute([
            ':session_id' => $id,
            ':now' => time()
        ]);
        $data = $statement->fetchColumn();

        return $data === false ? '' : (string) $data;
    }

    public function write(string $id, string $data): bool
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO app_sessions (session_id, session_data, expires_at)
             VALUES (:session_id, :session_data, :expires_at)
             ON DUPLICATE KEY UPDATE
                session_data = VALUES(session_data),
                expires_at = VALUES(expires_at)'
        );

        return $statement->execute([
            ':session_id' => $id,
            ':session_data' => $data,
            ':expires_at' => time() + self::SESSION_LIFETIME_SECONDS
        ]);
    }

    public function destroy(string $id): bool
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM app_sessions WHERE session_id = :session_id'
        );

        return $statement->execute([':session_id' => $id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM app_sessions WHERE expires_at <= :now'
        );
        $statement->execute([':now' => time()]);

        return $statement->rowCount();
    }

    public function validateId(string $id): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1 FROM app_sessions
             WHERE session_id = :session_id AND expires_at > :now'
        );
        $statement->execute([
            ':session_id' => $id,
            ':now' => time()
        ]);

        return $statement->fetchColumn() !== false;
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        return $this->write($id, $data);
    }
}
