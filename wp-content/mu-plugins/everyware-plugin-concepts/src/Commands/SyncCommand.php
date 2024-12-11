<?php declare(strict_types=1);

namespace Everyware\Concepts\Commands;

use GuzzleHttp\Client;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class SyncCommand
 * @package Everyware\Concepts\Commands
 */
class SyncCommand extends BaseCommand
{
    public function configure(): void
    {
        $this->setName('sync')
            ->setDescription('Use the event log from a chosen Open Content to sync a site. Can be used for local, stage or even production sites.')
            ->addArgument('site', InputArgument::REQUIRED, static::$descriptions['site']);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupIO($input, $output);

        $io = new SymfonyStyle($input, $output);

        $currentSite = $this->getSite($input);

        //  First check for ongoing bootstrap process
        if ($this->continueOngoingBootstrapProcess($currentSite, $this->state, $io)) {
            $io->newLine();
            $this->writeInfo('Run the "bootstrap" command instead.');
            exit;
        } elseif ( $this->state->onCurrentSite($currentSite) && $this->state->hasStoredConcepts()) {
            $this->state->storeLastHandledEvent(0);
        }

        // Reset local storage
        $this->state->cleanConceptStorage();

        // Reset event from local storage for other sites
        if ( ! $this->state->onCurrentSite($currentSite)) {
            $this->state->storeLastHandledEvent(0);
            $this->state->storeCurrentSite($currentSite);
        }

        $this->startProgress(new Client([
            'base_uri' => "https://{$currentSite}"
        ]), $this->state, $io);
    }

    public function startProgress(Client $siteClient, StateHandler $storage, SymfonyStyle $io)
    {
        $lastHandledEvent = (int)$this->state->getLastHandledEventId();
        $currentSite = $storage->getCurrentSite();
        $client = $this->getOcClient();
        $currentOc = $client->getBaseUri();
        $events = $client->events($lastHandledEvent);

        $io->section("Synchronizing concepts from \"{$currentOc}\" to: https://{$currentSite}");

        if (empty($events)) {
            $io->success('You are already in sync!');
            exit;
        }

        if ($lastHandledEvent === 0) {
            $lastHandledEvent = $client->firstEventId();
        }

        $eventsCount = max($client->lastEventId() - $lastHandledEvent, 100) / 100;
        $handledEvents = 0;
        $progressBar = $this->getProgressBar((int)$eventsCount);
        $progressBar->setMessage("Events handled {$handledEvents},");
        $progressBar->start();

        $maxErrors = 10;

        while ( ! empty($events)) {

            foreach ($events as $event) {
                $lastHandledEvent = $event['id'];
                ++$handledEvents;

                if ($this->getEventContentType($event) !== 'Concept') {
                    continue;
                }

                if ($this->handleConceptEvent($siteClient, $event['eventType'], $event['uuid'])) {

                    // Store every successfully handled Concept event
                    $this->state->storeLastHandledEvent($lastHandledEvent);
                }

                if (count($this->syncErrors) > $maxErrors) {
                    $io->newLine();
                    $io->error('Progress paused because of to many errors:');
                    $io->writeln('Registered Errors:');
                    $io->listing($this->syncErrors);

                    // Add option to exit progress or clear errors
                    if ( ! $io->confirm('Clear errors and continue?')) {

                        // Store event before exiting
                        $this->state->storeLastHandledEvent($lastHandledEvent);
                        exit;
                    }

                    $this->syncErrors = [];
                    $io->newLine();
                    $progressBar->display();
                }
                $progressBar->setMessage("Events handled {$handledEvents},");
            }

            // Store event before fetching new batch of events
            $this->state->storeLastHandledEvent($lastHandledEvent);
            $events = $client->events($lastHandledEvent);

            $progressBar->advance();
        }

        $progressBar->finish();
        $io->newLine();

        if ( ! empty($this->syncErrors)) {
            $io->newLine();
            $io->title('Some errors occurred while Synchronizing:');
            $io->writeln('Registered Errors:');
            $io->listing($this->syncErrors);
        }
    }

    protected function getEventContentType(array $event)
    {
        return $event['content']['contentType'] ?? null;
    }
}
