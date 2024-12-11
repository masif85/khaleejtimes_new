<?php declare(strict_types=1);

namespace Everyware\Concepts\Commands;

/**
 * Class StateHandler
 * @package Everyware\Concepts\Commands
 */
class StateHandler
{
    public const STATE_FILE = 'currentState.json';
    public const CONCEPTS_FILE = 'conceptUuids.json';

    /**
     * @var LocalStorage
     */
    private $localStorage;

    /**
     * ConceptStorage constructor.
     *
     * @param LocalStorage $localStorage
     */
    public function __construct(LocalStorage $localStorage)
    {
        $this->localStorage = $localStorage;
    }

    public function cleanConceptStorage(): void
    {
        $this->storeConcepts();
    }

    public function getCurrentSite(): string
    {
        return (string)$this->getStoredState('currentSite');
    }

    public function getLastHandledEventId(): ?int
    {
        if (($id = $this->getStoredState('lastEventId')) !== null) {
            return (int)$id;
        }

        return null;
    }

    public function getStoredConcepts(): array
    {
        return $this->getFileContent(static::CONCEPTS_FILE);
    }

    public function hasStoredConcepts(): bool
    {
        return ! empty($this->getStoredConcepts());
    }

    public function hasEventId(): bool
    {
        return $this->getLastHandledEventId() !== null;
    }

    public function hasCurrentSite(): bool
    {
        return ! empty($this->getCurrentSite());
    }

    public function onCurrentSite(string $currentSite): bool
    {
        return $this->getCurrentSite() === $currentSite;
    }

    public function storeCurrentSite(string $currentSite): StateHandler
    {
        return $this->storeState('currentSite', $currentSite);
    }

    /**
     * @param $id
     *
     * @return StateHandler
     */
    public function storeLastHandledEvent($id): StateHandler
    {
        return $this->storeState('lastEventId', $id);
    }

    /**
     * Store list of uuids in local storage
     *
     * @param array $uuids
     *
     * @return StateHandler
     */
    public function storeConcepts(array $uuids = []): StateHandler
    {
        $this->localStorage->updateFile(static::CONCEPTS_FILE, ! empty($uuids) ? json_encode($uuids) : '');

        return $this;
    }

    /**
     * Retrieve current state from Local storage
     * @return array
     */
    private function currentState(): array
    {
        return $this->getFileContent(static::STATE_FILE);
    }

    /**
     * Extract the content of a file into an array
     *
     * @param $filename
     *
     * @return array
     */
    private function getFileContent($filename): array
    {
        $content = $this->localStorage->readFile($filename);

        if ( ! empty($content)) {
            return json_decode($content, true) ?? [];
        }

        return [];
    }

    /**
     * Retrieve the current state from Local storage
     * @param $state
     *
     * @return mixed|null
     */
    private function getStoredState($state)
    {
        $currentState = $this->currentState();

        return $currentState[$state] ?? null;
    }

    /**
     * Store the current state value in Local storage
     *
     * @param $state
     * @param $value
     *
     * @return StateHandler
     */
    private function storeState($state, $value): StateHandler
    {
        $currentState = $this->currentState();

        $currentState[$state] = $value;

        $this->localStorage->updateFile(static::STATE_FILE, json_encode($currentState));

        return $this;
    }
}
