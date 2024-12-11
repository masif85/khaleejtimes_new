<?php

namespace Spec\Everyware\Concepts\Commands;

use Everyware\Concepts\Commands\LocalFilesystem;
use Everyware\Concepts\Commands\LocalStorage;
use Everyware\Concepts\Commands\StateHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Tested Methods:
 *
 * @method cleanConceptStorage()
 * @method getCurrentSite()
 * @method getLastHandledEventId()
 * @method getStoredConcepts()
 * @method storeCurrentSite(string $newSite)
 * @method storeLastHandledEvent(string $value)
 */
class StateHandlerSpec extends ObjectBehavior
{

    /**
     * @var LocalStorage
     */
    private $localStorage;

    private $storedConcepts = '[1,2,3,4,5,6]';

    private $conceptUuids = ['1','2','3','4','5','6'];

    private $storedState = '{"currentSite":"site.se", "lastEventId":"1"}';

    public function let(LocalStorage $localStorage): void
    {
        $this->localStorage = $localStorage;

        $this->beConstructedWith($this->localStorage);
    }

    public function it_can_clean_the_store_of_concepts(): void
    {
        $this->localStorage
            ->updateFile(StateHandler::CONCEPTS_FILE, '')
            ->shouldBeCalled();

        $this->cleanConceptStorage();
    }

    public function it_can_retrieve_stored_site(): void
    {
        $this->simulateReadFile(StateHandler::STATE_FILE, $this->storedState);

        $this->getCurrentSite()->shouldReturn('site.se');
    }

    public function it_will_return_null_if_there_is_no_stored_site(): void
    {
        $this->simulateReadFile(StateHandler::STATE_FILE, '{"lastEventId":"1"}');

        $this->getCurrentSite()->shouldReturn('');
    }

    public function it_treats_no_stored_state_as_no_stored_site(): void
    {
        $this->simulateReadFile(StateHandler::STATE_FILE, '');

        $this->getCurrentSite()->shouldReturn('');
    }

    public function it_can_retrieve_stored_eventId(): void
    {
        $this->simulateReadFile(StateHandler::STATE_FILE, $this->storedState);

        $this->getLastHandledEventId()->shouldReturn(1);
    }

    public function it_will_return_null_if_there_is_no_stored_eventId(): void
    {
        $this->simulateReadFile(StateHandler::STATE_FILE, '{"currentSite":"site.se"}');

        $this->getLastHandledEventId()->shouldReturn(null);
    }

    public function it_treats_no_stored_state_as_no_stored_eventId(): void
    {
        $this->simulateReadFile(StateHandler::STATE_FILE, '');

        $this->getLastHandledEventId()->shouldReturn(null);
    }

    public function it_can_retrieve_stored_concepts(): void
    {
        $this->simulateReadFile(StateHandler::CONCEPTS_FILE, $this->storedConcepts);

        $this->getStoredConcepts()->shouldReturn(json_decode($this->storedConcepts, true));
    }

    public function it_will_return_empty_list_if_there_are_no_stored_concepts(): void
    {
        $this->simulateReadFile(StateHandler::CONCEPTS_FILE, '');

        $this->getStoredConcepts()->shouldReturn([]);
    }

    public function it_stores_current_site_in_state_store(): void
    {
        $value = 'newsite.se';

        $this->simulateStateUpdate('currentSite', $value);

        $this->storeCurrentSite($value);
    }

    public function it_stores_event_log_ids_in_state_store(): void
    {
        $value = '4';
        $this->simulateStateUpdate('lastEventId', $value);

        $this->storeLastHandledEvent($value);
    }

    public function it_can_store_list_of_uuids_in_local_storage(): void
    {
        $this->simulateUpdateFile(StateHandler::CONCEPTS_FILE, json_encode($this->conceptUuids));

        $this->storeConcepts($this->conceptUuids);
    }

    public function it_will_store_empty_string_if_concept_list_is_empty(): void
    {
        $this->simulateUpdateFile(StateHandler::CONCEPTS_FILE, '');

        $this->storeConcepts([]);
    }

    // Simulations
    // ======================================================

    private function simulateReadFile($file, $return): void
    {
        $this->localStorage
            ->readFile($file)
            ->shouldBeCalled()
            ->willReturn($return);
    }

    private function simulateUpdateFile($file, $content): void
    {
        $this->localStorage
            ->updateFile($file, $content)
            ->shouldBeCalled();
    }

    private function simulateStateUpdate($state, $value): void
    {
        $this->simulateReadFile(StateHandler::STATE_FILE, $this->storedState);

        $storedState = json_decode($this->storedState, true);
        $storedState[$state] = $value;

        $this->simulateUpdateFile(StateHandler::STATE_FILE, json_encode($storedState));
    }
}
