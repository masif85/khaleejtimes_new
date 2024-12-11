<?php

use Everyware\Everyboard\DeprecationHandler;
use Everyware\Everyboard\OcApiHandler;
use Everyware\Everyboard\OcArticleProvider;

class EveryBoard_Renderer
{
    private $main_site_id;
    private $active_device;
    private $eb_fw_helper;
    private $env_settings;
    private static $widget_ids = [];
    private static $widget_counter = 0;

    private $max_cols;
    private $nested_col_sizes = [];
    private $nested_board_counter = 0;
    private $content_container_array;
    private $nested_content_container_arrays = [];


    /**
     * Class Constructor
     */
    public function __construct()
    {
        $this->active_device = $this->set_active_device();
        $this->eb_fw_helper = new EveryBoard_Framework_Helper();
        $this->env_settings = new EnvSettings();
        $this->max_cols = $this->eb_fw_helper->eb_max_columns;
        $this->main_site_id = get_current_blog_id();
        $this->content_container_array = apply_filters('ew_content_container_fill', []);
    }

    /**
     * Function to Initialize the rendering of a Board
     * Used in "The Content Filter" to replace the WP-content with Board Content
     *
     * @param          $board_json
     * @param int|null $site_id
     *
     * @return string Board HTML
     */
    public function render($board_json, int $site_id = null): string
    {
        $output = $this->render_internal($board_json, $site_id);
        EveryBoard_WidgetPrefab::get_instance()->reset_widget_sidebars();

        return $output;
    }

    /**
     * Internal render function used by linked boards and such, this to determine when the final render is called
     * in order to reset widgets and such
     *
     * @param array|string $board_json
     * @param int|null     $site_id
     *
     * @return string
     *
     */
    private function render_internal($board_json, int $site_id = null): string
    {
        // Extract board json from array
        $board_json = is_array($board_json) ? implode($board_json) : $board_json;

        if (empty($board_json)) {
            return '';
        }

        if (null === $site_id) {
            $site_id = get_current_blog_id();
        }

        $board_json_arr = json_decode($board_json, true);
        $board_json_obj = json_decode($board_json);

        $board_classes = [$this->eb_fw_helper->board_wrapper_classes()];

        if (array_key_exists('settings', $board_json_arr) && ! empty($board_json_arr['settings']['cssclass'])) {
            $board_classes[] = $board_json_arr['settings']['cssclass'];
        }

        $board_content = '';

        if (isset($board_json_obj->rows)) {
            foreach ((array)$board_json_obj->rows as $row) {
                $board_content .= $this->render_structure_item($row, $site_id);
            }
        }

        return $this->wrap_content('section', implode(' ', $board_classes), $board_content);
    }

    private function render_structure_item($item, int $site_id = null, array $page_col_size = []): string
    {
        if ( ! $this->should_item_render($item)) {
            return '';
        }

        $return_html = '';
        $css = $this->generate_item_css($item->settings, $item->type);

        if ( ! empty($page_col_size) && $this->should_have_content_size_classes($item)) {
            $css = trim($css);
            $css .= ' ' . $this->eb_fw_helper->get_content_size_classes($page_col_size);
        }

        if (isset($item->type)) {
            switch ($item->type) {
                case 'row':
                    $return_html .= $this->render_row($item, $css, $site_id);
                    break;
                case 'col':
                    $return_html .= $this->render_col($item, $css, $site_id);
                    break;
                case 'widget':
                    $return_html .= $this->render_widget($item, $css, $site_id);
                    break;
                case 'oc_article':
                    $return_html .= $this->render_article($item, $css);
                    break;
                case 'linked_widget':
                    $return_html .= $this->render_linked_widget($item, $css, $site_id);
                    break;
                case 'template_board_widget':
                    $return_html .= $this->render_template_board_widget($item, $css, $site_id);
                    break;
                case 'embed_widget':
                    $return_html .= $this->render_embed_widget($item, $css);
                    break;
                case 'articlelist_widget':
                    $return_html .= $this->render_articlelist_widget($item, $css, $site_id);
                    break;
                case 'oclist_widget':
                    $return_html .= $this->render_oclist_widget($item, $css);
                    break;
                case 'contentcontainer_widget':
                    $return_html .= $this->render_contentcontainer_widget($item, $css);
                    break;
                default:
                    error_log(sprintf('Unknown board item of type "%s" will not be rendered', $item->type));
            }
        }

        return $return_html;
    }

