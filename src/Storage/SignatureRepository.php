<?php

declare(strict_types=1);

namespace App\Storage;

use App\Domain\DuplicateSignatureException;
use App\Domain\Signature;
use DateTimeImmutable;
use PDO;
use PDOException;
use PDOStatement;

final class SignatureRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** Creates a pending online signature awaiting e-mail confirmation. */
    public function createOnline(
        string $petitionSlug,
        string $firstName,
        string $lastName,
        string $city,
        string $email,
        string $ipHash,
    ): Signature {
        $emailNormalized = mb_strtolower(trim($email));

        try {
            $this->run(<<<'SQL'
                INSERT INTO signatures
                    (petition_slug, first_name, last_name, city, email, email_normalized, token, status, source, ip_hash, created_at)
                VALUES
                    (:slug, :first_name, :last_name, :city, :email, :email_normalized, :token, 'pending', 'online', :ip_hash, :created_at)
                SQL, [
                ':slug' => $petitionSlug,
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':city' => $city,
                ':email' => $email,
                ':email_normalized' => $emailNormalized,
                ':token' => bin2hex(random_bytes(32)),
                ':ip_hash' => $ipHash,
                ':created_at' => self::now(),
            ]);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                throw new DuplicateSignatureException($petitionSlug, $emailNormalized);
            }

            throw $e;
        }

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    /** Inserts an already-confirmed signature transcribed from a paper list by the admin. */
    public function createPaper(string $petitionSlug, string $firstName, string $lastName, string $city): Signature
    {
        $this->run(<<<'SQL'
            INSERT INTO signatures
                (petition_slug, first_name, last_name, city, status, source, created_at, confirmed_at)
            VALUES
                (:slug, :first_name, :last_name, :city, 'confirmed', 'paper', :created_at, :created_at)
            SQL, [
            ':slug' => $petitionSlug,
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':city' => $city,
            ':created_at' => self::now(),
        ]);

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    public function findById(int $id): Signature
    {
        return $this->fetchOne($this->run('SELECT * FROM signatures WHERE id = :id', [':id' => $id]))
            ?? throw new \RuntimeException("Signature #$id not found.");
    }

    public function findByToken(#[\SensitiveParameter] string $token): ?Signature
    {
        return $this->fetchOne($this->run('SELECT * FROM signatures WHERE token = :token', [':token' => $token]));
    }

    public function confirm(Signature $signature): void
    {
        $this->run(
            "UPDATE signatures SET status = 'confirmed', confirmed_at = :now WHERE id = :id AND status = 'pending'",
            [':now' => self::now(), ':id' => $signature->id],
        );
    }

    public function countConfirmed(string $petitionSlug): int
    {
        return $this->countByStatus($petitionSlug, 'confirmed');
    }

    public function countPending(string $petitionSlug): int
    {
        return $this->countByStatus($petitionSlug, 'pending');
    }

    /** @return list<Signature> */
    public function allConfirmed(string $petitionSlug): array
    {
        return $this->fetchAll($this->run(
            "SELECT * FROM signatures WHERE petition_slug = :slug AND status = 'confirmed' ORDER BY confirmed_at ASC",
            [':slug' => $petitionSlug],
        ));
    }

    /**
     * Most recently confirmed signatures for the public "Niedawno podpisali" list — newest first.
     *
     * @return list<Signature>
     */
    public function recentConfirmed(string $petitionSlug, int $limit = 8): array
    {
        // id DESC as a tiebreaker: confirmed_at has only second precision, so signatures
        // confirmed within the same second (e.g. a batch paper import) would tie otherwise.
        $stmt = $this->prepare(
            "SELECT * FROM signatures WHERE petition_slug = :slug AND status = 'confirmed'
                ORDER BY confirmed_at DESC, id DESC LIMIT :limit"
        );
        $stmt->bindValue(':slug', $petitionSlug);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $this->fetchAll($stmt);
    }

    private function countByStatus(string $petitionSlug, string $status): int
    {
        $stmt = $this->run(
            'SELECT COUNT(*) FROM signatures WHERE petition_slug = :slug AND status = :status',
            [':slug' => $petitionSlug, ':status' => $status],
        );

        return (int) $stmt->fetchColumn();
    }

    private static function now(): string
    {
        return (new DateTimeImmutable())->format(DATE_ATOM);
    }

    private function prepare(string $sql): PDOStatement
    {
        // Database::connect() enables ERRMODE_EXCEPTION, so a failed prepare throws and never
        // returns false; this only narrows PDO's `PDOStatement|false` type in one place.
        return $this->pdo->prepare($sql) ?: throw new \LogicException('PDO::prepare() returned false.');
    }

    /** @param array<string, int|string> $params */
    private function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    private function fetchOne(PDOStatement $stmt): ?Signature
    {
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : Signature::fromRow($row);
    }

    /** @return list<Signature> */
    private function fetchAll(PDOStatement $stmt): array
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(Signature::fromRow(...), $rows);
    }
}
