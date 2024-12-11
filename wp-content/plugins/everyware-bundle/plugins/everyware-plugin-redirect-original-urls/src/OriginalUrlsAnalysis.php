<?php declare(strict_types=1);


namespace Everyware\Plugin\RedirectOriginalUrls;


use Everyware\Plugin\RedirectOriginalUrls\Exceptions\OcSearchFailedException;
use OcAPI;
use OcArticle;

/**
 * Class OriginalUrlsAnalysis
 *
 * Analyzes OriginalUrls data to suggest proper settings for {@see PluginSettings}.
 *
 * @package Everyware\Plugin\RedirectOriginalUrls
 */
class OriginalUrlsAnalysis
{
    /**
     * How many sample articles to load for each setting, to look for domains in their OriginalUrls.
     *
     * A higher number will increase the probability that the suggested list of domains is not missing anything.
     * @see OriginalUrlsAnalysis::addDomainsFoundInOriginalUrls()
     */
    private const LIMIT_PER_QUERY = 100;

    /**
     * How many more times to run {@see OriginalUrlsAnalysis::analyzeDomainCoverage()} if the results of the first run did not cover all known articles.
     *
     * A higher number will increase the probability that the suggested list of domains is not missing anything.
     */
    private const DOMAIN_COVERAGE_MAX_EXTRA_CYCLES = 3;

    /**
     * When comparing two settings items: how small the item with least hits may be in comparison to the other item, and still be seen as viable.
     * Range: 0.0 to 1.0.
     * Example 1: an intolerance of 0.2 will accept the least one as long as it has at least 1/5 as many hits as the other one.
     * Example 2: an intolerance of 1.0 will never accept the least one.
     */
    private const MINORITY_SETTING_INTOLERANCE = 0.02;

    /**
     * @var OriginalUrlsAnalysisItem[]
     */
    private $items;

    /**
     * @var array How many times each domain has been mentioned in the analyzed OriginalUrls. [string domain => int counter, ...]
     */
    private $mostFoundDomains;

    /**
     * @var string[] Article UUIDs.
     */
    private $articlesAnalyzed;

    /**
     * @var object|OcAPI
     */
    private $ocApi;

    /**
     * @var array {@see PluginSettings::get()}
     */
    private $settings;

    /**
     * @var string|null The last query used by {@see OriginalUrlsAnalysis::countArticlesWithOriginalUrlsBeginning()}.
     */
    private $lastCountQuery;

    /**
     * OriginalUrlsAnalysis constructor.
     *
     * @param OcAPI $ocApi
     * @param array $settings
     */
    public function __construct(OcAPI $ocApi, array $settings)
    {
        $this->items = [];
        $this->mostFoundDomains = [];
        $this->articlesAnalyzed = [];
        $this->ocApi = $ocApi;
        $this->settings = $settings;

        $propName = $this->settings[PluginSettings::ORIGINAL_URLS_PROPERTY_NAME];
        $this->createItem('extent_paths',             $propName . ':\/*', 'extent_paths');
        $this->createItem('extent_urls',              $propName . ':?* AND NOT ' . $propName . ':\/*', 'extent_urls');
        $this->createItem('schemes_https',            $propName . ':https\:\/\/*');
        $this->createItem('schemes_http',             $propName . ':http\:\/\/*');
        $this->createItem('www_subdomains_with',      $propName . ':https\:\/\/www.* OR ' . $propName . ':http\:\/\/www.*');
        $this->createItem('www_subdomains_without',   $propName . ':?* AND NOT ' . $propName . ':https\:\/\/www.* AND NOT ' . $propName . ':http\:\/\/www.*');
        $this->createItem('trailing_slashes_with',    $propName . ':?*\/');
        $this->createItem('trailing_slashes_without', $propName . ':?* AND NOT ' . $propName . ':?*\/');

        $this->compare('extent_paths',          'extent_urls',              1.0);
        $this->compare('schemes_https',         'schemes_http',             self::MINORITY_SETTING_INTOLERANCE);
        $this->compare('www_subdomains_with',   'www_subdomains_without',   self::MINORITY_SETTING_INTOLERANCE);
        $this->compare('trailing_slashes_with', 'trailing_slashes_without', self::MINORITY_SETTING_INTOLERANCE);

        $this->createDomainsItem();
    }

