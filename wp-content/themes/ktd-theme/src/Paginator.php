<?php

namespace KTDTheme;

use Infomaker\Everyware\Twig\View;

class Paginator
{
  const PAGE_KEY = 'pagenr';

  /**
   * @var int
   */
  public $totalPages;

  /**
   * @var int
   */
  public $currentPage;

  /**
   * @var int
   */
  public $perPage = 15;

  /**
   * @var int
   */
  public $total;

  /**
   * @var array
   */
  private $links = [];

  /**
   * @var array
   */
  private $query = [];

  /**
   * @var string
   */
  private $url;

  public function __construct(int $total, int $perPage = 15)
  {
    $this->total = $total;
    $this->perPage = $perPage;

    $this->setUrlParameters();
    $this->setTotalPages();
    $this->setCurrentPage();
  }

  /**
   * Make instance of a paginator
   *
   * @param int $total
   * @param int $perPage
   *
   * @return self
   */
  public static function make(int $total, int $perPage = 15) : self
  {
    return new static($total, $perPage);
  }

  private function setUrlParameters()
  {
    $this->url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    parse_str($_SERVER['QUERY_STRING'], $this->query);
  }

  private function setTotalPages()
  {
    $this->totalPages = (int) round(ceil($this->total / $this->perPage)) ?: 1;
  }

  private function setCurrentPage()
  {
    $this->currentPage = (int) ($_GET[self::PAGE_KEY] ?? 1);

    if ($this->currentPage > $this->totalPages) {
      $this->currentPage = $this->totalPages;
    }
  }

  public function render()
  {
    View::render('@base/page/part/pagination.twig', [
      'links' => $this->getLinks()
    ]);
  }

  public function getStart(): int
  {
    return $this->perPage * $this->currentPage - $this->perPage;
  }

  public function getCurrentTotal(): int
  {
    $currentTotal = $this->perPage * $this->currentPage;

    return $currentTotal < $this->total ? $currentTotal : $this->total;
  }

  public function getLinks() : ?array
  {
    if ($this->totalPages <= 1) {
      return null;
    }

    // Links already generated
    if ($this->links) {
      return $this->links;
    }

    $this->addPreviousLink();

    // Simple pagination without separators
    if ($this->totalPages <= 7) {
      for ($page = 1; $page <= $this->totalPages; $page++) {
        $this->addPageLink($page);
      }
    } else {
      // First Page
      if ($this->currentPage != 1) {
        $this->addPageLink(1);
      }

      // Left Side Dots
      if ($this->currentPage > 4) {
        $this->addLink('...');
      }

      // Left Side Adjacent Pages
      $this->addLinks($this->getLeftSideRange());

      // Current Page
      $this->addPageLink($this->currentPage);

      // Right Side Adjacent Pages
      $this->addLinks($this->getRightSideRange(), false);

      // Right Side Dots
      if ($this->totalPages - $this->currentPage >= 4) {
        $this->addLink('...');
      }

      // Last Page
      if ($this->currentPage != $this->totalPages) {
        $this->addPageLink($this->totalPages);
      }
    }

    $this->addNextLink();

    return $this->links;
  }

  private function addPreviousLink()
  {
    $this->addLink(
      '<',
      $this->currentPage > 1 ? $this->makeUrl($this->currentPage - 1) : null,
    );
  }

  private function addNextLink()
  {
    $this->addLink(
      '>',
      $this->currentPage < $this->totalPages ? $this->makeUrl($this->currentPage + 1) : null
    );
  }

  private function addPageLink(int $pageNumber)
  {
    $this->addLink($pageNumber, $this->makeUrl($pageNumber));
  }

  private function addLink(string $text, string $url = null)
  {
	$this->links[] = [
		'text' => $text,
		'pagenumber'=>trim($text),
      	'url' => $url,	
		'page'=>parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
      	'active' => $text == $this->currentPage,
    ];
  }

  private function addLinks(int $count, bool $isLeftSide = true)
  {
    if (! $count) {
      return;
    }

    if ($isLeftSide) {
      $min = $this->currentPage - $count;
      $max = $this->currentPage - 1;
    } else {
      $min = $this->currentPage + 1;
      $max = $this->currentPage + $count;
    }

    for ($page = $min; $page <= $max; $page++) {
      $this->addPageLink($page);
    }
  }

  private function getLeftSideRange() : int
  {
    // Last Two
    if ($this->totalPages - $this->currentPage <= 2) {
      return $this->currentPage + 4 - $this->totalPages;
    }

    // In Between
    if ($this->currentPage >= 4 && $this->currentPage <= $this->totalPages - 3) {
      return 2;
    }

    // Third
    if ($this->currentPage == 3) {
      return 1;
    }

    // First Two
    return 0;
  }

  private function getRightSideRange() : int
  {
    // First Two
    if ($this->currentPage <= 2) {
      return 5 - $this->currentPage;
    }

    $pagesLeft = $this->totalPages - $this->currentPage;

    // In Between
    if ($pagesLeft >= 3) {
      return 2;
    }

    // Last Two
    return $pagesLeft > 1 ? 1 : 0;
  }

  /**
   * Return path with query and update page number.
   *
   * @param int $pageNumber
   *
   * @return string
   */
  private function makeUrl(int $pageNumber) : string
  {
    $query = http_build_query(array_merge($this->query, [self::PAGE_KEY => $pageNumber]));

    return "$this->url?$query";
  }

}
