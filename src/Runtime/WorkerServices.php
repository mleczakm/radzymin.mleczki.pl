<?php

declare(strict_types=1);

namespace App\Runtime;

use App\Content\MarkdownLoader;
use App\Domain\PetitionRepository;
use App\Domain\TopicRepository;
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
    public private(set) PDO $pdo;
    public private(set) PetitionRepository $petitions;
    public private(set) TopicRepository $topics;
    /** @var array{heading: string, html: string}|null */
    public private(set) ?array $about;
    public private(set) SignatureRepository $signatures;
    public private(set) ConfirmationMailer $mailer;
    public private(set) LoggerInterface $logger;
    public private(set) FormTimingToken $timingToken;
    public private(set) RateLimiter $rateLimiter;
    public private(set) IpHasher $ipHasher;
    public private(set) BasicAuth $adminAuth;
    public private(set) string $baseUrl;

    public function __construct(private readonly Table $rateLimitTable)
    {
    }

    public function boot(): void
    {
        $appSecret = env('APP_SECRET') ?? throw new \RuntimeException('APP_SECRET is not configured.');

        $this->pdo = Database::connect(env('DB_PATH', dirname(__DIR__, 2) . '/var/data.sqlite'));
        $this->petitions = new PetitionRepository(dirname(__DIR__, 2) . '/content/petitions');
        $this->topics = new TopicRepository(dirname(__DIR__, 2) . '/content/topics');
        $this->about = self::loadAbout(dirname(__DIR__, 2) . '/content/about.md');
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

    /** @return array{heading: string, html: string}|null */
    private static function loadAbout(string $file): ?array
    {
        if (!is_file($file)) {
            return null;
        }

        $document = (new MarkdownLoader())->load($file);

        return [
            'heading' => (string) ($document->frontMatter['heading'] ?? 'Napisz do mnie'),
            'html' => $document->html,
        ];
    }
}
