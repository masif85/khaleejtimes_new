<?php declare (strict_types = 1);

namespace KTDTheme\ViewModels;

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\Utilities;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Twig\View;
use OcApi;
use KTDTheme\Paginator;
use KTDTheme\ViewModels\Teasers\MovieTeaser;

class CinemaListingPage extends BasePage
{
  public const SUB_CONTENT_TYPE = 'CINEMALISTING';
  public const LIMIT = 6;
  public const CINEMA_LOCATIONS = '/cinema_locations.json';

  /**
   * @var OcAPI
   */
  private $ocApi;

  public function __construct(Page $page)
  {
    parent::__construct($page);

    $this->ocApi = new OcAPI();

    $this->getMovies();
    $this->setViewData('tealiumGroup', 'cinema-listing');
    $this->setViewData('date_control', self::getDateControl($page->id));
  }

  private function getMovies()
  {
    $language = $_GET['language'] ?? '';
    $pagenr = intval($_GET['pagenr'] ?? 1) ?: 1;

    $provider = OpenContentProvider::setup([
      'q' => QueryBuilder::where('contenttype', 'Article')->query('CustomerCinemaListingLang:' . ($language ?: '*'))->buildQueryString(),
      'CustomerContentSubType' => self::SUB_CONTENT_TYPE,
      'sort.indexfield' => 'Pubdate',
      'sort.Pubdate.ascending' => 'false',
      'start' => ($pagenr - 1) * self::LIMIT,
      'limit' => self::LIMIT,
      'Status' => 'usable'
    ]);
    $provider->setPropertyMap('Article');
    $movies = array_map(static function (NewsMLArticle $article) {
      return (new MovieTeaser($article))->getViewData();
    }, $provider->queryWithRequirements());

    $this->setViewData('movies', $movies);
    $this->setViewData('language', $language);
    $this->setViewData('paginator', Paginator::make($provider->hits(), self::LIMIT));
  }

  public function getLanguages(): void
  {
    $this->ocApi->prepare_suggest_query([
      'oc_suggest_field' => 'CustomerCinemaListingLang',
      'oc_query' => 'CustomerContentSubType:' . self::SUB_CONTENT_TYPE 
    ]);

    $result = $this->ocApi->get_oc_suggest();

    $languages = array_map(static function ($language) {
      return $language->name;
    },  $result);

    $this->setViewData('language_filter', array_merge([
      '' => 'All'
    ], array_combine($languages, $languages)));
  }
  
  public static function getPage()
  {
    $pages = get_posts([
      'meta_key' => '_wp_page_template',
      'meta_value' => 'page-cinema-listing.php',
      'post_type' => 'page',
    ]);
    
    return $pages[0] ?? null;
  }

  public static function getDateControl(int $id = null): ?string
  {
    if (!$id) {
      return null;
    }

    $uuid = get_post_meta($id, 'date_control_article', $single = true);

    if (!$uuid) {
      return null;
    }
    $provider = OpenContentProvider::setup();
    $article = $provider->getObject($uuid);

    return $article ? $article->get_value('teaserbody') : null;
  }

  public static function getCinemaLocations(): array
  {
    $cinemaLocations = json_decode(
      file_get_contents(Utilities::childThemePath() . self::CINEMA_LOCATIONS), 
      true
    );

    return [
      'cinema_locations' => $cinemaLocations,
      'cinema_filter' => array_merge(['' => 'All'], array_combine(
        array_keys($cinemaLocations),
        array_map(function ($location) {
          return ucfirst(str_replace('-', ' ', $location));
        }, array_keys($cinemaLocations))
      ))
    ];
  }

  public function render()
  {
    View::render('@base/page/page-cinema-listing', array_replace(
      $this->getViewData(),
      self::getCinemaLocations()
    ));
  }
}
