<?php

declare(strict_types=1);

namespace App\Runtime;

use App\Domain\PetitionRepository;
use App\Mail\ConfirmationMailer;
use App\Mail\MailerFactory;
use App\Security\BasicAuth;
use App\Security\FormTimingToken;
use App\Security\IpHasher;
use App\Security\RateLimiter;
use App\Storage\Database;
use App\Storage\SignatureRepository;
use PDO;
use Psr\Log\LoggerInterface;
use Swoole\Table;

/**
 * Per-worker-process service registry.
 *
 * Built lazily in an onWorkerStart handler (i.e. after Swoole forks the
 * worker) because resources like PDO/SQLite connections are not safe to
 * share across forked processes.
 */
final class WorkerServices
{
    public readonly PDO $pdo;
    public readonly PetitionRepository $petitions;
    public readonly SignatureRepository $signatures;
    public readonly ConfirmationMailer $mailer;
    public readonly LoggerInterface $logger;
    public readonly FormTimingToken $timingToken;
    public readonly RateLimiter $rateLimiter;
    public readonly IpHasher $ipHasher;
    public readonly BasicAuth $adminAuth;
    public readonly string $baseUrl;

    public function __construct(private readonly Table $rateLimitTable)
    {
    }

    public function boot(): void
    {
        $appSecret = env('APP_SECRET') ?? throw new \RuntimeException('APP_SECRET is not configured.');

        $this->pdo = Database::connect(env('DB_PATH', dirname(__DIR__, 2) . '/var/data.sqlite'));
        $this->petitions = new PetitionRepository(dirname(__DIR__, 2) . '/content/petitions');
        $this->signatures = new SignatureRepository($this->pdo);
        $this->logger = \App\Log\Factory::create(filter_var(env('APP_DEBUG', '0'), FILTER_VALIDATE_BOOLEAN));
        $this->timingToken = new FormTimingToken($appSecret);
        $this->ipHasher = new IpHasher($appSecret);
        $this->rateLimiter = new RateLimiter($this->rateLimitTable);
        $this->baseUrl = env('BASE_URL', 'https://radzymin.mleczki.pl');

        $this->mailer = new ConfirmationMailer(
            MailerFactory::create(),
            env('MAIL_FROM', 'radzymin.mleczki@gmail.com'),
            $this->baseUrl,
        );

        $this->adminAuth = new BasicAuth(
            env('ADMIN_USER') ?? throw new \RuntimeException('ADMIN_USER is not configured.'),
            env('ADMIN_PASSWORD_HASH') ?? throw new \RuntimeException('ADMIN_PASSWORD_HASH is not configured.'),
        );
    }
}
