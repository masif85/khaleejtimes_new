<?php declare(strict_types=1);

namespace Everyware\Concepts;

use Everyware\Concepts\Contracts\ApiResponse;
use Everyware\Concepts\Exceptions\ConceptDeleteError;
use Everyware\Concepts\Exceptions\ConceptCreateError;
use Everyware\Concepts\Exceptions\ConceptUpdateError;
use Everyware\Concepts\Exceptions\InvalidConceptData;
use Exception;
use InvalidArgumentException;

/**
 * Class AjaxResponse
 * @package Everyware\Concepts
 */
class ConceptApiResponse implements ApiResponse
{
    public const ALREADY_EXISTS = 'ALREADY_EXISTS';
    public const INTERNAL_ERROR = 'INTERNAL_ERROR';
    public const INVALID_ROUTE = 'INVALID_ROUTE';
    public const MOVED = 'MOVED';
    public const NOT_FOUND_IN_WP = 'NOT_FOUND_IN_WP';
    public const NOT_FOUND_IN_SOURCE = 'NOT_FOUND_IN_SOURCE';
    public const PARENT_CREATED = 'PARENT_CREATED';
    public const PARENT_NOT_CREATED = 'PARENT_NOT_CREATED';
    public const PARENT_NOT_FOUND_IN_WP = 'PARENT_NOT_FOUND_IN_WP';
    public const PARENT_NOT_FOUND_IN_SOURCE = 'PARENT_NOT_FOUND_IN_SOURCE';

    private static $validResponseCodes = [
        'ALREADY_EXISTS',
        'INTERNAL_ERROR',
        'INVALID_ROUTE',
        'MOVED',
        'NOT_FOUND_IN_WP',
        'NOT_FOUND_IN_SOURCE',
        'PARENT_CREATED',
        'PARENT_NOT_CREATED',
        'PARENT_NOT_FOUND_IN_WP',
        'PARENT_NOT_FOUND_IN_SOURCE'
    ];

    private $response = [];
    /**
     * @var int
     */
    private $statusCode;

    public function __construct(array $response, int $statusCode)
    {
        $this->response = $response;
        $this->statusCode = $statusCode;
    }

    public static function alreadyExists(array $responseCodes = []): ConceptApiResponse
    {
        return static::createWithResponseCode(static::ALREADY_EXISTS, 409)->addResponseCodes($responseCodes);
    }

    public static function routeNotFound($route): ConceptApiResponse
    {
        return static::createWithResponseCode(static::INVALID_ROUTE, 400);
    }

    public static function notFoundInDb(array $responseCodes = []): ConceptApiResponse
    {
        return static::createWithResponseCode(static::NOT_FOUND_IN_WP, 404)->addResponseCodes($responseCodes);
    }

    public static function notFoundInOC(array $responseCodes = []): ConceptApiResponse
    {
        return static::createWithResponseCode(static::NOT_FOUND_IN_SOURCE, 424)->addResponseCodes($responseCodes);
    }

    public static function internalError(Exception $e): ConceptApiResponse
    {
        return static::createWithResponseCode(static::INTERNAL_ERROR, 500);
    }

    /**
     * Determine if an exception is meant for public use
     *
     * @param Exception $e
     *
     * @return bool
     */
    public static function isPublicException(Exception $e): bool
    {
        return $e instanceof ConceptCreateError ||
            $e instanceof ConceptDeleteError ||
            $e instanceof ConceptUpdateError ||
            $e instanceof InvalidConceptData;
    }

    // Success responses
    // ======================================================

    public static function creationSuccess(array $responseCodes = []): ConceptApiResponse
    {
        return (new static([], 201))->addResponseCodes($responseCodes);
    }

    public static function deletionSuccess(array $responseCodes = []): ConceptApiResponse
    {
        return (new static([], 200))->addResponseCodes($responseCodes);
    }

    public static function updateSuccess(array $responseCodes = []): ConceptApiResponse
    {
        return (new static([], 200))->addResponseCodes($responseCodes);
    }

    public static function success(array $data = [], $statusCode = 200): ConceptApiResponse
    {
        return new static([
            'responseCodes' => [],
            'data' => $data
        ], $statusCode);
    }

    public static function error(array $data = [], $statusCode = 400): ConceptApiResponse
    {
        return new static([
            'responseCodes' => [],
            'data' => $data
        ], $statusCode);
    }

    public static function createWithResponseCode(string $responseCode, int $statusCode): ConceptApiResponse
    {
        static::validateResponseCode($responseCode);

        return new static(['responseCodes' => (array)$responseCode], $statusCode);
    }

    public function addResponseCode(string $code): ConceptApiResponse
    {
        static::validateResponseCode($code);

        if ( ! isset($this->response['responseCodes'])) {
            $this->response['responseCodes'] = [];
        }

        if ( ! in_array($code, $this->response['responseCodes'], true)) {
            $this->response['responseCodes'][] = $code;
        }

        return $this;
    }

    public function addResponseCodes(iterable $codes): ConceptApiResponse
    {
        foreach ($codes as $code) {
            $this->addResponseCode($code);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * @param array $response
     *
     * @return void
     */
    public function setResponse(array $response): void
    {
        $this->response = $response;
    }

    /**
     * Send json response
     */
    public function send(): void
    {
        wp_send_json($this->response, $this->statusCode);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    private static function validateResponseCode($code): void
    {
        if ( ! in_array($code, static::$validResponseCodes, true)) {
            throw new InvalidArgumentException("Invalid responseCode:\"{$code}\"");
        }
    }
}
