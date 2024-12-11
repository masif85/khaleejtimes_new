<?php declare(strict_types=1);

namespace Everyware\Concepts\Commands;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class BootstrapCommand
 * @package Everyware\Concepts\Commands
 */
class BootstrapCommand extends BaseCommand
{
    public function configure(): void
    {
        $this->setName('bootstrap')
            ->setDescription('Bootstrap a site with concepts from a chosen Open Content. Can be used for local, stage or even production sites.')
            ->addArgument('site', InputArgument::REQUIRED, static::$descriptions['site']);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupIO($input, $output);

        $io = new SymfonyStyle($input, $output);

        $currentSite = $this->getSite($input);

        if ( ! $this->continueOngoingBootstrapProcess($currentSite, $this->state, $io)) {
            $this->state->storeCurrentSite($currentSite);
            $this->state->cleanConceptStorage();

            $client = $this->getOcClient();

            // Store last event before fetching all Concepts to make sure we can continue synchronizing afterwards
            $this->state->storeLastHandledEvent($client->lastEventId());
            $this->state->storeConcepts($client->fetchAll());
        }

        $this->startProgress(new Client([
            'base_uri' => "https://{$currentSite}"
        ]), $this->state, $io);
    }

    public function startProgress(Client $siteClient, StateHandler $storage, SymfonyStyle $io): void
    {
        $currentSite = $storage->getCurrentSite();
        $concepts = $storage->getStoredConcepts();
        $conceptsToAdd = count($concepts);

        $io->section("Adding \"{$conceptsToAdd}\" Concepts to: https://{$currentSite}");

        $progressBar = $this->getProgressBar($conceptsToAdd);
        $progressBar->setMessage('Adding Concepts:');
        $progressBar->start();

        $maxErrors = 10;
        $saveTimer = time();
        $timeToSave = 10;

        //Iterate stored Concept uuids and try to add them
        for ($i = 1; $i <= $conceptsToAdd; $i++) {
            $currentConcept = array_shift($concepts);

            // Remove from store and advance if success or already exists
            if ($this->handleConceptEvent($siteClient, 'ADD', $currentConcept)) {
                $progressBar->advance();
            } else {
                // Put it back to the end of the list again if error occurred
                $concepts[] = $currentConcept;
            }

            $now = time();

            if (($now - $saveTimer) > $timeToSave) {
                $storage->storeConcepts($concepts);
                $saveTimer = $now;
            }

            if (count($this->syncErrors) > $maxErrors) {
                $io->newLine();
                $io->error('Progress paused because of to many errors:');
                $io->writeln('Registered Errors:');
                $io->listing($this->syncErrors);

                // Add option to exit progress or clear errors
                if ( ! $io->confirm('Clear errors and continue?')) {

                    // Store Concepts before exiting
                    $storage->storeConcepts($concepts);
                    exit;
                }

                $this->syncErrors = [];
                $io->newLine();
                $progressBar->display();
            }
        }

        // Store the rest ones finished
        $storage->storeConcepts($concepts);

        $progressBar->finish();
        $io->newLine();

        if ( ! empty($this->syncErrors)) {
            $io->newLine();
            $io->title('Some errors occurred while bootstrapping:');
            $io->writeln('Registered Errors:');
            $io->listing($this->syncErrors);
        }
    }
}
