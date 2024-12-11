<?php declare(strict_types=1);

namespace Everyware\Plugin\Sitemap;

use Carbon\Carbon;
use OcAPI;
use OcArticle;
use Exception;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use OcImage;

class OcApiHandler
{
  /**
   * @var OcAPI
   */
  private $api;

  /**
   * Some queries require a limit, even though you might not have one in mind.
   */
  private const LIMIT = 10000;

  /**
   * @todo Change to TRUE once cache can be used reliably (without clearing out parts of the properties loaded by another process)
   */
  private const CACHE_CAN_BE_USED = false;

  /**
   * All properties that sitemap is going to need about articles.
   */
  private const ARTICLE_PROPERTIES = [
    'uuid',
    'contenttype',
    'TeaserRaw',
    'TeaserHeadline',
    'TeaserBody',
    'Authors.Name',
    'Pubdate',
    'Sections.Name',
    'Section',
    'CategoryName',
    'updated'
  ];

  /**
   * All properties that sitemap is going to need about videos.
   */
  private const VIDEO_PROPERTIES = [
    'uuid',
    'contenttype',
    'TeaserRaw',
    'TeaserHeadline',
    'TeaserImageUuids',
    'TeaserBody',
    'Authors.Name',
    'Pubdate',
    'Sections.Name',
    'Section',
    'CategoryName',
    'CustomerVideoId',
  ];

  /**
   * All properties that sitemap is going to need about images.
   */
  private const IMAGE_PROPERTIES = [
    'uuid',
    'created'
  ];

  public function __construct()
  {
      $this->api = new OcAPI();
  }

  /**
   * Get latest articles for sitemap from OC
   *
   * @return array
   */
  public function getLatestArticles(): array
  {
    $filter = $this->getDateFilter(
      'Pubdate',
      Carbon::now()->subDays(2)->toIso8601ZuluString(),
      Carbon::now()->toIso8601ZuluString(),
    );

    return $this->getArticles($filter);
  }

  /**
   * Undocumented function
   *
   * @param Carbon $date
   * @return array
   */
  public function getImages(Carbon $date): array
  {
    $params = [
      'contenttypes' => ['Image'],
      'limit'        => self::LIMIT,
      'properties'   => self::IMAGE_PROPERTIES,
      'q'            => $this->getDateFilter('ObjectCreated', $date->startOfMonth()->toIso8601ZuluString(), $date->endOfMonth()->toIso8601ZuluString()),
      'sort.indexfield'        => 'ObjectCreated',
      'sort.ObjectCreated.ascending' => 'false'
    ];

    try {
      $list = $this->api->search($params, self::CACHE_CAN_BE_USED);

      return array_filter($list, static function ($item) {
        return $item instanceof OcImage;
      });
    } catch (Exception $e) {
      return [];
    }
  }

  public function getVideos(Carbon $date): array
  {
    $params = [
      'contenttypes' => ['Article'],
      'limit'        => self::LIMIT,
      'properties'   => self::VIDEO_PROPERTIES,
      'q'            => QueryBuilder::query('CustomerContentSubType:VIDEO')
        ->append('CustomerVideoId:[\'\' TO *]')
        ->append($this->getDateFilter('Pubdate', $date->startOfMonth()->toIso8601ZuluString(), $date->endOfMonth()->toIso8601ZuluString()))
        ->buildQueryString(),
      'sort.indexfield'        => 'Pubdate',
      'sort.Pubdate.ascending' => 'false'
    ];

    try {
      $list = $this->api->search($params, self::CACHE_CAN_BE_USED);

      return array_filter($list, static function ($item) {
        return $item instanceof OcArticle;
      });
    } catch (Exception $e) {
      return [];
    }
  }

  public function getArticlesForMonth(Carbon $date): array
  {
    $filter = $this->getDateFilter(
      'Pubdate',
      $date->startOfMonth()->toIso8601ZuluString(),
      $date->copy()->endOfMonth()->toIso8601ZuluString()
    );

    return $this->getArticles($filter);
  }

  /**
   * Get articles for sitemap from OC
   *
   * @return array
   */
  public function getArticles(string $query): array
  {
    $params = [
      'contenttypes' => ['Article'],
      'limit'        => self::LIMIT,
      'properties'   => self::ARTICLE_PROPERTIES,
      'q'            => $query,
      'sort.indexfield'        => 'Pubdate',
      'sort.Pubdate.ascending' => 'false'
    ];

    try {
      $list = $this->api->search($params, self::CACHE_CAN_BE_USED);

      return array_filter($list, static function ($item) {
        // todo replace OcArticle with OcArticleInterface, once they implement it.
        return $item instanceof OcArticle;
      });
    } catch (Exception $e) {
      return [];
    }
  }

  private function getDateFilter(string $dateField, string $start, string $end)
  {
    return "{$dateField}:[{$start} TO {$end}]";
  }
}