    public function toArray(): array
    {
        $result = [];

        foreach ($this->items as $item) {
            $result[] = $item->toArray();
        }

        return $result;
    }

    /**
     * Escape characters that are part of the Solr query syntax.
     *
     * @param string $string
     *
     * @return string
     */
    private static function escapeSolrQuery(string $string): string
    {
        static $trans = null;

        if ($trans === null) {
            $reservedChars = '+-&|!(){}[]^"~*?:\\/';
            $trans = [];
            for ($i = 0; $i < strlen($reservedChars); $i++) {
                $trans[$reservedChars[$i]] = '\\' . $reservedChars[$i];
            }
        }

        return strtr($string, $trans);
    }

    /**
     * @param string      $key   Key in name of form input related to this item's setting. Also used to identify the item in this object.
     * @param string|null $query OC query to run to get stats for this item's setting.
     * @param string|null $id    HTML ID, when $key is not enough to identify a form input.
     *
     * @return OriginalUrlsAnalysisItem
     */
    private function createItem(string $key, string $query = null, string $id = null): OriginalUrlsAnalysisItem
    {
        $this->items[$key] = new OriginalUrlsAnalysisItem($key, $id);

        if ($query !== null) {
            $result = $this->search($query);
            $this->items[$key]->setResultCount($result['hits']);
            $this->items[$key]->setResultUrl($this->getResultUrl($query));
            $this->addDomainsFoundInOriginalUrls($result);
        }

        return $this->items[$key];
    }

    private function getItem(string $key): OriginalUrlsAnalysisItem
    {
        return $this->items[$key];
    }

    /**
     * Compare two items and set recommendations for them based on the desired tolerance for discrepancy.
     *
     * @param string $key1        Key to define the first item
     * @param string $key2        Key to define the second item
     * @param float  $intolerance {@see OriginalUrlsAnalysis::MINORITY_SETTING_INTOLERANCE}
     */
    private function compare(string $key1, string $key2, float $intolerance): void
    {
        $item1 = $this->getItem($key1);
        $item2 = $this->getItem($key2);

        $count1 = $item1->getResultCount();
        $count2 = $item2->getResultCount();

        if ($count1 === 0 && $count2 === 0) {
            // There's nothing to compare!
            return;
        }

        $highest = ($count1 > $count2) ? $item1 : $item2;
        $lowest = ($count1 > $count2) ? $item2 : $item1;

        $highest->setRecommendedValue(true);
        $highest->setResultStatus(OriginalUrlsAnalysisItem::STATUS_GOOD);

        $proportion = $lowest->getResultCount() / $highest->getResultCount();
        if ($proportion === 0) {
            $lowest->setResultStatus(OriginalUrlsAnalysisItem::STATUS_NEUTRAL);
            $lowest->setRecommendedValue(false);
        } else if ($intolerance === 1.0) {
            $lowest->setResultStatus(OriginalUrlsAnalysisItem::STATUS_INCOMPATIBLE);
            $lowest->setRecommendedValue(false);
        } else if ($proportion < $intolerance) {
            $lowest->setResultStatus(OriginalUrlsAnalysisItem::STATUS_LOW);
            $lowest->setRecommendedValue(false);
        } else {
            $lowest->setResultStatus(OriginalUrlsAnalysisItem::STATUS_GOOD);
            $lowest->setRecommendedValue(true);
        }
    }