    private function render_row($item, string $css, int $site_id = null): string
    {
        $return_html = '';

        if (isset($item->cols)) {
            foreach ($item->cols as $col) {
                $return_html .= $this->render_structure_item($col, $site_id);
            }
        }

        if ($this->eb_fw_helper->need_clearfix()) {
            $return_html .= '<div class="clearfix"></div>';
        }

        $row_classes = $this->eb_fw_helper->get_row_classes();

        $css = trim($css);
        if ( ! empty($css)) {
            $row_classes .= ' ' . $css;
        }

        return $this->wrap_content('div', $row_classes, $return_html);
    }

    private function calculate_page_col_size(array $nested_col_sizes)
    {
        # Remove the top layer and use as start values.
        $page_col_size = array_shift($nested_col_sizes);

        # Iterate threw the nested boards column sizes
        foreach ($nested_col_sizes as $board_col_size) {

            # Iterate threw the device-specific sizes
            foreach ($board_col_size as $device => $col_size) {

                $col_size = (int)$col_size; # make sure we are using int:s

                # Only recalculate if the column is less than max-size
                if ($col_size < $this->max_cols) {

                    $col_size_in_percent = $col_size / $this->max_cols;
                    $new_size = round($page_col_size[$device] * $col_size_in_percent, 0);
                    $page_col_size[$device] = ($new_size < 1) ? 1 : $new_size;
                }
            }
        }

        return $page_col_size;
    }

    private function render_col($item, string $css, int $site_id = null): string
    {
        # Save the sizes of the current column
        $current_col_size = [
            'mobile' => $item->settings->devices->mobile->colspan,
            'tablet' => $item->settings->devices->tablet->colspan,
            'smallscreen' => $item->settings->devices->smallscreen->colspan,
            'largescreen' => $item->settings->devices->largescreen->colspan
        ];
        $this->nested_col_sizes[$this->nested_board_counter] = $current_col_size;
        $page_col_size = ($this->nested_board_counter > 0) ? $this->calculate_page_col_size($this->nested_col_sizes) : $current_col_size;

        // If a wrapper is present, the css classes should only be applied to the wrapper, not the column.
        $col_css = $css;
        if ( ! isset($item->settings->hiddenwrapper) || $item->settings->hiddenwrapper !== 'true') {
            $col_css = '';
        }

        $return_html = $this->eb_fw_helper->col_html($current_col_size, $col_css, $page_col_size);

        if ( ! isset($item->settings->hiddenwrapper) || $item->settings->hiddenwrapper !== 'true') {
            $return_html .= '<div class="' . $css . '">';
        }

        if (isset($item->content)) {
            foreach ($item->content as $content) {

                if (isset($content->settings) && is_string($content->settings) && $content->settings !== '') {
                    $content->settings = json_decode($content->settings); // TODO: WTF?!
                }

                // Inherit the settings from content if none present
                if (( ! isset($content->settings->layout) && isset($item->settings->layout)) || (isset($content->settings->layout) && $content->settings->layout === 'None' && isset($item->settings->layout))) {

                    if (is_object($content->settings)) {
                        $content->settings->layout = $item->settings->layout;
                    }
                }

                $return_html .= $this->render_structure_item($content, $site_id, $page_col_size);
            }
        }

        if ( ! isset($item->settings->hiddenwrapper) || $item->settings->hiddenwrapper !== 'true') {
            $return_html .= '</div>';
            $return_html .= '</div>';
        } else {
            $return_html .= '</div>';
        }

        return $return_html;
    }

    private function render_article($item, string $css): string
    {
        $article = EveryBoard_Article_Helper::get_oc_article($item->oc_uuid);

        return $article instanceof OcArticle ? $this->render_article_object($article, $item, $css) : '';
    }

