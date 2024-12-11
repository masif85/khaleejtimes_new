<?php declare(strict_types=1);

namespace Everyware\Plugin\ContentSync\Wordpress;

use Everyware\Plugin\ContentSync\Exceptions\MissingAwsCredentialsException;
use Everyware\Plugin\ContentSync\Exceptions\MissingAwsRegionException;
use Exception;

class WpNotices
{
    private Exception $exception;

    public function __construct(Exception $e)
    {
        $this->exception = $e;
    }

    public function renderSiteNotice(): void
    {
        $headline = __('Oops! It seems you are missing information required for the Content Sync plugin to connect to AWS.',
            CONTENT_SYNC_LANG);
        $message = '';

        if ($this->exception instanceof MissingAwsCredentialsException) {
            $message = __('You are missing IAM user credential in your environment.', CONTENT_SYNC_LANG);
        }

        if ($this->exception instanceof MissingAwsRegionException) {
            $message = __('The plugin could not determine the AWS region of this application.', CONTENT_SYNC_LANG);
        }

        $description = ! empty($message) ?
            __('The plugin will not be able to upload configuration to S3. If this is a stage or a production environment please contact Naviga Support to have this set up',
                CONTENT_SYNC_LANG) : '';

        ?>
        <div class="notice notice-warning is-dismissible">
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">Dismiss this notice.</span>
            </button>
            <p><strong><?php print $headline; ?></strong></p>
            <p><?php print $message ?></p>
            <p class="description"><?php print $description; ?></p>
        </div>
        <?php
    }
}
