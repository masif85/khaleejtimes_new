<?php declare(strict_types=1);

namespace Everyware\Concepts\Admin;

use Everyware\Concepts\ConceptDiffProvider;
use Everyware\Concepts\ConceptDuplicatesProvider;
use Everyware\Concepts\ConceptPost;
use Everyware\Concepts\Concepts;
use Exception;
use Infomaker\Everyware\Twig\View;
use InvalidArgumentException;

/**
 * Class DuplicatesPage
 * @package Everyware\Concepts\Admin
 */
class DuplicatesPage extends SubPage
{
    /**
     * @var array
     */
    private $duplicates;

    /**
     * @var ConceptDuplicatesProvider
     */
    private $duplicatesProvider;

    public function __construct(ConceptDuplicatesProvider $duplicatesProvider)
    {
        $this->duplicatesProvider = $duplicatesProvider;

        add_action('wp_ajax_concepts_remove_post', [&$this, 'ajaxRemovePost']);
    }

    public function formContent(array $viewData): void
    {
        View::render('@conceptsPlugin/duplicates-page', array_replace($viewData, [
            'duplicates' => $this->getDuplicates()
        ]));
    }

    public function pageTitle(): string
    {
        return __('Concept duplicates', CONCEPTS_TEXT_DOMAIN);
    }

    public function getUserAccess(): string
    {
        return 'administrator';
    }

    public function getLocalTranslations(): array
    {
        return [
            'refreshButtonLabel' => __('Refresh', CONCEPTS_TEXT_DOMAIN),
            'resultLabel' => __('Duplicates found', CONCEPTS_TEXT_DOMAIN),
            'duplicatePostCountTooltip' => __('Number of wordpress posts with this uuid', CONCEPTS_TEXT_DOMAIN),
            'postEditTooltip' => __('Edit post', CONCEPTS_TEXT_DOMAIN),
            'postRemoveTooltip' => __('Remove post', CONCEPTS_TEXT_DOMAIN),
            'refreshButtonDescription' => __('Compare the concepts in Wordpress to find posts with duplicate uuids.',
                CONCEPTS_TEXT_DOMAIN),
            'postLabels' => [
                'permalink' => __('Permalink', CONCEPTS_TEXT_DOMAIN),
            ]
        ];
    }

    public function ajaxRemovePost($postId = null): void
    {
        $postId = (int)($_POST['id'] ?? $postId);

        try {
            if ($postId === null) {
                throw new InvalidArgumentException('Missing argument id');
            }

            $duplicates = $this->duplicatesProvider->getDuplicates();

            $postFound = false;

            foreach ($duplicates as $uuid => $conceptPosts) {
                foreach ($conceptPosts as $postPosition => $post) {
                    if ( ! $post instanceof ConceptPost) {
                        continue;
                    }

                    if ($post->id === $postId) {
                        $postFound = true;
                        Concepts::init()->delete($post);
                        unset($duplicates[$uuid][$postPosition]);
                        break;
                    }
                }
            }

            if ( ! $postFound) {
                throw new InvalidArgumentException("Could not find post with id: {$postId} in store.");
            }

            $this->duplicatesProvider->store(array_filter($duplicates));

            wp_send_json_success(['message' => "Post with id: {$postId} was successfully removed from Wordpress"]);

        } catch (Exception $e) {
            wp_send_json(['message' => $e->getMessage()], 400);
        }
    }

    protected function preFormRender(): void
    {
        $this->duplicates = $this->duplicatesProvider->getDuplicates(isset($_POST['refresh']));
    }

    private function getDuplicates(): array
    {
        return $this->duplicates ?? [];
    }
}
