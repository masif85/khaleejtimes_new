<?php
declare(strict_types=1);

namespace Unit\Everyware\Plugin\RedirectOriginalUrls;

use Everyware\Plugin\RedirectOriginalUrls\Exceptions\NoUrlSettingVariationException;
use Everyware\Plugin\RedirectOriginalUrls\OcMigratedUrlRepository;
use Everyware\Plugin\RedirectOriginalUrls\PluginSettings;
use Everyware\Plugin\RedirectOriginalUrls\OriginalUrlsRedirector;
use OcObject;
use RuntimeException;

/**
 * Class OriginalUrlsRedirectorTest
 * @package Unit\Everyware\Plugin\RedirectOriginalUrls
 */
class OriginalUrlsRedirectorTest extends RedirectOriginalUrlsTestCase
{
    public function testIs404CanBeMockedUp(): void
    {
        self::assertTrue(is_404());
        $this->mockUp404(false);
        self::assertFalse(is_404());
    }

    /**
     * Test that the default settings are as expected. Other tests here depend on that.
     */
    public function testDefaultSettings(): void
    {
        $settings = new FakePluginSettings(new FakeSettingsProvider());
        $expected = [
            PluginSettings::ORIGINAL_URLS_PROPERTY_NAME => 'OriginalUrls',
            PluginSettings::URL_SETTING_EXTENT => PluginSettings::URL_SETTING_EXTENT_URLS,
            PluginSettings::URL_SETTING_DOMAINS => [],
            PluginSettings::URL_SETTING_SCHEMES => [
                PluginSettings::URL_SETTING_SCHEME_HTTPS => true,
                PluginSettings::URL_SETTING_SCHEME_HTTP => true,
            ],
            PluginSettings::URL_SETTING_WWW_SUBDOMAINS => [
                PluginSettings::URL_SETTING_WWW_SUBDOMAINS_WITH => true,
                PluginSettings::URL_SETTING_WWW_SUBDOMAINS_WITHOUT => true,
            ],
            PluginSettings::URL_SETTING_TRAILING_SLASHES => [
                PluginSettings::URL_SETTING_TRAILING_SLASHES_WITH => true,
                PluginSettings::URL_SETTING_TRAILING_SLASHES_WITHOUT => true,
            ],
        ];

        self::assertEquals($expected, $settings->get());
    }

    public function testHandle404(): void
    {
        $url1 = 'http://www.example.com/foo/bar';
        $url2 = 'https://another-domain.io/?foo=bar&numbers=123';

        // Before mocking up any OriginalUrls, neither of these should generate a redirect.
        self::assertNull($this->getRedirectResult($url1));
        self::assertNull($this->getRedirectResult($url2));

        // Mockup the first URL.
        $article1 = $this->mockUpOriginalUrls('aaa', [$url1]);
        self::assertSame($article1->get_permalink(), $this->getRedirectResult($url1));
        self::assertNull($this->getRedirectResult($url2));

        // Mockup the second URL.
        $article2 = $this->mockUpOriginalUrls('bbb', [$url2]);
        self::assertSame($article1->get_permalink(), $this->getRedirectResult($url1));
        self::assertSame($article2->get_permalink(), $this->getRedirectResult($url2));
    }

    /**
     * @param array  $settingChanges
     * @param string $originalUrl
     * @param string $testingUrl
     * @param bool   $expectedMatch
     *
     * @dataProvider handle404WithVariedUrlsProvider
     */
    public function testHandle404WithVariedUrls(array $settingChanges, string $originalUrl, string $testingUrl, bool $expectedMatch): void
    {
        $this->mockUpSettings($settingChanges);
        $article = $this->mockUpOriginalUrls('aaa', [$originalUrl]);

        if ($expectedMatch) {
            self::assertEquals($article->get_permalink(), $this->getRedirectResult($testingUrl));
        } else {
            self::assertNull($this->getRedirectResult($testingUrl));
        }
    }

