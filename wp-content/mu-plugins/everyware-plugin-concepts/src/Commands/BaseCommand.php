<?php declare(strict_types=1);

namespace Everyware\Concepts\Commands;

use Everyware\Concepts\Admin\OpenContentClient;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Infomaker\Everyware\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class BaseCommand
 * @package NavigaWeb\Console
 */
abstract class BaseCommand extends Command
{
    /**
     * @var array
     */
    protected static $descriptions = [
        'site' => 'The targeted site for the api-requests.',
    ];

    /**
     * Contains any environment variables found
     *
     * @var array
     */
    protected static $env = [];

    /**
     * @var SymfonyStyle
     */
    protected $ioInstance;

    protected $progressFormats = [
        'standard' => ' %message% %percent%% done, %remaining% remaining...',
        'syncing' => ' %message% %percent%% done, %remaining% remaining...'
    ];

    /**
     * Contains eventual errors that may have occurred while in progress of syncing
     *
     * @var array
     */
    protected $syncErrors = [];

    /**
     * @var OpenContentClient
     */
    protected $client;

    /**
     * @var StateHandler
     */
    protected $state;
    /**
     * @var bool
     */
    private $requestHasError;

    public function __construct(OpenContentClient $client, StateHandler $storage, string $name = null)
    {
        $this->client = $client;
        $this->state = $storage;

        parent::__construct($name);
    }

    public function continueOngoingBootstrapProcess(
        string $currentSite,
        StateHandler $state,
        SymfonyStyle $io
    ): bool {
        if ( ! $state->hasStoredConcepts()) {
            return false;
        }

        $io->note('You have already started a bootstrap progress for "' . $state->getCurrentSite() . '"!');

        // Offer option to quit if there still are concepts left and site is actually set.
        if ( ! $this->state->onCurrentSite($currentSite)) {

            if ($io->confirm('Maybe you should quit and finish that first. Do you want to quit?')) {
                exit;
            }

            return false;
        }

        return $io->confirm('Do you want to pick up where you left off?');
    }

    protected function extractEnv(string $file): array
    {
        // If we have stored variables just return them
        if (isset(static::$env[$file])) {
            return static::$env[$file];
        }

        // check if file exist first
        if ( ! file_exists($file)) {
            return [];
        }

        $fileLines = array_filter(file($file));

        $variables = [];
        foreach ($fileLines as $line) {
            $match = trim($line, PHP_EOL);

            // Don't mind empty variables or variable that have been commented out
            if (empty($match) || Str::startsWith($match, '#') || Str::endsWith($match, '=')) {
                continue;
            }

            // Separate variable into key value
            [$key, $value] = preg_split('/\s*(=)\s*/', $match, 2);

            // Remove all wrappigng quotes
            $variables[$key] = trim($value, PHP_EOL . '"');
        }

        // Store and return variables
        return static::$env[$file] = $variables;
    }

    /**
     * @param string      $variable The variables to retrieve
     * @param string|null $file     Optional to specify other file than "/.env"
     *
     * @return string
     */
    protected function getEnv(string $variable, $file = null): ?string
    {
        // default to .env from "current working directory"
        $env = $this->extractEnv($file ?? getcwd() . '/.env');

        return $env[$variable] ?? null;
    }

    protected function getSourceOpenContent(?string $default = ''): string
    {
        return $this->IO()->ask('Source Open Content', $default);
    }

    protected function getOcClient(): OpenContentClient
    {
        // Extract Environment variables
        $targetOc = $this->getEnv('OC_URL');
        $ocUser = $this->getEnv('OC_USER');
        $ocPass = $this->getEnv('OC_PASSWORD');

        if ($targetOc) {
            $this->IO()->note('We found some settings in "/.env"!');
            if ($ocUser && $ocPass && $this->IO()->confirm("Would you like to use Open content:\"{$targetOc}\" with User:\"{$ocUser}\"")) {
                $this->client->setBaseUri($targetOc);
                $this->client->setCredentials($ocUser, $ocPass);

                return $this->client;
            }
        } else {
            $this->IO()->note('OC_URL not found in "/.env"!');
        }

        $this->client->setBaseUri($this->getSourceOpenContent($targetOc));

        $credentials = $this->getCredentials($ocUser, $ocPass);

        $this->client->setCredentials(
            $credentials['user'],
            $credentials['password']
        );

        return $this->client;
    }

    protected function getCredentials(string $defaultUser = '', string $defaultPass = ''): array
    {
        $user = $this->IO()->ask('User', $defaultUser);

        // Only use default password for default user
        $passDefault = $user === $defaultUser ? $defaultPass : '';

        $password = $this->IO()->askHidden('Password') ?? $passDefault;

        return compact(['user', 'password']);
    }

    protected function filterUrl($url)
    {
        $disallowed = ['http://', 'https://'];
        foreach ($disallowed as $d) {
            if (strpos($url, $d) === 0) {
                return str_replace($d, '', $url);
            }
        }

        return $url;
    }

    protected function getSite(InputInterface $input)
    {
        return $this->filterUrl($input->getArgument('site'));
    }

