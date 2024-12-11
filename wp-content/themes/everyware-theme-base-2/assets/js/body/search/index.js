$(function() {

    const $buttonLoadMore = $('.btn--load-more');
    const $container = $('.articles-container');
    let action = $buttonLoadMore.data('action');
    let query = $container.data('query');
    let fetchedCounter = $container.data('fetched');

    const renderArticles = response => {
        const articleList = response.articleList || '';
        const totalFetchCount = response.totalFetchCount || 0;
        const hits = response.hits || 0;

        fetchedCounter = totalFetchCount;

        if(fetchedCounter >= hits) {
            $buttonLoadMore.remove();
        }

        $container.data('fetched', fetchedCounter);
        $container.append(articleList);
    };

    const fetchArticles = () => {
        $buttonLoadMore.attr('data-status', 'loading');

        const ajaxData = {
            action: action,
            data: {
                q: query,
                start: fetchedCounter,
            }
        };

        $.ajax({
            type : 'GET',
            url : infomaker.ajaxurl,
            data : ajaxData,
            success : response => renderArticles(response),
            error : error => console.log('error: ', error),
            complete: () => $buttonLoadMore.attr('data-status', 'done'),
        });
    };

    $buttonLoadMore.on('click', fetchArticles);
});