    private function render_article_object(OcArticle $article, $item, $css): string
    {
        if ( ! $this->should_article_render($article)) {
            return '';
        }

        $layout = $item->settings->layout ?? 'None';

        if ($layout === 'None') {
            $layout = 'templates/ocarticle-default.php';
        }

        $template_file = EveryBoard_Settings::get_template_path($layout);

        if ( ! file_exists($template_file)) {
            trigger_error("Missing article template file: {$template_file}");

            return '';
        }

        ob_start();
        include $template_file;
        $html = ob_get_clean();
        $classes = 'rendered_board_article ' . trim($css);

        return "<div class=\"$classes\">$html</div>";
    }

    public function render_widget($widget, string $css, int $site_id = null): string
    {
        if ( ! isset($widget->widget_id)) {
            return '';
        }

        $widget_info = $this->widget_instance_info($widget->widget_id, $site_id);

        if (empty($widget_info)) {
            return "";
        }

        // Check if the widget has already been rendered, in that case prepend it's id to make it unique.
        if (in_array($widget->widget_id, self::$widget_ids, true)) {
            $widget->widget_id = 'eb' . self::$widget_counter . '_' . $widget->widget_id;
            self::$widget_counter++;
        } else {
            self::$widget_ids[] = $widget->widget_id;
        }

        $widget_info['settings']['id'] = $widget->widget_id;

        ob_start();
        the_widget($widget_info['classname'], $widget_info['settings']);
        $html = ob_get_clean();
        $classes = 'rendered_board_widget ' . trim($css);

        return "<div class=\"$classes\" id=\"$widget->widget_id\">$html</div>";
    }

    private function render_embed_widget($widget, string $css): string
    {
        $content = $widget->settings->content ?? '';

        if (empty($content)) {
            return '';
        }

        ob_start();
        echo do_shortcode(base64_decode($content));
        $html = ob_get_clean();
        $classes = 'rendered_board_widget ' . trim($css);

        return "<div class=\"$classes\">$html</div>";
    }

    private function render_linked_widget($board, string $css, int $site_id = null): string
    {
        return $this->render_nested_board($board, 'rendered_board_linked_widget ' . trim($css), $site_id);
    }

    private function render_template_board_widget($board, string $css, int $site_id = null): string
    {
        $data_feed = $this->load_from_data_source($board->settings);

        return $this->render_nested_board($board, 'rendered_board_template_widget ' . trim($css), $site_id,
            $data_feed);
    }

