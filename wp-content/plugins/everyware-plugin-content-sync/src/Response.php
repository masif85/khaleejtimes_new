<?php declare(strict_types=1);

namespace Everyware\Plugin\ContentSync;

use Everyware\Plugin\ContentSync\Wordpress\Contracts\WpSendJson;

/**
 * Class Response
 * @package Everyware\Plugin\ContentSync
 */
class Response implements WpSendJson
{
    public function sendJson($response, int $status_code = null, int $options = 0): void
    {
        wp_send_json($response, $status_code, $options);
    }

    public function sendJsonError($data = null, int $status_code = null, int $options = 0): void
    {
        wp_send_json_error($data, $status_code, $options);
    }

    public function sendJsonSuccess($data = null, $status_code = null, $options = 0): void
    {
        wp_send_json_success($data, $status_code, $options);
    }
}
