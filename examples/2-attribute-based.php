<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use FacelessLogger\Anonymization\AnonymizationProcessor;
use FacelessLogger\Anonymization\Attribute\Anonymize;
use FacelessLogger\Anonymization\Strategy\HashStrategy;
use FacelessLogger\Anonymization\Strategy\MaskStrategy;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


// ------------------------------------
// Example 2: Attribute-Based Strategy
// ------------------------------------

final class UserProfile
{
    #[Anonymize(new MaskStrategy('*', 2))]
    public string $name;

    #[Anonymize(new HashStrategy('sha256'))]
    public string $email;

    public string $role;

    public function __construct(string $name, string $email, string $role)
    {
        $this->name  = $name;
        $this->email = $email;
        $this->role  = $role;
    }
}

$logger = new Logger('faceless');
$logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));
$logger->pushProcessor(new AnonymizationProcessor());

$user = new UserProfile('John Doe', 'john.doe@example.com', 'admin');

$logger->info('User profile updated', ['user' => $user]);