    public function handle404WithVariedUrlsProvider(): array
    {
        // Different settings
        $default = [];
        $withPaths = [
            PluginSettings::URL_SETTING_EXTENT => PluginSettings::URL_SETTING_EXTENT_PATHS,
        ];
        $onlyHttps = [
            PluginSettings::URL_SETTING_SCHEMES => [
                PluginSettings::URL_SETTING_SCHEME_HTTP => false,
            ],
        ];
        $onlyHttp = [
            PluginSettings::URL_SETTING_SCHEMES => [
                PluginSettings::URL_SETTING_SCHEME_HTTPS => false,
            ],
        ];
        $onlyWithWww = [
            PluginSettings::URL_SETTING_WWW_SUBDOMAINS => [
                PluginSettings::URL_SETTING_WWW_SUBDOMAINS_WITHOUT => false,
            ],
        ];
        $onlyWithoutWww = [
            PluginSettings::URL_SETTING_WWW_SUBDOMAINS => [
                PluginSettings::URL_SETTING_WWW_SUBDOMAINS_WITH => false,
            ],
        ];
        $onlyWithTrailingSlash = [
            PluginSettings::URL_SETTING_TRAILING_SLASHES => [
                PluginSettings::URL_SETTING_TRAILING_SLASHES_WITHOUT => false,
            ],
        ];
        $onlyWithoutTrailingSlash = [
            PluginSettings::URL_SETTING_TRAILING_SLASHES => [
                PluginSettings::URL_SETTING_TRAILING_SLASHES_WITH => false,
            ],
        ];

        // Different URLs and paths
        $path1                   = '/foo/bar';
        $url1                    = 'http://www.example.com/foo/bar';
        $url1WithHttps           = 'https://www.example.com/foo/bar';
        $url1WithoutWww          = 'http://example.com/foo/bar';
        $url1WithTrailingSlash   = 'http://www.example.com/foo/bar/';
        $url1WithAllVariations   = 'https://example.com/foo/bar/';
        $url1ButDifferentDomain  = 'http://www.example2.com/foo/bar';
        $url2                    = 'http://example.com/foo/bar/close-but-no-cigar';

        return [
            [$default, $url1, $url1WithHttps,          true ],
            [$default, $url1, $url1WithoutWww,         true ],
            [$default, $url1, $url1WithTrailingSlash,  true ],
            [$default, $url1, $url1WithAllVariations,  true ],
            [$default, $url1, $url1ButDifferentDomain, false],
            [$default, $url1, $url2,                   false],

            [$withPaths, $path1, $url1WithHttps,          true ],
            [$withPaths, $path1, $url1WithoutWww,         true ],
            [$withPaths, $path1, $url1WithTrailingSlash,  true ],
            [$withPaths, $path1, $url1WithAllVariations,  true ],
            [$withPaths, $path1, $url1ButDifferentDomain, true ],
            [$withPaths, $path1, $url2,                   false],

            [$onlyHttps, $url1,          $url1,          false],
            [$onlyHttps, $url1,          $url1WithHttps, false],
            [$onlyHttps, $url1WithHttps, $url1WithHttps, true ],
            [$onlyHttps, $url1WithHttps, $url1,          true ],

            [$onlyHttp, $url1,          $url1,          true ],
            [$onlyHttp, $url1,          $url1WithHttps, true ],
            [$onlyHttp, $url1WithHttps, $url1WithHttps, false],
            [$onlyHttp, $url1WithHttps, $url1,          false],

            [$onlyWithWww, $url1,           $url1,           true ],
            [$onlyWithWww, $url1,           $url1WithoutWww, true ],
            [$onlyWithWww, $url1WithoutWww, $url1,           false],
            [$onlyWithWww, $url1WithoutWww, $url1WithoutWww, false],

            [$onlyWithoutWww, $url1,           $url1,           false],
            [$onlyWithoutWww, $url1,           $url1WithoutWww, false],
            [$onlyWithoutWww, $url1WithoutWww, $url1,           true ],
            [$onlyWithoutWww, $url1WithoutWww, $url1WithoutWww, true ],

            [$onlyWithTrailingSlash, $url1,                  $url1,                  false],
            [$onlyWithTrailingSlash, $url1,                  $url1WithTrailingSlash, false],
            [$onlyWithTrailingSlash, $url1WithTrailingSlash, $url1,                  true ],
            [$onlyWithTrailingSlash, $url1WithTrailingSlash, $url1WithTrailingSlash, true ],

            [$onlyWithoutTrailingSlash, $url1,                  $url1,                  true ],
            [$onlyWithoutTrailingSlash, $url1,                  $url1WithTrailingSlash, true ],
            [$onlyWithoutTrailingSlash, $url1WithTrailingSlash, $url1,                  false],
            [$onlyWithoutTrailingSlash, $url1WithTrailingSlash, $url1WithTrailingSlash, false],
        ];
    }

