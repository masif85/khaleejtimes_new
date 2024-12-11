<?php declare(strict_types=1);

use Infomaker\Everyware\Twig\View;
use Everyware\RssFeeds\RssFeedPost;
use Everyware\RssFeeds\RssFeed;
use Everyware\RssFeeds\RssChannel;
use Everyware\RssFeeds\RssItem;

// Handle password-protected posts.
if ( post_password_required() ) {
    View::render('@rssFeedsPlugin/views/password-form.twig', [
        'headline' => 'Password required',
        'password_form' => get_the_password_form()
    ]);
    exit;
}
// Handle private posts.
if (get_post_status() === 'private' && !current_user_can('read_private_posts')) {
    View::render('@base/page/page-not-found');
    exit;
}

$feed = RssFeed::fromRssFeedPost(RssFeedPost::current());

$channel = new RssChannel(
    $feed->getTitle(),
    $feed->getLink(),
    $feed->getDescription()
);
$feed->addChannel($channel);

$ocArticles = $feed->getOcArticles();
foreach ($ocArticles as $ocArticle) {
    $item = RssItem::fromOcArticle($ocArticle, $feed->getItemSettings());
    $channel->addItem($item);
}

header('Content-Type: application/rss+xml');
echo $feed->toXml();
