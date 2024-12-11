<?php declare (strict_types = 1);

namespace KTDTheme\ViewModels;

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Support\Date;
use Infomaker\Everyware\Twig\View;
use KTDTheme\Paginator;
use KTDTheme\ViewModels\Teasers\Teaser;
use Tightenco\Collect\Support\Arr;

class SearchPage extends BasePage
{
  private const SORT_NEWEST = 'newest';
  private const SORT_OLDEST = 'oldest';
  private const OC_SORT_FIELD = 'updated';
  private const TYPE_ARTICLE = 'article';
  private const TYPE_GALLERY = 'gallery';
  private const TYPE_VIDEO = 'video';

  private $limit = 16;
  private $dateRange;
  private $pagenr;
  private $type;
  private $sort;

  public function __construct(Page $page)
  {
    parent::__construct($page);

    $this->setLimit(Arr::get($_GET, 'limit'));
  
    $this->sort = Arr::get($_GET, 'sort');
    $this->pagenr = (int) Arr::get($_GET, 'pagenr', 1);
    $this->type = Arr::get($_GET, 'type', '');
    $this->dateRange = Arr::get($_GET, 'daterange');

    $this->getArticles();

    $this->setViewData('limit', $this->limit);
    $this->setViewData('dateRange', $this->dateRange);
    $this->setViewData('pagenr', $this->pagenr);
    $this->setViewData('type', $this->type);
    $this->setViewData('sort', $this->sort);
    $this->setViewData('sortOrders', $this->getSortings());
    $this->setViewData('types', $this->getTypes());
    $this->setViewData('tealiumGroup', 'searchpage');
  }

  private function setLimit(?string $limit): void
  {
    $limit = (int) $limit;
    
    if (!$limit || $limit < 0) {
      return;
    }

    $this->limit = $limit > 100 ? 100 : $limit;
  }

  private function getArticles()
  {
    $provider = OpenContentProvider::setup([
      'q' => QueryBuilder::where('contenttype', 'Article')
        ->andIfProperty('CustomerContentSubType', urldecode(strtoupper($this->type)))
        ->andIf(ContentSubType_Query_Param)
        ->setText(urldecode($this->getQuery()))
        ->buildQueryString(),
      'limit' => $this->limit,
      'start' => $this->pagenr * $this->limit - $this->limit,
      'sort.indexfield' => self::OC_SORT_FIELD,
      'sort.updated.ascending' => $this->getSortAscending() ? 'true' : 'false',
      'Pubdate' => $this->getDateFilter(),
    ]);

    $provider->setPropertyMap('Article');

    $articles = array_map(static function (NewsMLArticle $article) {
      return (new Teaser($article))->getViewData();
    }, $provider->queryWithRequirements());

    $this->setViewData('articles', $articles);
    $this->setViewData('paginator', Paginator::make($provider->hits(), $this->limit));
  }

  private function getDateFilter(): string
  {
    $startDate = '*';
    $endDate = '*';

    if ($this->dateRange) {
      $dateRange = explode(' - ', $this->dateRange);
      $start = $dateRange[0] ?? null;
      $end = $dateRange[1] ?? null;
      $startDate = $start ? Date::parse($start)->toIso8601ZuluString() : $startDate;
      $endDate = $end ? Date::parse($end)->toIso8601ZuluString() : $endDate;
    }

    return "[{$startDate} TO {$endDate}]";
  }

  private function getSortAscending(): bool
  {
    return $this->sort === self::SORT_OLDEST;
  }

  private function getTypes(): array
  {
    return [
      '' => 'ALL',
      self::TYPE_ARTICLE => 'ARTICLES',
      self::TYPE_GALLERY => 'PHOTOS',
      self::TYPE_VIDEO => 'VIDEOS'
    ];
  }

  public function getSortings(): array
  {
    return [
      '' => 'SORT BY',
      self::SORT_NEWEST => strtoupper(self::SORT_NEWEST),
      self::SORT_OLDEST => strtoupper(self::SORT_OLDEST)
    ];
  }

  public function render()
  {

    View::render('@base/page/search', array_replace($this->getViewData(), [
      'metaGroup' => 'search'
    ]));
  }
}
