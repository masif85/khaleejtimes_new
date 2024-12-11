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
  private $stateParam = '';

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
    
    $this->stateParam = ( $page->post_name == 'prayer-time-uae' ) ? 'dubai' : $page->post_name;

    $this->getMenus($page);
    $this->getContent();
    $this->getArticles();
    $this->getPrayerTimings();
    $this->setViewData('tealiumGroup', 'prayer-timings');
  }

  public function render()
  {

    View::render('@base/page/page-prayer-timings', $this->getViewData());
  }

  private function formatDate( $date ){
    $date = str_replace( 'T', ' ', $date );
    return $date;
  }

  private function getPrayerTimings()
  {
    // Get Data from API
    $url = 'https://api.khaleejtimes.com/prayertime/get_data?city='. $this->stateParam;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    curl_close($ch);

    if( empty( $response ) ){
      return false;
    }

    $todayDate        = date('Y-m-d');
    $islamicMonthsArr = array(
      1  => 'Muḥarram',
      2  => 'Safar',
      3  => 'Rabi al-Awwal',
      4  => 'Rabi al-Thani',
      5  => 'Jumada al-Ula',
      6  => 'Jumada al-Akhirah',
      7  => 'Rajab',
      8  => 'Shaban',
      9  => 'Ramadan',
      10 => 'Shawwal',
      11 => 'Dhu al-Qadah',
      12 => 'Dhu al-Hijjah'
    );

    $response = json_decode( $response, true );

    $responseArr = array();
    foreach( $response as $key => $res ){
      $date = str_replace( 'T', ' ', $res['listDateGreg'] );
      $date = date( 'Y-m-d', strtotime( $date ) );
      
      $islamicDateStr = $res['hijriDateString'];
      $islamicDateArr = explode( '/', $islamicDateStr );

      $islamicMonth = $islamicMonthsArr[$islamicDateArr[1]];
      $islamicDate  = $islamicDateArr[2];
      $islamicYear  = $islamicDateArr[0];

      $islamicDateF = "{$islamicMonth} {$islamicDate}, {$islamicYear}";


      if( $todayDate == $date ){
        $responseArr['data']['today'] = array(
          'islamicdate' => $islamicDateF,
          'fajr'        => explode('T', $res['fajr'])[1],
          'shuruq'      => explode('T', $res['sunrise'])[1],
          'dhuhr'       => explode('T', $res['dhuhr'])[1],
          'asr'         => explode('T', $res['asr'])[1],
          'magrib'      => explode('T', $res['maghrib'])[1],
          'isha'        => explode('T', $res['isha'])[1]
        );
      }

      $responseArr['data']['monthly']['headings'] = array(
        date('F'), 'Day', 'Hijri', 'Month', 'Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'
      );

      $dateTimeStamp = strtotime( $this->formatDate( $res['listDateGreg'] ) );

      $day       = date( 'j', $dateTimeStamp );
      $weekDay   = date( 'l', $dateTimeStamp );
      $monthDate = date( 'd', $dateTimeStamp );
      $fajr      = date( 'g:i A', strtotime( $this->formatDate( $res['fajr'] ) ) );
      $sunrise   = date( 'g:i A', strtotime( $this->formatDate( $res['sunrise'] ) ) );
      $dhuhr     = date( 'g:i A', strtotime( $this->formatDate( $res['dhuhr'] ) ) );
      $asr       = date( 'g:i A', strtotime( $this->formatDate( $res['asr'] ) ) );
      $maghrib   = date( 'g:i A', strtotime( $this->formatDate( $res['maghrib'] ) ) );
      $isha      = date( 'g:i A', strtotime( $this->formatDate( $res['isha'] ) ) );


      $row = array(
        $day, $weekDay, $islamicDate, $islamicMonth, $fajr, 
        $sunrise, $dhuhr, $asr, $maghrib, $isha
      );

      $responseArr['data']['monthly']['body'][] = $row;
    }

    return $responseArr;
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

    // Get Data From API
    $prayerTimeData = $this->getPrayerTimings();

    /*echo '<pre>';
    print_r( $prayerTimeData ); die;*/

    $articleBody = $article->getParsedBody();
    
    /*$contentPart = $articleBody->firstOfType('x-im/content-part');
    if ($contentPart) {
      $data = $contentPart->getFirstChild('data');
      $data = $data ? $data->toArray() : [];

      $this->setViewData('islamicdate', Arr::pull($data, 'islamicdate'));
      $this->setViewData('contentPartData', $data);
    }*/

    $data = $prayerTimeData['data']['today'];
    $this->setViewData('islamicdate', Arr::pull($data, 'islamicdate'));
    $this->setViewData('contentPartData', $data);

    $tableDataHeading = $prayerTimeData['data']['monthly']['headings'];
    $tableData        = $prayerTimeData['data']['monthly']['body'];

    /*echo '<pre>';
    print_r( $tableData ); die;*/

    $this->setViewData('tableDataHeading', $tableDataHeading);
    $this->setViewData('tableData', $tableData);
    // $this->setViewData('tableData', 'asdfdasfdasfsa');

    /*$table = $articleBody->firstOfType('x-im/table');
    if ($table) {
      $tableData = $table->getFirstChild('data');

      echo '<pre>';
      print_r( $tableData ); die;

      $this->setViewData('tableData', $tableData);
    }*/

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
