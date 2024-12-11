<?php declare(strict_types=1);

namespace Everyware\Plugin\ContentSync\Wordpress\Contracts;

/**
 * Interface WpSendJson
 * @package Everyware\Plugin\ContentSync\Wordpress\Contracts
 */
interface WpSendJson
{

    /**
     * Send a JSON response back to an Ajax request.
     *
     * @param mixed    $response    Variable (usually an array or object) to encode as JSON,
     *                              then print and die.
     * @param int|null $status_code Optional. The HTTP status code to output. Default null.
     * @param int      $options     Optional. Options to be passed to json_encode(). Default 0.
     *
     * @since 3.5.0
     * @since 4.7.0 The `$status_code` parameter was added.
     * @since 5.6.0 The `$options` parameter was added.
     */
    public function sendJson($response, int $status_code = null, int $options = 0): void;

    /**
     * Send a JSON response back to an Ajax request, indicating failure.
     *
     * If the `$data` parameter is a WP_Error object, the errors
     * within the object are processed and output as an array of error
     * codes and corresponding messages. All other types are output
     * without further processing.
     *
     * @param mixed    $data        Optional. Data to encode as JSON, then print and die. Default null.
     * @param int|null $status_code Optional. The HTTP status code to output. Default null.
     * @param int      $options     Optional. Options to be passed to json_encode(). Default 0.
     *
     * @since 5.6.0 The `$options` parameter was added.
     *
     * @since 3.5.0
     * @since 4.1.0 The `$data` parameter is now processed if a WP_Error object is passed in.
     * @since 4.7.0 The `$status_code` parameter was added.
     */
    public function sendJsonError($data = null, int $status_code = null, int $options = 0): void;

    /**
     * Send a JSON response back to an Ajax request, indicating success.
     *
     * @param mixed    $data        Optional. Data to encode as JSON, then print and die. Default null.
     * @param int|null $status_code Optional. The HTTP status code to output. Default null.
     * @param int      $options     Optional. Options to be passed to json_encode(). Default 0.
     *
     * @since 3.5.0
     * @since 4.7.0 The `$status_code` parameter was added.
     * @since 5.6.0 The `$options` parameter was added.
     */
    public function sendJsonSuccess($data = null, int $status_code = null, int $options = 0): void;
}
