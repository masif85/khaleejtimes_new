<?php declare(strict_types=1);

namespace Everyware\Everyboard\Widgets;

use Everyware\Everyboard\OcApiResponse;
use Everyware\Everyboard\OcArticleProvider;
use Everyware\Everyboard\OcListAdapter;
use Everyware\Everyboard\OcValidator;
use InvalidArgumentException;

/**
 * Class ClientValidator
 * @package Everyware\Everyboard
 */
class ClientValidator
{
    /**
     * @var OcValidator
     */
    private $validator;

    public function __construct(OcValidator $validator)
    {
        $this->validator = $validator;
    }

    public static function getRequest(): array
    {
        return stripslashes_deep($_REQUEST);
    }

    public static function getInput(string $field, $default = null)
    {
        $request = static::getRequest();

        return $request[$field] ?? $default;
    }

    public function validateOcList(string $listUuid, OcListAdapter $adapter): OcApiResponse
    {
        if (empty($listUuid)) {
            return $this->sendWarning(__('No data source connected', 'everyboard'));
        }

        if ( ! $this->validator->validateList($listUuid, $adapter)) {
            return $this->sendWarning(
                $this->validator->getValidationMessages()[0] ?? __('Failed to validate List', 'everyboard')
            );
        }

        $count = $this->validator->getArticleCount();

        return $this->sendOk(sprintf(__('%d articles', 'everyboard'), $count));
    }

    public function validateQuery(array $data, OcArticleProvider $provider): OcApiResponse
    {
        $query = $data['query'] ?? '';
        $limit = (int)($data['limit'] ?? 0);
        $start = (int)($data['start'] ?? 0);
        try {
            if (empty($query)) {
                throw new InvalidArgumentException(__('Missing query', 'everyboard'));
            }

            if ($limit <= 0) {
                throw new InvalidArgumentException(__('Invalid limit', 'everyboard'));
            }

            if ( ! $this->validator->validateOcQuery($query, $provider)) {
                return $this->sendWarning(
                    $this->validator->getValidationMessages()[0] ?? __('Failed to validate Query', 'everyboard')
                );
            }

            $count = max(($this->validator->getArticleCount() - $start), 0);

        } catch (InvalidArgumentException $e) {
            return $this->sendWarning($e->getMessage());
        }

        if ($count === 0) {
            return $this->sendWarning(__('No articles found', 'everyboard'));
        }

        return $this->sendOk(
            sprintf(__('%d articles', 'everyboard'), min($count, $limit))
        );
    }

    public static function validateClientData(): void
    {
        $type = static::getInput('type', '');
        $data = static::getInput('data', []);
        $validator = new static(new OcValidator());
        switch ($type) {
            case 'list':
                $response = $validator->validateOcList($data['uuid'] ?? '', new OcListAdapter());
                break;
            case 'query':
                $response = $validator->validateQuery($data, OcArticleProvider::create());
                break;
            default:
                $response = $validator->sendError(sprintf(__('Can not validate type: %s', 'everyboard'), $type));

        }

        $validator->send($response);
    }

    public function send(OcApiResponse $response): void
    {
        wp_send_json($response->toArray());
    }

    public function sendError(string $message): OcApiResponse
    {
        return new OcApiResponse(OcApiResponse::RESPONSE_TYPE_ERROR, $message);
    }

    public function sendOk(string $message): OcApiResponse
    {
        return new OcApiResponse(OcApiResponse::RESPONSE_TYPE_OK, $message);
    }

    public function sendWarning(string $message): OcApiResponse
    {
        return new OcApiResponse(OcApiResponse::RESPONSE_TYPE_WARNING, $message);
    }
}
