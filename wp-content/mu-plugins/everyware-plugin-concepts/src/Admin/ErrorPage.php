<?php declare(strict_types=1);

namespace Everyware\Concepts\Admin;

use Everyware\Concepts\ConceptDiffProvider;
use Exception;
use Infomaker\Everyware\Support\Date;
use Infomaker\Everyware\Support\Str;
use Infomaker\Everyware\Twig\View;
use InvalidArgumentException;

/**
 * Class SettingsPage
 * @package Everyware\Concepts\Admin
 */
class ErrorPage extends SubPage
{
    /**
     * @var ConceptDiffProvider
     */
    private $diffProvider;

    /**
     * @var string
     */
    private $activeOrderBy;

    /**
     * @var string
     */
    private $activeOrder;

    /**
     * @var array
     */
    private $lastEvent;

    /**
     * @var string
     */
    private $paged;

    /**
     * @var int
     */
    private $pageLimit = 40;

    /**
     * @var array
     */
    private $diffData;

    /**
     * @var bool
     */
    private $eventLogHealthy;

    public function __construct(ConceptDiffProvider $diffProvider)
    {
        $this->diffProvider = $diffProvider;

        add_action('wp_ajax_concepts_settings_sync', [&$this, 'ajaxSync']);
    }

    public function pageTitle(): string
    {
        return __('Errors', CONCEPTS_TEXT_DOMAIN);
    }

    public function formContent(array $viewData): void
    {
        // Check health if last event is over 6h old
        $eventLogHealthy = $this->eventLogHealthy();

        $diff = $this->getDiffData();
        unset($viewData['currentQueryParams']['paged']);
        $diffCount = count($diff['diff']);
        $pageStart = ($this->paged - 1) * $this->pageLimit;

        View::render('@conceptsPlugin/error-page', array_replace($viewData, [
            'activeOrderBy' => $this->activeOrderBy,
            'activeOrder' => $this->activeOrder,
            'diff' => array_replace([
                'total' => $diffCount,
                'limit' => $this->pageLimit,
                'page' => $this->paged
            ], $diff),
            'diffPage' => [
                'concepts' => $this->getDiffPage($this->orderDiff($diff['diff'])),
                'start' => $pageStart + 1,
                'end' => $pageStart + min($diffCount - $pageStart, $this->pageLimit),
            ],
            'eventLogHealth' => [
                'label' => __('Event log health', CONCEPTS_TEXT_DOMAIN),
                'value' => $eventLogHealthy,
                'statusText' => $eventLogHealthy ? __('Healthy', CONCEPTS_TEXT_DOMAIN) : __('Unhealthy',
                    CONCEPTS_TEXT_DOMAIN),
                'description' => __('The "event log" is used to synchronise concepts to the site.',
                    CONCEPTS_TEXT_DOMAIN)
            ],
            'settingsInfo' => $this->getSettingsInfo($diff),
        ]));
    }

    protected function dashboardNotices(): void
    {
        if ( ! $this->eventLogHealthy()) {
            View::render('@conceptsPlugin/eventlog-notice', [
                'slug' => Str::slug($this->pageTitle()),
                'noticeType' => 'error',
                'headline' => __('EventLog is unhealthy!', CONCEPTS_TEXT_DOMAIN),
                'message' => __("We're not able to find the last changes in your <strong>Open Content</strong> eventlog. We rely on those events to keep your sites concepts in sync.",
                    CONCEPTS_TEXT_DOMAIN),
            ]);
        }
    }

    public function getUserAccess(): string
    {
        return 'administrator';
    }

    public function getLocalTranslations(): array
    {
        return [
            'refreshButtonLabel' => __('Refresh', CONCEPTS_TEXT_DOMAIN),
            'refreshButtonDescription' => __('Compare the concepts in Open Content with the ones stored in Wordpress.',
                CONCEPTS_TEXT_DOMAIN),
            'settingsPage' => [
                'filterDescription' => __('Search concept', CONCEPTS_TEXT_DOMAIN),
                'NoDiff' => __('You are up-to-date!', CONCEPTS_TEXT_DOMAIN),
                'pagination' => [
                    'submit' => __('Go to', CONCEPTS_TEXT_DOMAIN),
                    'next' => __('Next page', CONCEPTS_TEXT_DOMAIN),
                    'end' => __('Last page', CONCEPTS_TEXT_DOMAIN),
                    'prev' => __('Previous page', CONCEPTS_TEXT_DOMAIN),
                    'start' => __('First page', CONCEPTS_TEXT_DOMAIN),
                ]
            ]
        ];
    }

    protected function preFormRender(): void
    {
        $this->activeOrderBy = $_GET['orderby'] ?? null;
        $this->activeOrder = $_GET['order'] ?? null;
        $this->lastEvent = $this->diffProvider->getLastEvent();
        $this->paged = (int)($_GET['paged'] ?? '1');

        // One more check to se
        if ( ! filter_var($this->paged, FILTER_VALIDATE_INT)) {
            $this->paged = 1;
        }
    }

