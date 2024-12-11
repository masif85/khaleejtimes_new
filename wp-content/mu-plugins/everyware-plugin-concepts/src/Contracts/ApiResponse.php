<?php declare(strict_types=1);

namespace Everyware\Concepts\Contracts;

/**
 * Interface ApiResponse
 * @package Everyware\Concepts\Contracts
 */
interface ApiResponse
{
    /**
     * Send json response
     */
    public function send(): void;

    public function setStatusCode(int $message): void;

    public function setResponse(array $response): void;

    public function getResponse(): array;

    public function getStatusCode(): int;

}
