<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Admin\ProjectPluginPage;

use Infomaker\Everyware\Twig\View;
use Everyware\ProjectPlugin\Admin\Abstracts\ComponentsPage;

class WidgetsPage extends ComponentsPage
{
    protected static $slug = 'widgets';
    protected static $title = 'Widgets';

    public function renderPage(): void
    {
        View::render('@projectPlugin/admin/components/widgets', $this->getComponentsPageData([
            'description' => __('From here you can manage your custom widgets for the site.', $this->textDomain),
        ]));
    }
}
