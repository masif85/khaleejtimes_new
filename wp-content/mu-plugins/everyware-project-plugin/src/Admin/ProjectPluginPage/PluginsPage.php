<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Admin\ProjectPluginPage;

use Infomaker\Everyware\Twig\View;
use Everyware\ProjectPlugin\Admin\Abstracts\ComponentsPage;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

class PluginsPage extends ComponentsPage
{
    protected static $slug = 'plugins';
    protected static $title = 'Plugins';

    public function renderPage(): void
    {
        View::render('@projectPlugin/admin/components/plugins', $this->getComponentsPageData([
            'description' => __('From here you can manage your custom plugins for the site.', $this->textDomain),
        ]));
    }
}
