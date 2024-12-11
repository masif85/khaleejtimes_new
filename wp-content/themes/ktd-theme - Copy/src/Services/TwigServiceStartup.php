<?php declare(strict_types=1);

namespace KTDTheme\Services;

use EuKit\Base\Interfaces\ThemeServiceStartup;
use Infomaker\Everyware\Support\Str;
use Infomaker\Everyware\Twig\ViewSetup;
use KTDTheme\TwigFunctions;

class TwigServiceStartup implements ThemeServiceStartup
{
    /**
     * Contains Wordpress functions that will be added to the list off functions
     * that will be available in the templates
     * function: [func_name] => [custom_name]
     *
     * @var array
     */
    protected $wpFunctions = [
        'get_header' => '',
        'language_attributes' => '',
        'get_bloginfo' => '',
        'get_footer' => '',
        'get_the_title' => '',
        'the_content' => '',
        'body_class' => '',
        'site_url' => '',
        'self_link' => '',
        'get_self_link' => '',
        'get_permalink' => '',
        'add_query_arg' => '',
        'wp_head' => '',
        'wp_footer' => '',
        'get_template_directory_uri' => '',
        'get_stylesheet_directory_uri' => '',
        'get_option' => '',
        '__' => '',
        '_x' => ''
    ];
    
    /**
     * @var ViewSetup
     */
    private $viewSetup;

    public function __construct(ViewSetup $viewSetup)
    {
        $this->viewSetup = $viewSetup;
    }

    public function setup(): void
    {
        $this->addWpFunctions();
        $this->registerFolders();
        $this->addTwigFunctions();
    }

    protected function addWpFunctions()
    {
        foreach ($this->wpFunctions as $wpFunction => $name) {
            $this->viewSetup->addWpFunction($wpFunction, $name);
        }
    }

    protected function registerFolders()
    {
        $this->viewSetup->registerTwigFolder('base', get_stylesheet_directory() . '/views');
        $this->viewSetup->registerTwigFolder('base', get_template_directory() . '/views');
    }

    protected function addTwigFunctions()
    {
        // All methods from TwigFunctions will be added to twig as global functions.
        // The method-name will be converted to snake_case to match PSR-1 for "global functions"
        // E.g. methodName => method_name
        $twigFunctions = new TwigFunctions();
        foreach ( get_class_methods( $twigFunctions ) as $funcName ) {
            $this->viewSetup->addFunction( Str::snake( $funcName ), [ $twigFunctions, $funcName ] );
        }
    }
}