    private function createDomainsItem(): OriginalUrlsAnalysisItem
    {
        /** @var $covered - Number of articles whose OriginalUrls are covered by the domains found in $analysis->getMostFoundDomains(). */
        $covered = $this->analyzeDomainCoverage();

        /** @var $total - Number of articles whose OriginalUrls are using ANY domain. */
        $total = $this->getItem('extent_urls')->getResultCount();

        for ($i = 0; $i < self::DOMAIN_COVERAGE_MAX_EXTRA_CYCLES; $i++) {
            if ($covered >= $total) {
                break;
            }

            // Find some non-matching articles and evaluate the OriginalUrls in those.
            $result = $this->search($this->settings[PluginSettings::ORIGINAL_URLS_PROPERTY_NAME] . ':?* AND NOT (' . $this->lastCountQuery . ')');
            $addedDomains = $this->addDomainsFoundInOriginalUrls($result);
            if ($addedDomains === 0) {
                break;
            }

            // Redo the coverage analysis with the newfound additions.
            $covered = $this->analyzeDomainCoverage();
        }

        $domainsItem = $this->createItem('domains');
        $status = ($covered < $total) ? OriginalUrlsAnalysisItem::STATUS_INSUFFICIENT : OriginalUrlsAnalysisItem::STATUS_GOOD;
        $domainsItem->setResultStatus($status);
        $domainsItem->setRecommendedValue(implode(', ', array_keys($this->mostFoundDomains)));

        $domainsList = '<ol style="list-style-type: none">';
        foreach ($this->mostFoundDomains as $domain => $count) {
            $resultUrl = $this->getResultUrl($this->getQueryForUrlsBeginningWith($this->getUrlVariationsForDomain($domain)));
            $domainsList .= '<li>' . $domain . ' ' . OriginalUrlsAnalysisItem::resultLink($count, $resultUrl);
        }
        $domainsList .= '</ol>';

        $percentage = floor(100 * $covered / $total);
        $template = __('Covers %d articles (%d %%). There may be more.', REDIRECT_ORIGINAL_URLS_TEXT_DOMAIN);
        $domainsItem->setResultText(sprintf($template, $covered, $percentage) . $domainsList);

        return $domainsItem;
    }

    /**
     * Using the found domains, analyze how many articles they cover.
     * This will give a good idea of whether they might represent all the domains available.
     * (Without actually loading every single article.)
     *
     * This updates and sorts $this->mostFoundDomains.
     *
     * @return int The number of articles with at least one OriginalUrl that uses a domain from $this->mostFoundDomains.
     */
    private function analyzeDomainCoverage(): int
    {
        // If there are any manually entered domains in the settings, search for them as well.
        $domainsInSettings = $this->settings[PluginSettings::URL_SETTING_DOMAINS];
        foreach ($domainsInSettings as $domain) {
            if (!isset($this->mostFoundDomains[$domain])) {
                $this->mostFoundDomains[$domain] = 0;
            }
        }

        $urlVariations = [];
        foreach ($this->mostFoundDomains as $domain => $number) {
            $urlVariationsThisDomain = $this->getUrlVariationsForDomain($domain);
            $hits = $this->countArticlesWithOriginalUrlsBeginning($urlVariationsThisDomain);

            if ($hits === 0) {
                unset($this->mostFoundDomains[$domain]);
                continue;
            }

            $this->mostFoundDomains[$domain] = $hits;
            $urlVariations = array_merge($urlVariations, $urlVariationsThisDomain);
        }

        arsort($this->mostFoundDomains);

        if (count($urlVariations) === 0) {
            return 0;
        }

        // Finally, do a search with ALL the domains.
        return $this->countArticlesWithOriginalUrlsBeginning($urlVariations);
    }

    private function getResultUrl(string $query): string
    {
        return rtrim($this->ocApi->getOcBaseUrl(), '/') .
            '/search?properties=uuid,Headline,' . $this->settings[PluginSettings::ORIGINAL_URLS_PROPERTY_NAME] . '&q=' .
            urlencode($query);
    }