    public function ajaxSync()
    {
        try {
            if ( ! isset($_POST['uuid'])) {
                throw new InvalidArgumentException('Missing argument uuid');
            }

            $uuid = $_POST['uuid'];

            $diff = $this->diffProvider->getDiff();

            if ( ! array_key_exists($uuid, $diff['diff'])) {
                throw new InvalidArgumentException("Could not find concept with uuid: {$uuid} in store.");
            }

            unset($diff['diff'][$uuid]);

            $this->diffProvider->storeDiff($diff);

            wp_send_json_success();

        } catch (Exception $e) {
            wp_send_json(['message' => $e->getMessage()], 400);
        }
    }

    protected function validatePage(): bool
    {
        if ( ! isset($_GET['paged'])) {
            return parent::validatePage();
        }

        $paged = (int)($_GET['paged'] ?? '1');

        if ( ! filter_var($paged, FILTER_VALIDATE_INT)) {
            $paged = 1;
        }

        $diff = $this->getDiffData();
        $validOffset = $this->getValidPage(count($diff['diff']), $paged, $this->pageLimit);

        if ($paged === $validOffset) {
            return parent::validatePage();
        }

        $_GET['paged'] = $validOffset;

        return false;
    }

    private function orderDiff(array $conceptDiff): array
    {
        $sortMap = [
            'title' => 'title',
            'wp' => 'inWp',
            'oc' => 'inOc',
        ];

        $orderBy = $sortMap[strtolower($this->activeOrderBy ?? 'title')];
        $order = $this->activeOrder ?? 'asc';

        return collect($conceptDiff)->sortBy($orderBy, SORT_REGULAR, $order !== 'asc')->toArray();
    }

    /**
     * Check health if last event is over 6h old
     */
    private function eventLogHealthy(): bool
    {

        if (empty($this->lastEvent)) {
            $this->eventLogHealthy = false;
        }

        if ($this->eventLogHealthy === null) {
            $lastEventDate = Date::createFromOcString($this->lastEvent['created']);
            $this->eventLogHealthy = $lastEventDate->diffInHours(Date::now()) > 6 ? $this->diffProvider->checkEventHealth($this->lastEvent) : true;
        }

        return $this->eventLogHealthy;
    }

    private function getDiffData(): array
    {
        if ($this->diffData === null) {
            $this->diffData = $this->diffProvider->getDiff(isset($_POST['refresh']));
        }

        return $this->diffData;
    }

    private function getDiffPage(array $conceptDiff): array
    {
        $conceptsCount = count($conceptDiff);

        if ($conceptsCount < $this->pageLimit) {
            return $conceptDiff;
        }

        $offset = ($this->paged - 1) * $this->pageLimit;

        if ($offset > $conceptsCount) {
            $offset = max($conceptsCount - $this->pageLimit, 0);
        }

        return array_slice($conceptDiff, $offset, $this->pageLimit);
    }

    private function getSettingsInfo(array $diffData): array
    {
        return [
            [
                'label' => __('Connected Open Content', CONCEPTS_TEXT_DOMAIN),
                'value' => $this->diffProvider->getConnectedOc()
            ],
            [
                'label' => __('Last known event from Open Content occurred', CONCEPTS_TEXT_DOMAIN),
                'value' => $this->getLastKnownEventToString()
            ],
            [
                'label' => __('Last refresh occurred', CONCEPTS_TEXT_DOMAIN),
                'value' => $this->toPresentationDate(Date::createFromTimestamp($diffData['timestamp']))
            ]
        ];
    }

    private function getValidPage(int $conceptsCount, int $paged, int $pageLimit): int
    {
        if ($conceptsCount < $pageLimit || $paged <= 1) {
            return 1;
        }

        $offset = ($paged - 1) * $pageLimit;

        if ($offset >= $conceptsCount) {
            return (int)ceil($conceptsCount / $pageLimit);
        }

        return $paged;
    }

    /**
     * Get the date formatted into a translated and readable string
     *
     * @param Date $createdDate
     *
     * @return string
     */
    private function toPresentationDate(Date $createdDate): string
    {
        // Translate to language set in Wordpress
        $createdDate->locale(get_locale());

        if ($createdDate->isToday() || $createdDate->isYesterday()) {
            return $createdDate->calendar();
        }

        return $createdDate->isoFormat('llll');
    }

    private function getLastKnownEventToString()
    {
        if (empty($this->lastEvent)) {
            return sprintf('<i>%s</i>', __('Could not fetch events from Open Content.', CONCEPTS_TEXT_DOMAIN));
        }

        return $this->toPresentationDate(Date::createFromOcString($this->lastEvent['created']));
    }
}