    public function testHandle404WithDifferentDomains(): void
    {
        $domain1 = 'example1.com';
        $domain2 = 'example2.com';
        $domain3 = 'example3.com';

        $path = '/foo/bar';

        $url1 = 'https://' . $domain1 . $path;
        $url2 = 'https://' . $domain2 . $path;
        $url3 = 'https://' . $domain3 . $path;

        $article1 = $this->mockUpOriginalUrls('aaa', [$url1]);
        $article2 = $this->mockUpOriginalUrls('bbb', [$url2]);

        /**
         * $url1 and $url2 will be matched with the article that has that exact OriginalUrl.
         * But $url3, which has no exact domain match, will fall back to whichever matching domain that is mentioned first in the settings.
         */

        $this->mockUpSettings([
            PluginSettings::URL_SETTING_DOMAINS => [ $domain1, $domain2, $domain3 ],
        ]);

        self::assertEquals($article1->get_permalink(), $this->getRedirectResult($url1));
        self::assertEquals($article2->get_permalink(), $this->getRedirectResult($url2));
        self::assertEquals($article1->get_permalink(), $this->getRedirectResult($url3));

        $this->mockUpSettings([
            PluginSettings::URL_SETTING_DOMAINS => [ $domain3, $domain2, $domain1 ],
        ]);

        self::assertEquals($article1->get_permalink(), $this->getRedirectResult($url1));
        self::assertEquals($article2->get_permalink(), $this->getRedirectResult($url2));
        self::assertEquals($article2->get_permalink(), $this->getRedirectResult($url3));
    }

    public function testHandle404WhenThereIsNo404(): void
    {
        $this->mockUp404(false);

        new OriginalUrlsRedirector($this->repository);

        // If the above did not throw an exception, the test passed.
        self::assertTrue(true);
    }

    public function testHandle404WithNoMatches(): void
    {
        new OriginalUrlsRedirector($this->repository);

        // If the above did not throw an exception, the test passed.
        self::assertTrue(true);
    }

    /**
     * @param string      $url
     * @param string|null $domain
     * @param string[]    $expectedResult Items do not have to be in any particular order.
     *
     * @dataProvider createUrlVariationsDataProvider
     */
    public function testCreateUrlVariations(string $url, array $settingChanges, ?string $domain, array $expectedResult): void
    {
        $settings = new FakePluginSettings(
            new FakeSettingsProvider($settingChanges)
        );
        $urls = OriginalUrlsRedirector::createUrlVariations($url, $settings->get(), $domain);

        foreach ($urls as $url) {
            self::assertContains($url, $expectedResult);
        }
        foreach ($expectedResult as $url) {
            self::assertContains($url, $urls);
        }
    }