    /**
     * Adds domains to $this->mostFoundDomains.
     *
     * @param array $result OC result with articles.
     *
     * @return int The number of domains added.
     */
    private function addDomainsFoundInOriginalUrls(array $result): int
    {
        $domainsAdded = 0;

        /** @var OcArticle[] $articles */
        $articles = array_filter($result, static function ($item) {
            return ($item instanceof OcArticle);
        });
        foreach ($articles as $article) {

            // Only analyze each article once.
            if (in_array($article->get_value('uuid'), $this->articlesAnalyzed, true)) {
                continue;
            }

            foreach ($article->get($this->settings[PluginSettings::ORIGINAL_URLS_PROPERTY_NAME]) as $url) {

                // Extract domain.
                $components = parse_url($url);
                if (!isset($components['host'])) {
                    continue;
                }
                $domain = $components['host'];

                // Remove "www." subdomain, if any.
                if (strpos($domain, 'www.') === 0) {
                    $domain = substr($domain, 4);
                }

                // Increment counter.
                if (!isset($this->mostFoundDomains[$domain])) {
                    $this->mostFoundDomains[$domain] = 0;
                    $domainsAdded++;
                }
                $this->mostFoundDomains[$domain]++;
            }

            $this->articlesAnalyzed[] = $article->get_value('uuid');
        }

        return $domainsAdded;
    }

    /**
     * @param string $domain
     *
     * @return string[]
     */
    private function getUrlVariationsForDomain(string $domain): array
    {
        $urlVariations = ['https://' . $domain . '/'];
        OriginalUrlsRedirector::varyScheme($urlVariations, [
            PluginSettings::URL_SETTING_SCHEME_HTTPS => true,
            PluginSettings::URL_SETTING_SCHEME_HTTP  => true
        ]);
        OriginalUrlsRedirector::varyWwwSubdomain($urlVariations, [
            PluginSettings::URL_SETTING_WWW_SUBDOMAINS_WITH    => true,
            PluginSettings::URL_SETTING_WWW_SUBDOMAINS_WITHOUT => true
        ]);

        return $urlVariations;
    }

    /**
     * @param array $urlVariations
     *
     * @return string
     */
    private function getQueryForUrlsBeginningWith(array $urlVariations): string
    {
        array_walk($urlVariations, static function(string &$string) {
            $string = self::escapeSolrQuery($string) . '*';
        });

        return $this->settings[PluginSettings::ORIGINAL_URLS_PROPERTY_NAME] . ':' . implode(' OR ' . $this->settings[PluginSettings::ORIGINAL_URLS_PROPERTY_NAME] . ':', $urlVariations);
    }

    /**
     * @param string   $query
     *
     * @return array|string
     */
    private function search(string $query)
    {
        $params = [
            'q'            => $query,
            'contenttypes' => ['Article'],
            'properties'   => ['uuid', $this->settings[PluginSettings::ORIGINAL_URLS_PROPERTY_NAME]],
            'limit'        => self::LIMIT_PER_QUERY
        ];
        $useCache = false;
        $createArticle = false;

        $result = $this->ocApi->search($params, $useCache, $createArticle);

        if (count($result) === 0) {
            throw new OcSearchFailedException('Failed Article search with query "' . $params['q'] . '"');
        }

        return $result;
    }

    /**
     * @param string[] $originalUrls
     *
     * @return int
     */
    private function countArticlesWithOriginalUrlsBeginning(array $originalUrls): int
    {
        $params = [
            'q'            => $this->getQueryForUrlsBeginningWith($originalUrls),
            'contenttypes' => ['Article'],
            'properties'   => ['uuid'],
            'limit'        => 1
        ];
        $useCache = false;
        $createArticle = false;
        $result = $this->ocApi->search($params, $useCache, $createArticle);
        $this->lastCountQuery = $params['q'];

        return $result['hits'];
    }
}
