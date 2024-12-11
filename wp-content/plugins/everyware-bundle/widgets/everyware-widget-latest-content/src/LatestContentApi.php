<?php declare(strict_types=1);

namespace Everyware\Widget\LatestContent;

use Infomaker\Everyware\Twig\View;
use OcAPI;
use OcArticle;

/**
 * Class LatestContentApi
 */
class LatestContentApi
{
  private const LIMIT = 16;
  public const SORT_LATEST = 'latest';
  public const SORT_OLDEST = 'oldest';
  public const SORT_TITLE_ASC = 'title_asc';
  public const SORT_TITLE_DESC = 'title_desc';

  /**
   * @var OcAPI
   */
  private $ocApi;

  /**
   * @var string
   */
  private $template = 'notice-teaser-with-image.php';

  public function __construct()
  {
    $this->ocApi = new OcAPI();
  }

  /**
   * Initialize and return Sitemap.
   *
   * @return self
   */
  public static function init(): self
  {
    static $inst = null;
    if ($inst === null) {
      $inst = new self();
    }

    return $inst;
  }
  
  /**
   * Render content from ajax request
   *
   * @param array $request
   * @return void
   */
  public function renderContentFromRequest(array $request): void
  {
    $section = $request['section'] ?? null;
    $articles = $this->fetchLatestContent(
      array_merge([
        'CustomerContentSubType' => $request['content_type'] ?? 'Article',
        'Section' => $section ?: '*',
      ], $this->getSortOrder($request['sort'] ?? null))
    );

    wp_send_json([
      'html' => View::generate('@ew-latest-content/articles', [
        'articles' => $this->generateFromTemplate($articles)
      ])
    ]);
    wp_die(); 
  }

  /**
   * Render initial content from widget settings
   *
   * @param array $params
   * @return array
   */
  public function renderContent(array $params): array
  {
    $articles = $this->fetchLatestContent(
      array_merge($params, $this->getSortOrder())
    );

    return $this->generateFromTemplate($articles);
  }

  /**
   * Get list of subsections for content type
   *
   * @param string $contentType
   * @return array
   */
  public function getSubSections(string $contentType): array
  {
    $this->ocApi->prepare_suggest_query([
      'oc_suggest_field' => 'Section',
      'oc_query' => 'CustomerContentSubType:' . $contentType
    ]);

    $result = $this->ocApi->get_oc_suggest();
    
    return array_map(static function ($section) {
      return $section->name;
    },  $result);
  }

  /**
   * Fetch latest content from OC
   *
   * @param array $params
   * @return array
   */
  public function fetchLatestContent(array $params): array
  {   
    $params = array_merge($params, [
      'contenttypes' => ['Article'],
      'limit' => self::LIMIT
    ]);

    $result = $this->ocApi->search($params, true);

    unset($result['facet'], $result['hits'], $result['duration']);

    return $result;
  }

  /**
   * Get sort order fields for OC
   *
   * @param string $sort
   * @return array
   */
  private function getSortOrder(string $sort = self::SORT_LATEST): array
  {
    switch ($sort) {
      case self::SORT_LATEST:
        return [
          'sort.indexfield' => 'Pubdate',
          'sort.Pubdate.ascending' => 'false'
        ];
      case self::SORT_OLDEST:
        return [
          'sort.indexfield' => 'Pubdate',
          'sort.Pubdate.ascending' => 'true'
        ];
      case self::SORT_TITLE_DESC:
        return [
          'sort.indexfield' => 'Headline',
          'sort.Headline.ascending' => 'false'
        ];
      case self::SORT_TITLE_ASC:
        return [
          'sort.indexfield' => 'Headline',
          'sort.Headline.ascending' => 'true'
        ];
      default:
        return [
          'sort.indexfield' => 'Pubdate',
          'sort.Pubdate.ascending' => 'false'
        ];
    }
  }

  /**
   * Run the article through the template to generate HTML
   *
   * @param OcArticle[] $ocArticles
   * @param string      $template
   *
   * @return array
   */
  private function generateFromTemplate(array $ocArticles): array
  {
    $template = locate_template([$this->template, "/templates/{$this->template}"]);

    $articles = [];
    ob_start();
    foreach ($ocArticles as $article) {
      $templateFile = $template;
      include $templateFile;
      $articles[] = ob_get_contents();
      ob_clean();
    }
    ob_end_clean();

    return $articles;
  }
}
