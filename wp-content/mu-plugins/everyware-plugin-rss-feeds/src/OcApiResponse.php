<?php declare(strict_types=1);

namespace Everyware\RssFeeds;

use InvalidArgumentException;

/**
 * Class OcApiResponse
 */
class OcApiResponse
{
    public const RESPONSE_TYPE_OK = 'OK';

    public const RESPONSE_TYPE_WARNING = 'WARNING';

    public const RESPONSE_TYPE_ERROR = 'ERROR';

    /**
     * @var string Response code, for computer handling.
     */
    private $responseType;

    /**
     * @var string Similar to response code, but for presentation to humans.
     */
    private $responseSubject;

    /**
     * @var string Message
     */
    private $response;

    /**
     * OcApiResponse constructor.
     *
     * @param string $responseType One of the self::RESPONSE_TYPE_* constants
     * @param string $response
     */
    public function __construct(string $responseType, string $response)
    {
        $validResponseTypes = [
            self::RESPONSE_TYPE_OK      => 'Result',
            self::RESPONSE_TYPE_WARNING => 'Warning',
            self::RESPONSE_TYPE_ERROR   => 'Error'
        ];
        if (!isset($validResponseTypes[$responseType])) {
            throw new InvalidArgumentException('Invalid response type `' . $responseType . '`');
        }

        $this->responseType = $responseType;
        $this->responseSubject = $validResponseTypes[$responseType];
        $this->response = $response;
    }

    public function toArray(): array
    {
        return [
            'responseType'    => $this->responseType,
            'responseSubject' => $this->responseSubject,
            'response'        => $this->response
        ];
    }
}
