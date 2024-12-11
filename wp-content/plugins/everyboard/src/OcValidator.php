<?php declare(strict_types=1);

namespace Everyware\Everyboard;

use Exception;

/**
 * Class OcValidator
 * @package Everyware\Everyboard
 */
class OcValidator
{
    private $messages = [];

    private $articleCount = 0;

    public function getValidationMessages(): array
    {
        return $this->messages;
    }

    public function validateList($uuid, OcListAdapter $adapter): bool
    {
        try {
            $uuids = $adapter->get_article_uuids_or_fail($uuid);
            $this->articleCount = count($uuids);
        } catch (Exception $e) {
            $this->addValidationMessage($e->getMessage());
        }

        if( empty($uuids) ) {
            $this->addValidationMessage(__('No articles found in List', 'everyboard'));
        }

        return $this->isValid();
    }

    public function validateOcQuery(string $query, OcArticleProvider $provider): bool
    {
        if (empty($query)) {
            $this->addValidationMessage(__('Missing query', 'everyboard'));
            return false;
        }

        try {
            $this->articleCount = $provider->testQuery($query);

            if( $this->articleCount === 0 ) {
                $this->addValidationMessage(__("No articles match this query (\"{$query}\")", 'everyboard'));
            }

        } catch (Exception $e) {
            $this->addValidationMessage($e->getMessage());
        }

        return $this->isValid();
    }

    public function getArticleCount(): int
    {
        return $this->articleCount;
    }

    public function clearMessages(): void
    {
        $this->messages = [];
    }

    public function validationFailed(): bool
    {
        return ! $this->isValid();
    }

    public function isValid(): bool
    {
        return empty($this->getValidationMessages());
    }

    private function addValidationMessage(string $message): void
    {
        if( ! empty ($message) ) {
            $this->messages[] = $message;
        }
    }
}