    public function createUrlVariationsDataProvider(): array
    {
        // Settings
        $default = [];

        return [
            ['http://d/foo', $default, null, [
                'https://www.d/foo/',
                'https://www.d/foo',
                'https://d/foo/',
                'https://d/foo',
                'http://www.d/foo/',
                'http://www.d/foo',
                'http://d/foo/',
                'http://d/foo',
            ]],
            ['http://e', $default, null, [
                'https://www.e/',
                'https://www.e',
                'https://e/',
                'https://e',
                'http://www.e/',
                'http://www.e',
                'http://e/',
                'http://e',
            ]],
            ['https://f/bar/', $default, 'navigaglobal.com', [
                'https://www.navigaglobal.com/bar/',
                'https://www.navigaglobal.com/bar',
                'https://navigaglobal.com/bar/',
                'https://navigaglobal.com/bar',
                'http://www.navigaglobal.com/bar/',
                'http://www.navigaglobal.com/bar',
                'http://navigaglobal.com/bar/',
                'http://navigaglobal.com/bar',
            ]],
        ];
    }

    /**
     * @param string[]      $urls
     * @param bool          $useHttps
     * @param bool          $useHttp
     * @param string[]|null $expectedResult Items do not have to be in any particular order.
     * @param string|null   $expectedExceptionMessage
     *
     * @dataProvider varySchemeDataProvider
     */
    public function testVaryScheme(array $urls, bool $useHttps, bool $useHttp, array $expectedResult = null, string $expectedExceptionMessage = null): void
    {
        $setting = [
            PluginSettings::URL_SETTING_SCHEME_HTTPS => $useHttps,
            PluginSettings::URL_SETTING_SCHEME_HTTP  => $useHttp
        ];
        $this->varyTwoUrlSettingsThatCannotBothBeFalse('varyScheme', $urls, $setting, $expectedResult, $expectedExceptionMessage);
    }

    public function varySchemeDataProvider(): array
    {
        $urls = ['http://d/foo', 'https://www.d/bar/'];
        return [
            [$urls, false, false, null, 'No URL scheme defined.'],
            [$urls, false, true, ['http://d/foo', 'http://www.d/bar/']],
            [$urls, true, false, ['https://d/foo', 'https://www.d/bar/']],
            [$urls, true, true, ['https://d/foo', 'http://d/foo', 'https://www.d/bar/', 'http://www.d/bar/']],
        ];
    }

    /**
     * @param string[]      $urls
     * @param bool          $withWww
     * @param bool          $withoutWww
     * @param string[]|null $expectedResult Items do not have to be in any particular order.
     * @param string|null   $expectedExceptionMessage
     *
     * @dataProvider varyWwwSubdomainDataProvider
     */
    public function testVaryWwwSubdomain(array $urls, bool $withWww, bool $withoutWww, array $expectedResult = null, string $expectedExceptionMessage = null): void
    {
        $setting = [
            PluginSettings::URL_SETTING_WWW_SUBDOMAINS_WITH => $withWww,
            PluginSettings::URL_SETTING_WWW_SUBDOMAINS_WITHOUT => $withoutWww
        ];
        $this->varyTwoUrlSettingsThatCannotBothBeFalse('varyWwwSubdomain', $urls, $setting, $expectedResult, $expectedExceptionMessage);
    }

    public function varyWwwSubdomainDataProvider(): array
    {
        $urls = ['http://d/foo', 'https://www.d/bar/'];
        return [
            [$urls, false, false, null, 'No www subdomain setting defined.'],
            [$urls, false, true, ['http://d/foo', 'https://d/bar/']],
            [$urls, true, false, ['http://www.d/foo', 'https://www.d/bar/']],
            [$urls, true, true, ['http://d/foo', 'http://www.d/foo', 'https://d/bar/', 'https://www.d/bar/']],
        ];
    }

    /**
     * @param string[]      $urls
     * @param bool          $withSlash
     * @param bool          $withoutSlash
     * @param string[]|null $expectedResult Items do not have to be in any particular order.
     * @param string|null   $expectedExceptionMessage
     *
     * @dataProvider varyTrailingSlashDataProvider
     */
    public function testVaryTrailingSlash(array $urls, bool $withSlash, bool $withoutSlash, array $expectedResult = null, string $expectedExceptionMessage = null): void
    {
        $setting = [
            PluginSettings::URL_SETTING_TRAILING_SLASHES_WITH => $withSlash,
            PluginSettings::URL_SETTING_TRAILING_SLASHES_WITHOUT => $withoutSlash
        ];
        $this->varyTwoUrlSettingsThatCannotBothBeFalse('varyTrailingSlash', $urls, $setting, $expectedResult, $expectedExceptionMessage);
    }

