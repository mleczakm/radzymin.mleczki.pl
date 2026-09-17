<?php

declare(strict_types=1);

namespace App\Storage;

use App\Domain\DuplicateSignatureException;
use App\Domain\Signature;
use DateTimeImmutable;
use PDO;
use PDOException;

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
        $token = bin2hex(random_bytes(32));
        $now = (new DateTimeImmutable())->format(DATE_ATOM);

        $stmt = $this->pdo->prepare(<<<'SQL'
            INSERT INTO signatures
                (petition_slug, first_name, last_name, city, email, email_normalized, token, status, source, ip_hash, created_at)
            VALUES
                (:slug, :first_name, :last_name, :city, :email, :email_normalized, :token, 'pending', 'online', :ip_hash, :created_at)
            SQL);

        try {
            $stmt->execute([
                ':slug' => $petitionSlug,
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':city' => $city,
                ':email' => $email,
                ':email_normalized' => $emailNormalized,
                ':token' => $token,
                ':ip_hash' => $ipHash,
                ':created_at' => $now,
            ]);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                throw new DuplicateSignatureException($petitionSlug, $emailNormalized);
            }

            throw $e;
        }

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    /** Bulk-inserts already-confirmed signatures transcribed from a paper list by the admin. */
    public function createPaper(string $petitionSlug, string $firstName, string $lastName, string $city): Signature
    {
        $now = (new DateTimeImmutable())->format(DATE_ATOM);

        $stmt = $this->pdo->prepare(<<<'SQL'
            INSERT INTO signatures
                (petition_slug, first_name, last_name, city, status, source, created_at, confirmed_at)
            VALUES
                (:slug, :first_name, :last_name, :city, 'confirmed', 'paper', :created_at, :created_at)
            SQL);

        $stmt->execute([
            ':slug' => $petitionSlug,
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':city' => $city,
            ':created_at' => $now,
        ]);

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    public function findById(int $id): Signature
    {
        $stmt = $this->pdo->prepare('SELECT * FROM signatures WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new \RuntimeException("Signature #$id not found.");
        }

        return Signature::fromRow($row);
    }

    public function findByToken(string $token): ?Signature
    {
        $stmt = $this->pdo->prepare('SELECT * FROM signatures WHERE token = :token');
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : Signature::fromRow($row);
    }

    public function confirm(Signature $signature): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE signatures SET status = 'confirmed', confirmed_at = :now WHERE id = :id AND status = 'pending'"
        );
        $stmt->execute([
            ':now' => (new DateTimeImmutable())->format(DATE_ATOM),
            ':id' => $signature->id,
        ]);
    }

    public function countConfirmed(string $petitionSlug): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM signatures WHERE petition_slug = :slug AND status = 'confirmed'"
        );
        $stmt->execute([':slug' => $petitionSlug]);

        return (int) $stmt->fetchColumn();
    }

    /** @return list<Signature> */
    public function allConfirmed(string $petitionSlug): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM signatures WHERE petition_slug = :slug AND status = 'confirmed' ORDER BY confirmed_at ASC"
        );
        $stmt->execute([':slug' => $petitionSlug]);

        return array_map(
            Signature::fromRow(...),
            $stmt->fetchAll(PDO::FETCH_ASSOC),
        );
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
        $stmt = $this->pdo->prepare(
            "SELECT * FROM signatures WHERE petition_slug = :slug AND status = 'confirmed'
                ORDER BY confirmed_at DESC, id DESC LIMIT :limit"
        );
        $stmt->bindValue(':slug', $petitionSlug);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(
            Signature::fromRow(...),
            $stmt->fetchAll(PDO::FETCH_ASSOC),
        );
    }

    public function countPending(string $petitionSlug): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM signatures WHERE petition_slug = :slug AND status = 'pending'"
        );
        $stmt->execute([':slug' => $petitionSlug]);

        return (int) $stmt->fetchColumn();
    }

}
