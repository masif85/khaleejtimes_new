<?php declare(strict_types=1);

namespace Everyware\Plugin\Sitemap;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Support\Arr;
use OcArticle;
use OcImage;
use WP_Post;
use WP_Query;

class Sitemap
{
  /**
   * First article date in OC
   *
   * @var string
   */
  private const FIRST_ARTICLE = '2003-02-07';

  /**
   * First image date in OC
   *
   * @var string
   */
  private const FIRST_IMAGE = '2018-10-10';

  /**
   * First video date in OC
   *
   * @var string
   */
  private const FIRST_VIDEO = '2015-07-21';

  /**
   * Domain for images in sitemap
   *
   * @var string
   */
  private const IMAGE_DOMAIN = 'https://image.khaleejtimes.com';

  /**
   * Api url to get video thumbnails
   *
   * @var string
   */
  private const VIDEO_API = 'https://api.dailymotion.com';

  /**
   * Sitemap types
   *
   * @var array
   */
  private $root = [
    'news',
    'archive',
    'images',
    'videos'
  ];
  private $lang = ['hi','ml','ta'];
  /** @var string */
  private $type;

  /** @var string */
  private $year;

  /** @var string */
  private $month;

  /** @var string */
  private $first;

  /**
   * Initialize and return Sitemap.
   *
   * @return self
   */
  public static function init(string $type = null, string $year = null, string $month = null, string $lang = null): self
  {
    $inst = new self();

    $inst->type = $type;
    $inst->year = $year;
    $inst->month = $month;
    $inst->lang = $lang;

    return $inst;
  }

  private function __construct()
  {
    $this->addRewriteRules();

    add_filter('query_vars', [$this, 'addQueryVars']);
  }

  /**
   * Add rewrite rules for sitemap
   *
   * @return void
   */

	
	
	  public function addRewriteRules(): void
  {
    $types = implode('|', $this->root);
    $langs = implode('|', $this->lang);

    add_rewrite_rule(
      "sitemap/({$langs})/({$types})/?$",
      'index.php?pagename=sitemap&sitemap_type=$matches[2]&sitemap_lang=$matches[1]',
      'top'
    );
    add_rewrite_rule(
      "sitemap/({$types})/?$",
      'index.php?pagename=sitemap&sitemap_type=$matches[1]',
      'top'
    );

    $types = implode('|', Arr::except($this->root, 'news'));
	  $langs = implode('|', Arr::except($this->lang, 'hi'));

    add_rewrite_rule(
      "sitemap/({$langs})/({$types})/([0-9]{4})/?$",
      'index.php?pagename=sitemap&sitemap_type=$matches[2]&sitemap_year=$matches[3]&sitemap_lang=$matches[1]',
      'top'
    );
    add_rewrite_rule(
      "sitemap/({$langs})/({$types})/([0-9]{4})/([0-9]{2})?$",
      'index.php?pagename=sitemap&sitemap_type=$matches[2]&sitemap_year=$matches[3]&sitemap_lang=$matches[1]',
      'top'
    );
    add_rewrite_rule(
      "sitemap/({$types})/([0-9]{4})/?$",
      'index.php?pagename=sitemap&sitemap_type=$matches[1]&sitemap_year=$matches[2]',
      'top'
    );
    add_rewrite_rule(
      "sitemap/({$types})/([0-9]{4})/([0-9]{2})?$",
      'index.php?pagename=sitemap&sitemap_type=$matches[1]&sitemap_year=$matches[2]&sitemap_month=$matches[3]',
      'top'
    );
  }

  /**
   * Add sitemap querystring variables
   *
   * @param array $queryVars
   * @return array
   */
  public function addQueryVars(array $queryVars): array
  {
    $queryVars[] = 'sitemap_type';
    $queryVars[] = 'sitemap_year';
    $queryVars[] = 'sitemap_month';
    $queryVars[] = 'sitemap_lang';

    return $queryVars;
  }

