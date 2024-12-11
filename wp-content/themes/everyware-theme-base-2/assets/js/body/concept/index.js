$(function() {

    const $buttonLoadMore = $('.concept-load-more');
    const $container = $('.articles-container');
    const uuid = $container.data('uuid');
    let postsCounter = $container.data('posts');

    const renderArticles = response => {
        const articleList = response.articleList || '';
        const totalFetchCount = response.totalFetchCount || 0;
        const hits = response.hits || 0;

        postsCounter = totalFetchCount;

        if(postsCounter >= hits) {
            $buttonLoadMore.remove();
        }

        $container.data('posts', postsCounter);
        $container.append(articleList);
    };

    const fetchArticles = () => {
        $buttonLoadMore.attr('data-status', 'loading');

        const ajaxData = {
            action: 'fetch_concept_posts',
            data: {
                uuid,
                start: postsCounter,
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
