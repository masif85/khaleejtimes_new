<?php declare(strict_types=1);

namespace KTDTheme;

use Infomaker\Everyware\Twig\View;
use Exception;
use Infomaker\Everyware\Support\NewRelicLog;

class StorytellingArticleBodyPresentation extends ArticleBodyPresentation
{
    protected function generateArticlePart($part, $data): string
    {
        try {
            return View::generate("@base/article/storytelling/{$part}", $data);
        } catch (Exception $e) {
            NewRelicLog::error('Failed to generate storytelling article body part', $e);
        }

        return '';
    }
}