  /**
   * Render sitemap xml
   *
   * @return string
   */
  public function getXml(): string
  {
    if (!$this->type) {
      return $this->getRootXml();
    }

    switch ($this->type) {
      case 'news':
        return $this->getNewsXml();
      case 'archive':
        // Request file from static bucket
        $externalFilePath = CF_STATIC . "/sitemap/{$this->year}-{$this->month}.xml";
        $archiveResp = wp_remote_get($externalFilePath);

        if (wp_remote_retrieve_response_code($archiveResp) === 200) {
            header('X-Return-From-Archive: true');
            // Only serve archive file if 200
            //error_log("RETURNED RESPONSE FROM ARCHIVE: {$externalFilePath}");
            return wp_remote_retrieve_body($archiveResp);
        } else {
            header('X-Return-From-Archive: false');
            if (is_wp_error($archiveResp)) {
                // This only represents a failed HTTP request or internal error
                error_log($archiveResp->get_error_message());
            } else {
                // This is when an archive file is not found
                //error_log("FAILED TO FIND ARCHIVE FILE - SERVED FROM WP");
            }
            $this->first = $this::FIRST_ARTICLE;
            return $this->getArchiveXml();
        }
      case 'images':
        $this->first = $this::FIRST_IMAGE;
        return $this->getImagesXml();
      case 'videos':
        $this->first = $this::FIRST_VIDEO;
        return $this->getVideosXml();
      default:
        return '';
    }
  }

  /**
   * Get headers for sitemap.
   * Add cache headers for old content.
   * 1 week for content older than 2 months or if the year is different increase to 1 month
   *
   * @return array
   */
  public function getHeaders(): array
  {
    $headers = ['Content-Type' => 'application/xml'];
    $headers['expires'] = '120';

    if ($this->year && $this->month) {
      $date = Carbon::createFromDate($this->year, $this->month, 1);

      if (Carbon::today()->endOfMonth()->diffInMonths($date) > 2) {
        if (!$date->isCurrentYear()) {
          $headers['expires'] = '2629746 ';
        } else {
          $headers['expires'] = '604800';
        }
      } 
    }

    return $headers;
  }

  /**
   * Get root sitemap
   *
   * @return string
   */
  private function getRootXml(): string
  {
    $links = array_map(static function($sitemapType) {
      return [
        'type' => $sitemapType,
        'last_updated' => $sitemapType == 'news' ? Carbon::now() : Carbon::today()
      ];
    }, $this->root);

    return View::generate('@sitemapPlugin/views/sitemap.twig', [
      'langs' => ['hi','ml','ta'],
      'links' => $links,
      'domain' => home_url()
    ]);
  }

  /**
   * Get sitemap with latest articles
   *
   * @return string
   */
  private function getNewsXml(): string
  {
    $latestArticles = $this->getOcApiHandler()->getLatestArticles();

    return $this->articlesToXml($this->mapOcArticles($latestArticles));
  }

  /**
   * Get archive articles for sitemap
   *
   * @return string
   */
  private function getArchiveXml(): string
  {
    if ($this->month) {
      $query = new WP_Query([
        'post_type'  => 'article',
        'nopaging' => true,
        'date_query' => [
          ['year'  => $this->year, 'month' => $this->month]
        ],
      ]);

      return View::generate('@sitemapPlugin/views/sitemap-articles.twig', [
        'domain' => home_url(),
        'articles' => $this->mapWpArticles($query->posts)
      ]);
    }

    return $this->getListXml();
  }

  /**
   * List months for sitemap type
   *
   * @param string $type
   * @param string $first
   * @param string|null $year
   * @return string
   */
  private function getListXml(): string
  {
    if ($this->year) {
      $date = Carbon::createFromDate($this->year, 1, 1);
      $interval = CarbonPeriod::create($date, '1 month', $date->isCurrentYear() ? Carbon::today() : $date->copy()->endOfYear());
    } else {
      $interval = CarbonPeriod::create($this->first, '1 month', Carbon::today());
    }

    return View::generate('@sitemapPlugin/views/sitemap-interval.twig', [
      'type' => $this->type,
      'period' => $interval,
      'domain' => home_url()
    ]);
  }

  /**
   * Get images for sitemap
   *
   * @return string
   */
  private function getImagesXml(): string
  {
    if ($this->month) {
      $images = $this->getOcApiHandler()->getImages(Carbon::createFromDate($this->year, $this->month));
      
      return View::generate('@sitemapPlugin/views/sitemap-images.twig', [
        'domain' => home_url(),
        'image_domain' => $this::IMAGE_DOMAIN,
        'images' => $this->mapOcImages($images)
      ]);
    }

    return $this->getListXml();
  }

  /**
   * Get videos for sitemap
   *
   * @return string
   */
  private function getVideosXml(): string
  {
    if ($this->month) {
      $videos = $this->getOcApiHandler()->getVideos(Carbon::createFromDate($this->year, $this->month));
      
      return View::generate('@sitemapPlugin/views/sitemap-videos.twig', [
        'videos' => $this->mapOcVideos($videos)
      ]);
    }

    return $this->getListXml();
  }

