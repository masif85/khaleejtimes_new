<?php declare(strict_types=1);

namespace Everyware\Concepts\Commands;

use GuzzleHttp\Client;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class AddCommand
 * @package Everyware\Concepts\Commands
 */
class ApiCommand extends BaseCommand
{
    private static $validRoutes = ['create', 'read', 'update', 'delete', 'sync'];

    public function configure(): void
    {
        static::$descriptions['uuid'] = 'One or a comma separated list of uuids you would like to handle';
        static::$descriptions['route'] = 'The route functions to request';

        $this->setName('api')
            ->setDescription('Request a sites concept API with one of our create|read|update|delete (CRUD) functions or the more general "sync". Can be used for local, stage or even production sites.')
            ->addArgument('site', InputArgument::REQUIRED, static::$descriptions['site'])
            ->addArgument('route', InputArgument::OPTIONAL, static::$descriptions['route'])
            ->addArgument('uuid', InputArgument::OPTIONAL, static::$descriptions['uuid']);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupIO($input, $output);

        $io = new SymfonyStyle($input, $output);

        $currentSite = $this->getSite($input);
        $uuids = $this->getUuids($input, $io);
        $route = $this->getRoute($input, $io);

        $io->section($this->getSectionHeader($route, $uuids, $currentSite));
        $siteClient = new Client([
            'base_uri' => 'https://' . $this->getSite($input)
        ]);

        foreach ($uuids as $uuid) {
            $response = $this->handleConceptRequest($siteClient, $route, $uuid);
            if ( ! $this->hasRequestError()) {
            $block = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                $io->success(! empty($block) ? $block : '');
            } else {
                $io->warning($this->getRequestErrors());
            }
        }
    }

    private function getUuids(InputInterface $input, SymfonyStyle $io): array
    {
        if ( ! ($uuid = $input->getArgument('uuid'))) {
            $uuid = $io->ask('Uuid to handle');
        }

        // Then split on comma and remove empty values
        return array_filter(preg_split('/\s*(,)\s*/', $uuid));
    }

    private function getRoute(InputInterface $input, SymfonyStyle $io)
    {
        $route = $input->getArgument('route');
        if ( ! in_array($route, static::$validRoutes, true)) {
            if ($route !== null) {
                $io->warning("\"{$route}\" is not a valid api function");
            }
            $route = $io->choice('Which route function would you like to request?', static::$validRoutes,
                'read');
        }

        return $route;
    }

    private function getSectionHeader($route, $items, $site)
    {
        $itemsToHandle = count($items);
        $firstItem = $items[0];
        $sections = [
            'create' => "Add Concept:\"{$firstItem}\" to: https://{$site}",
            'create_multi' => "Add \"{$itemsToHandle}\" Concepts to: https://{$site}",
            'delete' => "Remove Concept:\"{$firstItem}\" from: https://{$site}",
            'delete_multi' => "Remove \"{$itemsToHandle}\" Concepts from: https://{$site}",
            'read' => "Get Concept:\"{$firstItem}\" from: https://{$site}",
            'read_multi' => "Get \"{$itemsToHandle}\" Concepts from: https://{$site}",
            'update' => "Update Concept:\"{$firstItem}\" on: https://{$site}",
            'update_multi' => "Update \"{$itemsToHandle}\" Concepts on: https://{$site}",
            'sync' => "Synchronize Concept:\"{$firstItem}\" to: https://{$site}",
            'sync_multi' => "Synchronize \"{$itemsToHandle}\" Concepts to: https://{$site}",
        ];

        return $sections[$itemsToHandle > 1 ? "{$route}_multi" : $route];
    }
}