    public function varyTrailingSlashDataProvider(): array
    {
        $urls = ['http://d/foo', 'https://www.d/bar/', 'http://d'];

        return [
            [$urls, false, false, null, 'No trailing slash setting defined.'],
            [$urls, false, true, ['http://d/foo', 'https://www.d/bar', 'http://d']],
            [$urls, true, false, ['http://d/foo/', 'https://www.d/bar/', 'http://d/']],
            [$urls, true, true, ['http://d/foo/', 'https://www.d/bar/', 'http://d/', 'http://d/foo', 'https://www.d/bar', 'http://d']],
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->ocApi = new FakeOcAPI();
        $this->repository = new OcMigratedUrlRepository($this->ocApi);

        $this->mockUp404(true);
        $this->mockUpCurrentUrl('https://example.com/');
    }

    /**
     * @param bool $response Set what is_404() should return next time.
     */
    private function mockUp404(bool $response): void
    {
        is_404($response);
    }

    /**
     * Emulate running {@see OriginalUrlsRedirector} and see if it redirects to anything.
     *
     * @param string $inputUrl
     *
     * @return string|null  URL to redirect to, or NULL if no URL was found.
     */
    private function getRedirectResult(string $inputUrl): ?string
    {
        $this->mockUpCurrentUrl($inputUrl);

        try {
            new OriginalUrlsRedirector($this->repository);
        } catch (RuntimeException $e) {
            $result = preg_match('/^Called wp_safe_redirect with arguments `([^`]+)`/', $e->getMessage(), $regs);

            return ($result === 1) ? $regs[1] : null;
        }

        return null;
    }

    /**
     * Set $_REQUEST variables to mock up what {@see OriginalUrlsRedirector::getCurrentUrl()} will return.
     *
     * @param string $url
     */
    private function mockUpCurrentUrl(string $url): void
    {
        $parts = parse_url($url);

        $_SERVER['HTTPS'] = ($parts['scheme'] === 'https') ? 'on' : null;
        $_SERVER['HTTP_HOST'] = $parts['host'];
        $_SERVER['REQUEST_URI'] = $parts['path'];
        if (isset($parts['query'])) {
            $_SERVER['REQUEST_URI'] .= '?' . $parts['query'];
        }
    }

    private function mockUpSettings(array $settingChanges): void
    {
        $this->repository->setUrlSettings(new FakePluginSettings(
            new FakeSettingsProvider($settingChanges)
        ));
    }

    /**
     * Set OriginalUrls value to an article matched by $articleUuid, and return the article.
     *
     * @param string $articleUuid
     * @param array  $originalUrls
     *
     * @return OcObject
     */
    private function mockUpOriginalUrls(string $articleUuid, array $originalUrls): OcObject
    {
        $article = $this->ocApi->get_single_object($articleUuid);
        $article->set('OriginalUrls', $originalUrls);

        return $article;
    }

    /**
     * @param string      $methodName
     * @param string[]    $urls
     * @param bool[]      $setting
     * @param array|null  $expectedResult
     * @param string|null $expectedExceptionMessage
     */
    private function varyTwoUrlSettingsThatCannotBothBeFalse(string $methodName, array $urls, array $setting, array $expectedResult = null, string $expectedExceptionMessage = null): void
    {
        if ($expectedExceptionMessage !== null) {
            $this->expectException(NoUrlSettingVariationException::class);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        OriginalUrlsRedirector::{$methodName}($urls, $setting);

        if ($expectedResult === null) {
            return;
        }

        foreach ($urls as $url) {
            self::assertContains($url, $expectedResult);
        }
        foreach ($expectedResult as $url) {
            self::assertContains($url, $urls);
        }
    }

    /**
     * @var OcMigratedUrlRepository
     */
    private $repository;

    /**
     * @var FakeOcAPI
     */
    private $ocApi;
}