    /**
     * @param Client $client
     * @param string $function
     * @param string $uuid
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    protected function requestConceptApi(Client $client, $function, $uuid)
    {
        // For now we map "Read" to "Show" for API compatibility
        if ($function === 'read') {
            $function = 'show';
        }

        $method = $function === 'show' ? 'GET' : 'POST';

        $params = [
            'action' => 'concepts',
            'route' => $function,
            'uuid' => $uuid
        ];

        $options = [
            'form_params' => $params,
            'timeout' => 5, // Response timeout
            'connect_timeout' => 5, // Connection timeout
            'verify' => false
        ];

        if ($method === 'GET') {
            $options['query'] = $params;
        } else {
            $options['form_params'] = $params;
        }

        return $client->request($method, '/ajax.php', $options);
    }

    protected function handleConceptEvent(Client $client, $eventType, $uuid): bool
    {
        $eventMap = [
            'ADD' => 'sync',
            'DELETE' => 'delete',
            'UPDATE' => 'sync'
        ];

        try {
            $response = $this->requestConceptApi($client, $eventMap[$eventType], $uuid);

            if ( ! $this->isAcceptableResponseCode($eventType, $response->getStatusCode())) {
                $body = json_decode((string)$response->getBody(), true);
                $this->addRequestError($body['message'] ?? "{$eventType} {$uuid} - responded with error code: " . $response->getStatusCode());

                return false;
            }

            return true;
        } catch (RequestException $e) {
            $context = $e->getHandlerContext();
            $message = $context['error'] ?? $e->getMessage();
            $this->addRequestError("Api:{$eventMap[$eventType]} {$uuid} caused error: {$message}");
            $this->setRequestHasError(true);
        } catch (GuzzleException $e) {
            $this->addRequestError("{$eventType} {$uuid} caused error: " . $e->getMessage());
        }

        return false;
    }

    protected function isAcceptableResponseCode(string $eventType, int $statusCode)
    {
        // Accept all codes between 200-300
        if ($statusCode >= 200 && $statusCode < 300) {
            return true;
        }

        $acceptableCodes = [
            'ADD' => [409],
            'DELETE' => [404],
            'UPDATE' => []
        ];

        return in_array($statusCode, $acceptableCodes[$eventType], true);
    }

    protected function handleConceptRequest(Client $client, $function, $uuid): array
    {
        try {
            $response = $this->requestConceptApi($client, $function, $uuid);
            $statusCode = $response->getStatusCode();
            $body = json_decode((string)$response->getBody(), true) ?? [];
            $this->setRequestHasError(! ($statusCode >= 200 && $statusCode < 300));

            if ( $this->hasRequestError()) {
                $this->setRequestHasError(true);
                $this->addApiError($body, $statusCode);
            }

            return $body;
        } catch (RequestException $e) {
            $context = $e->getHandlerContext();
            $message = $context['error'] ?? $e->getMessage();
            $this->addRequestError("Api:{$function} {$uuid} caused error: {$message}");
            $this->setRequestHasError(true);
        } catch (GuzzleException $e) {
            $this->addRequestError("Api:{$function} {$uuid} caused error: " . $e->getMessage());
            $this->setRequestHasError(true);
        }

        return [];
    }

    private function getOutputInstance(): SymfonyStyle
    {
        if ( ! $this->ioInstance instanceof SymfonyStyle) {
            throw new RuntimeException(static::class . '::output has not been properly set');
        }

        return $this->ioInstance;
    }

    protected function setupIO(InputInterface $input, OutputInterface $output): void
    {
        $this->ioInstance = new SymfonyStyle($input, $output);
    }

    protected function IO(): SymfonyStyle
    {
        if ( ! $this->ioInstance instanceof SymfonyStyle) {
            throw new RuntimeException(static::class . '::output has not been properly set');
        }

        return $this->ioInstance;
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     * @see https://symfony.com/doc/current/console/coloring.html
     *
     * @param string          $tag      Type of tag to output Available tags ['info', 'error', 'comment', 'question']
     * @param string|iterable $messages The message as an iterable of strings or a single string
     * @param int             $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is
     *                                  considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    protected function writeTag($tag, $messages, $options = 0): void
    {
        foreach ((array)$messages as $message) {
            $this->IO()->writeln("<{$tag}>{$message}</{$tag}>", $options);
        }
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|iterable $messages The message as an iterable of strings or a single string
     * @param int             $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is
     *                                  considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    protected function writeComment($messages, $options = 0): void
    {
        $this->writeTag('comment', $messages, $options);
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|iterable $messages The message as an iterable of strings or a single string
     * @param int             $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is
     *                                  considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    protected function writeInfo($messages, $options = 0): void
    {
        $this->writeTag('info', $messages, $options);
    }

    protected function getProgressBar(int $max, $format = 'standard'): ProgressBar
    {
        // Add our custom Formats
        foreach ($this->progressFormats as $name => $customFormat) {
            ProgressBar::setFormatDefinition($name, $customFormat);
        }

        $progressBar = $this->IO()->createProgressBar($max);

        if ($max > 5000) {
            // For performance reasons, we set the redraw frequency to a higher value so it updates on only some iterations.
            // We have a maximum of every 100 iterations
            $frequency = max(abs($max / 1000), 100);
            $progressBar->setRedrawFrequency($frequency);
        }

        $progressBar->setFormat($format);

        return $progressBar;
    }

    protected function addApiError(array $body, int $statusCode): void
    {
        $responseCodes = $body['responseCodes'] ?? [];
        $this->addRequestError(! empty($responseCodes) ?
            'ApiRequest responded with codes: ' . implode(', ', $responseCodes) :
            "ApiRequest responded with status code: {$statusCode}");
    }

    protected function addRequestError(string $message): void
    {
        $this->syncErrors[] = $message;
    }

    protected function getRequestErrors(): array
    {
        return $this->syncErrors;
    }

    private function setRequestHasError(bool $hasError): void
    {
        $this->requestHasError = $hasError;
    }

    protected function hasRequestError(): bool
    {
        return $this->requestHasError;
    }
}
