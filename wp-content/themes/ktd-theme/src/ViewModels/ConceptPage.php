<?php

namespace KTDTheme\ViewModels;

use Infomaker\Everyware\Base\Models\Page as PageModel;
use Infomaker\Everyware\Support\NewRelicLog;
use Infomaker\Everyware\Base\Utilities;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use EuKit\Base\Parts\NewsMLArticle;
use KTDTheme\ViewModels\Teasers\Teaser;
use KTDTheme\Paginator;
use Tightenco\Collect\Support\Arr;
use Infomaker\Everyware\Twig\View;

class ConceptPage extends Page
{
  private $limit = 10;
  private $pagenr = 1;
  private $view = 'concept-page';

  public function __construct(PageModel $page)
  {
    try {
      $concept = Concept::createFromPost($page);
    }
    catch(\UnexpectedValueException $exception) {
      NewRelicLog::logException($exception);
      Utilities::trigger404();
    }

    $this->pagenr = (int) Arr::get($_GET, 'pagenr', $this->pagenr);
    $pageUrlParam = Arr::get($_GET, 'pagenr','');

    $concept->fill([
      'currentPage' => $page,
      'pageUrlParam' => $pageUrlParam,
      'tealiumGroup' => 'concept',
      'parentPostName' => get_the_title($page->post_parent),
      'getMetaDescription' => $page->getMeta('meta_description', ''),
      'getMetaTitle' => $page->getMeta('meta_title', ''),
      'pagetype'=>$concept->type,
      'getMetaKeywords' => $page->getMeta('meta_keywords', ''),
    ]);
    
    parent::__construct($concept);

    $this->getArticles($concept);

    if ($concept->type === 'author') {
      $this->view = 'page-author';
      $this->setViewData('avatar', $concept->getAvatar());
      $this->setViewData('meta_data', $concept->getMetadata());
      $this->setViewData('related_links', $concept->getRelatedLinks());
    }
  }

  private function getArticles(Concept $concept)
  {
    $query = QueryBuilder::where('ConceptUuids', $concept->uuid)->andIf(ContentSubType_Query_Param);

    $provider = OpenContentProvider::setup([
      'q' => $query->buildQueryString(),
      'contenttypes' => ['Article'],
      'sort.indexfield' => 'Pubdate',
      'sort.Pubdate.ascending' => 'false',
      'limit' => $this->limit,
      'start' => $this->pagenr * $this->limit - $this->limit,
      'Status' => 'usable',
    ]);
    $provider->setPropertyMap('Article');

    $articles = array_map(static function (NewsMLArticle $article) {
      return (new Teaser($article))->getViewData();
    }, $provider->queryWithRequirements());

    $this->setViewData('articles', $articles);
    $this->setViewData('paginator', Paginator::make($provider->hits(), $this->limit));
  }

  public function render()
  {
    View::render("@base/page/{$this->view}", $this->getViewData());
  }
}