    private function load_from_data_source(object $settings): array
    {
        // For now we settle with fetching 100 articles if no limit is set.
        $limit = ! empty($settings->oc_query_limit) ? (int)$settings->oc_query_limit : 100;
        $start = (int)$settings->oc_query_start;

        try {
            switch ($settings->data_source) {
                case 'list':
                    return OcList::get_articles((string)$settings->list, $start, $limit);
                case 'query':
                    return OcArticleProvider::create()->getArticlesByQuery(
                        (string)$settings->oc_query,
                        $limit,
                        max(($start - 1), 0), // Convert start to match OC start option
                        (string)$settings->oc_query_sort
                    );
                default:
                    throw new UnexpectedValueException('Unknown data source `' . $settings->data_source . '`');
            }
        } catch (Exception $e) {
            error_log('Failed to render board widget: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Helper method for render_linked_widget() and render_template_board_widget().
     *
     * @param Object      $board
     * @param string      $css
     * @param int|null    $site_id
     * @param OcArticle[] $data_feed
     *
     * @return string
     */
    private function render_nested_board($board, string $css, int $site_id = null, array $data_feed = null): string
    {
        $content = '';

        // Set up the differences between local boards and network boards.
        $json_source = 'everyboard_json';
        if (isset($board->settings->board_id) && $board->settings->board_id !== "0") {

            $board_id = $board->settings->board_id;
            $render_on_source = false;

            // Use the temp JSON if in preview
            if (isset($_GET['board_preview']) && $_GET['board_preview'] == 'true') {
                $json_source = 'everyboard_json_temp';
            }

        } else {
            if (isset($board->settings->network_board_id, $board->settings->network_board_site_id) && $board->settings->network_board_id !== "0" && $board->settings->network_board_site_id !== "0") {

                $board_id = (int)$board->settings->network_board_id;
                $site_id = (int)$board->settings->network_board_site_id;
                $render_on_source = (isset($board->settings->network_board_source_render) && $board->settings->network_board_source_render === 'true');

            } else {

                return '';
            }
        }

        $this->increase_board_nesting($data_feed);

        if (isset($site_id)) {
            switch_to_blog($site_id);
        }

        $board_json = get_post_custom_values($json_source, $board_id);

        // If the board should NOT be rendered on source site change back to the current site BEFORE render.
        if (isset($site_id) && ! $render_on_source) {
            restore_current_blog();
        }

        /** TODO: Why are we echo:ing here? Is this needed the blog switch? */
        /** TODO: To properly support grid nesting rowes should be the direct child of the column. We should remove the board wrapper on nested boards and start with the rows. */
        ob_start();
        echo '<div class="' . trim($css) . '">';
        echo $this->render_internal($board_json, $site_id);
        echo '</div>';
        $content .= ob_get_clean();

        // If the board should be rendered on source site change back to the current site AFTER render.
        if (isset($site_id) && $render_on_source) {
            restore_current_blog();
        }

        // We have to reset the nested board counter after we used it.
        $this->decrease_board_nesting();

        return $content;
    }

    /**
     * @param OcArticle[]|null $data_feed
     */
    private function increase_board_nesting(array $data_feed = null): void
    {
        $this->nested_board_counter++;
        $this->nested_content_container_arrays[$this->nested_board_counter] = $data_feed;
    }

    private function decrease_board_nesting(): void
    {
        unset($this->nested_col_sizes[$this->nested_board_counter], $this->nested_content_container_arrays[$this->nested_board_counter]);
        $this->nested_board_counter--;
    }

    /**
     * Renders the articles from the deprecated "Everylist"
     *
     * @param          $item
     * @param string   $css
     * @param int|null $site_id
     *
     * @return string
     * @deprecated as of v2.2.0 to be removed in v3
     */
    private function render_articlelist_widget($item, string $css, int $site_id = null): string
    {
        $content = '';

        if ( ! EveryBoard_Global_Settings::is_everylist_active()) {
            return '';
        }

        DeprecationHandler::log(__CLASS__ . '::render_articlelist_widget is deprecated as of infomaker/everyboard v2.2.0 to be removed in v3.');

        if (isset($item->settings->list) && $item->settings->list !== '0') {
            $list = (int)$item->settings->list;
            $listposition = (int)$item->settings->listposition;
            $limit = isset($item->settings->listlimit) ? (int)$item->settings->listlimit : 1;
            $listarticle = null;
            $listarticles = null;

            $switched = false;
            $original_blog_id = get_current_blog_id();

            if (isset($site_id)) {
                if ($original_blog_id !== $site_id) {
                    $switched = true;
                    switch_to_blog($site_id);
                }
            }

            if ($limit > 1) {
                $listarticles = EveryList::get_articles_from_list($list, $listposition, $limit);
            } else {
                $listarticle = EveryList::get_article_from_list($list, $listposition);
            }

            if ($switched) {
                restore_current_blog();
            }

            if ($listarticle !== null) {
                if ($switched || get_current_blog_id() !== $this->main_site_id) {
                    $article = EveryBoard_Article_Helper::force_fetch_search_oc_article($listarticle->get_value('uuid'));
                    $content = $this->render_article_object($article, $item, $css);
                } else {
                    $item->oc_uuid = $listarticle->get_value('uuid');
                    $content = $this->render_article($item, $css);
                }
            } else {
                if ($listarticles !== null) {
                    $content = '';
                    if ($switched || get_current_blog_id() !== $this->main_site_id) {
                        foreach ($listarticles as $larticle) {
                            $article = EveryBoard_Article_Helper::force_fetch_search_oc_article($larticle->get_value('uuid'));
                            $content .= $this->render_article_object($article, $item, $css);
                        }
                    } else {
                        foreach ($listarticles as $article) {
                            $item->oc_uuid = $article->get_value('uuid');
                            $content .= $this->render_article($item, $css);
                        }
                    }
                }
            }


        } else {
            if (isset($item->settings->network_list, $item->settings->network_list_site_id) && $item->settings->network_list !== 0 && $item->settings->network_list_site_id !== '') {

                $list = (int)$item->settings->network_list;
                $listposition = (int)$item->settings->listposition;
                $limit = isset($item->settings->listlimit) ? (int)$item->settings->listlimit : 1;
                $network_list_site = (int)$item->settings->network_list_site_id;
                $listarticle = null;
                $listarticles = null;

                $switched = false;
                $inner_switched = false;
                $current_blog_id = get_current_blog_id();

                if (isset($site_id) && $current_blog_id !== $site_id) {
                    switch_to_blog($site_id);
                    $switched = true;
                }

                if ($network_list_site !== get_current_blog_id()) {
                    switch_to_blog($network_list_site);
                    $inner_switched = true;
                }

                if ($limit > 1) {
                    $listarticles = EveryList::get_articles_from_list($list, $listposition, $limit);
                } else {
                    $listarticle = EveryList::get_article_from_list($list, $listposition);
                }

                if ($inner_switched) {
                    restore_current_blog();
                }

                if ($switched) {
                    restore_current_blog();
                }

                if ($listarticle !== null) {
                    if ($switched || $inner_switched || get_current_blog_id() !== $this->main_site_id) {
                        $article = EveryBoard_Article_Helper::force_fetch_oc_article($listarticle->get_value('uuid'));
                        $content = $this->render_article_object($article, $item, $css);
                    } else {
                        $item->oc_uuid = $listarticle->get_value('uuid');
                        $content = $this->render_article($item, $css);
                    }
                } else {
                    if ($listarticles !== null) {
                        $content = '';
                        if ($switched || $inner_switched || get_current_blog_id() !== $this->main_site_id) {
                            foreach ($listarticles as $larticle) {
                                $article = EveryBoard_Article_Helper::force_fetch_oc_article($larticle->get_value('uuid'));
                                $content .= $this->render_article_object($article, $item, $css);
                            }
                        } else {
                            foreach ($listarticles as $article) {
                                $item->oc_uuid = $article->get_value('uuid');
                                $content .= $this->render_article($item, $css);
                            }
                        }
                    }
                }
            }
        }

        return $content;
    }

    private function render_oclist_widget($item, string $css): string
    {
        if ( ! EveryBoard_Global_Settings::is_oclist_active()) {
            return '';
        }

        if ( ! isset($item->settings->list) || $item->settings->list === '0') {
            return '';
        }

        $list = $item->settings->list;
        $listposition = (int)$item->settings->listposition;
        $limit = isset($item->settings->listlimit) ? (int)$item->settings->listlimit : 1;

        $articles = OcList::get_articles($list, $listposition, $limit);

        $content = '';

        foreach ($articles as $article) {
            $content .= $this->render_article_object($article, $item, $css);
        }

        return $content;
    }

    private function render_contentcontainer_widget($item, string $css): string
    {
        $content = '';

        if (isset($item->settings->position)) {
            $pos = (int)$item->settings->position;
            $limit = isset($item->settings->limit) ? (int)$item->settings->limit : 1;

            if ($pos > 0) {
                for ($i = $pos; $i < ($pos + $limit); $i++) {
                    $array_index = $i - 1;
                    $data_source = $this->nested_content_container_arrays[$this->nested_board_counter] ?? $this->content_container_array;

                    $article = $data_source[$array_index] ?? null;
                    if ($article instanceof OcArticle) {
                        $content .= $this->render_article_object($article, $item, $css);
                    }
                }
            }
        }

        return $content;
    }

    /**
     * Fallback for article render if no template is found use this general article render.
     *
     * @param $article
     *
     * @return string
     * @deprecated as of v2.2.0 to be removed in v3
     */
    public function render_article_template_fallback($article): string
    {
        DeprecationHandler::error(__CLASS__ . '::render_article_template_fallback is deprecated as of infomaker/everyboard v2.2.0 to be removed in v3.');

        $article_img = $article->imageuuid[0] ?? null;
        $article_title = $article->headline[0] ?? '';
        $artile_leadin = $article->leadin[0] ?? null;
        $article_text = $article->text[0] ?? '';
        $article_author = $article->author[0] ?? '';

        $article_html = '';

        if (isset($article_img)) {
            $image_src = plugins_url('Every/image.php') . '?uuid=' . $article_img . '&type=preview&source=false&function=cover';
            $article_html .= '<img src="' . $image_src . '" style="width: 100%; height: auto;" />';
        }

        if (isset($article_title)) {
            $article_html .= '<h2>' . $article_title . '</h2>';
        }

        if (isset($artile_leadin)) {
            $article_html .= '<h4>' . $artile_leadin . '</h4>';
        }

        if (isset($article_author)) {
            $article_html .= '<p>' . $article_author . '</p>';
        }

        if (isset($article_text)) {
            $article_html .= '<div>' . OcUtilities::truncate_article_text($article_text, 200) . '</div>';
        }

        return $article_html;
    }

    /**
     * Uses Mobile Detect library to check what device type the current user is browsing from.
     *
     * @return string
     */
    private function set_active_device(): string
    {
        return $_SERVER['EVERY_DEVICE'] ?? 'mobile';
    }

    private function wrap_content(string $tag, string $classes, string $content): string
    {
        return ! empty($classes) ? "<{$tag} class=\"{$classes}\">$content</{$tag}>" : "<{$tag}>$content</{$tag}>";
    }

    /**
     * Checks the items settings to see if the item should be rendered on current device.
     *
     * @param $item
     *
     * @return bool
     */
    private function should_item_render($item): bool
    {
        $devices = $item->settings->devices ?? null;
        if ( ! $devices) {
            return true;
        }

        switch ($this->active_device) {
            case 'mobile':
                $shouldRender = $devices->mobile->removed !== 'true';
                break;
            case 'tablet':
                $shouldRender = $devices->tablet->removed !== 'true';
                break;
            case 'desktop':
                $shouldRender = $devices->smallscreen->removed !== 'true' && $devices->largescreen->removed !== 'true';
                break;
            default:
                $shouldRender = true;
        }

        return $shouldRender;
    }

    private function should_article_render(OcArticle $article): bool
    {
        // If articles with pubdates in the future should be hidden.
        if (isset($article->pubdate[0]) && $this->env_settings->get_pubdate_hide_property()) {
            $pubdate_timestamp = strtotime($article->pubdate[0]);
            $now_timestamp = time();

            if ($pubdate_timestamp > $now_timestamp) {
                return false;
            }
        }

        if (isset($article->everytrashed) && $this->env_settings->get_use_trashed()) {
            return in_array(OcUtilities::get_site_shortname(), (array)$article->everytrashed, true) === false;
        }

        return true;
    }

    private function should_have_content_size_classes(object $item): bool
    {
        if ( ! isset($item->type)) {
            return true;
        }

        return ! in_array($item->type, ['row', 'col', 'linked_widget', 'template_board_widget'], true);
    }

    private function widget_instance_info($widget_id, int $site_id = null): array
    {
        return EveryBoard_WidgetPrefab::get_instance()->get_widget_instance($widget_id, $site_id) ?? [];
    }

    /**
     * Generates custom CSS for an items settings.
     *
     * @param        $settings
     * @param string $item_type
     *
     * @return string
     */
    private function generate_item_css($settings, string $item_type = ''): string
    {
        $custom_css = ' ';

        // Loop over any existing css classes from settings.
        if (isset($settings->cssclass)) {
            foreach ((array)$settings->cssclass as $value) {
                $custom_css .= $value . ' ';
            }
        }

        // Set any framework specific classes for the settings.
        return $custom_css . $this->eb_fw_helper->get_item_css_classes($settings, $item_type);
    }
}
