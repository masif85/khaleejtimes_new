<?php declare (strict_types = 1);

namespace KTDTheme\ViewModels;

use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Twig\View;
use Tightenco\Collect\Support\Arr;

class PrayerTimingsPage extends BasePage
{
  private $uuid = '8cdce398-b4c3-491d-b72e-006b62f5f72a';

  private $labels = [
	'imsak' => 'IMSAK',
    'fajr' => 'FAJR',
    'shuruq' => 'SUNRISE',
    'dhuhr' => 'DHUHR',
    'asr' => 'ASR',
    'magrib' => 'MAGHRIB',
    'isha' => 'ISHA'
  ];

  public function __construct(Page $page)
  {
    parent::__construct($page);
    
    $this->getMenus($page);
    $this->getContent();
    $this->getArticles();

    $this->setViewData('tealiumGroup', 'prayer-timings');
  }

  public function render()
  {
    View::render('@base/page/page-prayer-timings', $this->getViewData());
  }

  private function getMenus(Page $currentPage): void
  {
    $posts = get_posts([
      'meta_key' => '_wp_page_template',
      'meta_value' => 'page-prayertimings.php',
      'post_type' => 'page',
      'nopaging' => true,
      'orderby' => 'menu_order',
      'order' => 'asc',
    ]);

    $pages = collect($posts);

    $parentPage = $currentPage->post_parent
      ? Page::createFromId((int)$currentPage->post_parent)
      : $currentPage;

    $activePage = $currentPage;
    
    $default = get_post_meta($parentPage->id, 'submenu_default', true);
    
    $posts = $pages->filter(function($page) use ($parentPage) {
      return $page->post_parent == $parentPage->id;
    });

    if (get_post_meta($parentPage->id, 'show_submenu', true)) {
      $this->setViewData('prayertimesSubMenu', $posts->toArray());
      // Since submenu is only enable for uae then set variable based on that.
      $this->setViewData('isUAE', true);
    }

    if (!$currentPage->post_parent) {
      $post = $posts->first(function($post) use ($default) {
        return $default ? $post->post_name == $default : true;
      });

      if ($post) {
        $activePage = Page::createFromPost($post);
      }
    }

    $this->setViewData('activePage', $activePage);
    $this->setViewData('prayertimesMenu', $this->getMenuItems('prayertimes'));
  }

  private function getContent()
  {
    $provider = OpenContentProvider::setup([
      'uuid' => $this->uuid,
      'contenttype' => 'Article'
    ]);
    $provider->setPropertyMap('Article');
    $articles = $provider->queryWithRequirements();
    $article = $articles[0] ?? null;

    if (!$article) {
      return;
    }

    $articleBody = $article->getParsedBody();

    $contentPart = $articleBody->firstOfType('x-im/content-part');
    if ($contentPart) {
      $data = $contentPart->getFirstChild('data');
      $data = $data ? $data->toArray() : [];

      $this->setViewData('islamicdate', Arr::pull($data, 'islamicdate'));
      $this->setViewData('contentPartData', $data);
    }

    $table = $articleBody->firstOfType('x-im/table');
    if ($table) {
      $tableData = $table->getFirstChild('data');

      $this->setViewData('tableData', $tableData);
    }

    $this->setViewData('labels', $this->labels);
  }

  private function getArticles()
  {
    // SPONSOREDCONTENT Articles
    $provider = OpenContentProvider::setup([
      'q' => QueryBuilder::query('CustomerContentSubType:SPONSOREDCONTENT')->buildQueryString(),
      'contenttype' => ['Article'],
      'sort.indexfield' => 'Pubdate',
      'sort.Pubdate.ascending' => 'false',
      'limit' => 9,
      'Status' => 'usable'
    ]);

    $articles = $provider->queryWithRequirements();

    if (is_array($articles)) {
      add_filter('ew_content_container_fill', static function ($arr) use ($articles) {
        return array_merge($arr, $articles);
      });
    }
  }
}
