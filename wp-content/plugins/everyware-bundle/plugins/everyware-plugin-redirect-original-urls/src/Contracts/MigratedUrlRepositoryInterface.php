<?php declare(strict_types=1);


namespace Everyware\Plugin\RedirectOriginalUrls\Contracts;


/**
 * Interface MigratedUrlRepositoryInterface
 * @package Everyware\Plugin\RedirectOriginalUrls\Contracts
 */
interface MigratedUrlRepositoryInterface
{
    /**
     * @param string $oldUrl
     *
     * @return string|null
     */
    public function findNewUrl(string $oldUrl): ?string;
}