  /**
   * Export sitemap as XML
   *
   * @param array $articles
   * @return string
   */
  private function articlesToXml(array $articles): string
  {
    return View::generate('@sitemapPlugin/views/sitemap-articles.twig', [
      'lang' => $this->lang,
      'domain' => home_url(),
      'articles' => $articles
    ]);
  }

  /**
   * Map OcArticle properties for sitemap xml
   *
   * @param array $ocArticles
   * @return array
   */
  private function mapOcArticles(array $ocArticles): array
  {
    return array_map(static function(OcArticle $ocArticle) {
      return [
        'headline' => $ocArticle->get_value('headline'),
        'url' => $ocArticle->get_permalink(),
        'pubdate' => $ocArticle->get_value('pubdate'),
        'last_updated' => $ocArticle->get_value('updated')
      ];
    }, $ocArticles);
  }

  /**
   * Map OcImage properties for sitemap xml
   *
   * @param array $ocImages
   * @return array
   */
  private function mapOcImages(array $ocImages): array
  {
    return array_map(static function(OcImage $ocImage) {
      return [
        'uuid' => current($ocImage->get('uuid') ?? []),
        'date' => current($ocImage->get('created') ?? [])
      ];
    }, $ocImages);
  }

  /**
   * Map video OcArticle properties for sitemap xml
   *
   * @param array $ocVideos
   * @return array
   */
  private function mapOcVideos(array $ocVideos): array
  {
    return array_filter(array_map(function(OcArticle $ocArticle) {
      $videoId = $ocArticle->get_value('customervideoid');
      
      if (!$videoId) {
        return [];
      }
      
      return [
        'headline' => $ocArticle->get_value('headline'),
        'description' => $ocArticle->get_value('teaserbody'),
        'videoId' => $videoId,
        'thumbnail' => $this->getVideoThumbnail($videoId),
        'url' => $ocArticle->get_permalink(),
        'pubdate' => $ocArticle->get_value('pubdate'),
      ];
    }, $ocVideos));
  }
  
  /**
   * Map wp articles for sitemap xml
   *
   * @param array $articles
   * @return array
   */
  private function mapWpArticles(array $articles): array
  {
	/* return array_map(static function(WP_Post $article) {
      return [
        'headline' => $article->post_title,
        'url' => get_permalink($article),
        'pubdate' => Carbon::parse($article->post_date)->toIso8601ZuluString(),
        'last_updated' => Carbon::parse($article->post_modified)->toIso8601ZuluString()
      ];
    }, $articles); */	  
	//$can=OcArticle::get_value('headline');  
    //$can=$this->getcan();	  
   return array_map(function(WP_Post $article) {	
	/*$cantag="khaleejtimes";  
     $article_body=$this->getOcApiHandler()->getArticle($article->post_title);
     if($article_body):
     $articles=json_decode(json_encode($article_body[0]), true);    
     if(array_key_exists("customercanonicaltagging", $articles) && $articles['customercanonicaltagging'])
     {       
      $cantag =$articles['customercanonicaltagging'][0];
    }
    else
    {
      $cantag="khaleejtimes";
    }
    else:
     $cantag="khaleejtimes";
   endif;
   */
  return [
        'headline' => $article->post_title,	 
        //'cantag' =>$cantag,         
        'url' => get_permalink($article),
        'pubdate' => Carbon::parse($article->post_date)->toIso8601ZuluString(),
        'last_updated' => Carbon::parse($article->post_modified)->toIso8601ZuluString()
      ];      
   
    }, $articles);	   
	  
  }
	
 
  /**
   * Get thumbnail image for video
   *
   * @param string $videoId
   * @return string|null
   */
  private function getVideoThumbnail(string $videoId): ?string
  {
    $url = $this::VIDEO_API;
    try {
      $response = @file_get_contents("{$url}/video/{$videoId}?fields=thumbnail_large_url");

      if ($response !== false) {
        $video = json_decode($response);
      }

      return $video->thumbnail_large_url ?? null;
    } catch (Exception $e) {
    }
  }

  /**
   * Initialize and return OcApiHandler.
   *
   * @return OcApiHandler
   */
  private function getOcApiHandler(): OcApiHandler
  {
    return new OcApiHandler();
  }
}
