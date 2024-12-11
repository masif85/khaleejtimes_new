<?php

use Everyware\Everyboard\DeprecationHandler;

class EveryBoard_Framework_Helper
{
    // Class members
    public $eb_default_framework = 'bootstrap4';

    public $eb_active_framework;

    public $eb_max_columns;

    private $deprecated_frameworks = [
        'bootstrap3_20col' => 'Bootstrap 3 - 20 columns',
        'bootstrap' => 'Bootstrap',
        'foundation' => 'Foundation',
        'percent' => 'Percent',
        'suit' => 'Suit Grid'
    ];

    /**
     * Framework Helper constructor adds actions and sets class members.
     */
    public function __construct()
    {
        $options = get_option('render_framework');
        $this->eb_active_framework = esc_attr($options['framework'] ?? '');
        $this->eb_max_columns = $this->get_max_cols();
    }

    /*
     * Function to return "registered" frameworks.
     * @return Array with frameworks.
     */
    public function get_frameworks(): array
    {
        $frameworks = [
            'bootstrap4' => 'Bootstrap 4',
            'bootstrap3' => 'Bootstrap 3'
        ];

        // Make sure deprecated framework is added if active
        if (array_key_exists($this->eb_active_framework, $this->deprecated_frameworks)) {
            error_log(implode(PHP_EOL, [
                'Framework: "' . $this->eb_active_framework . '" is deprecated as of infomaker/everyboard v2.2.0.',
                'Please switch to one of the other supported frameworks available.'
            ]));
            $frameworks[$this->eb_active_framework] = $this->deprecated_frameworks[$this->eb_active_framework];
        }

        return $frameworks;
    }

    /**
     * Function to return framework-specific wrap-classes for the boards grid system
     */
    public function board_wrapper_classes(): string
    {
        return 'every_board';
    }

    /**
     * Function to return html formatted for a row in the active framework.
     *
     * @param string $css Any extra classes to add to the row.
     *
     * @return string             Row HTML for the active framework.
     */
    public function row_html(string $css = ''): string
    {
        DeprecationHandler::error(implode(PHP_EOL, [
            __CLASS__ . '::row_html is deprecated as of infomaker/everyboard v2.2.0 to be removed in v3.',
            'Use ' . __CLASS__ . '::get_row_classes to fetch classes instead.'
        ]));

        $classes = $this->get_row_classes();

        $css = trim($css);
        if ( ! empty($css)) {
            $classes .= ' ' . $css;
        }

        return '<div class="' . $classes . '">';
    }

    /**
     * Function to return html formatted for a column in the active framework.
     *
     * @param array  $current_col_size The col sizes for the column about to be rendered
     * @param string $css              Any extra classes to add to the col.
     * @param array  $page_col_size    The col sizes relative to the top board in case of nested boards
     *
     * @return string                   Framework-specific column HTML.
     */
    public function col_html(array $current_col_size, string $css, $page_col_size = []): string
    {
        $col_classes = $this->get_framework_col_classes($this->eb_active_framework, $current_col_size);

        if ( ! empty($page_col_size)) {
            $col_classes .= ' ' . $this->get_page_col_classes($this->eb_active_framework, $page_col_size);
        }

        $col_classes = trim($col_classes);
        $col_classes .= (trim($css) !== '') ? ' ' . trim($css) : '';

        switch ($this->eb_active_framework) {
            case 'bootstrap3':
            case 'bootstrap3_20col':
            case 'bootstrap4':
            case 'bootstrap':
            case 'foundation':
                return '<div class="' . $col_classes . '">';
            case 'suit':
                return '<div class="Grid-cell ' . $col_classes . '">';
            default:
                return '<div class="rendered_col ' . trim($css) . '" style="width: ' . $current_col_size['smallscreen'] . '%; float: left;">';
        }
    }

    /**
     * Function to create framework-specific column-classes
     *
     * @param $active_framework
     * @param $col_size
     *
     * @return string
     */
    private function get_framework_col_classes($active_framework, $col_size): string
    {
        switch ($active_framework) {
            case 'bootstrap3':
            case 'bootstrap3_20col':
                $classes = [
                    "col-xs-${col_size['mobile']}",
                    "col-sm-${col_size['tablet']}",
                    "col-md-${col_size['smallscreen']}",
                    "col-lg-${col_size['largescreen']}"
                ];
                break;
            case 'bootstrap':
                $classes = ["span${col_size['smallscreen']}"];
                break;
            case 'foundation':
                $classes = [
                    'columns',
                    "large-${col_size['smallscreen']}"
                ];
                break;
            case 'suit':
                $classes = [
                    "u-sm-size${col_size['mobile']}of12",
                    "u-md-size${col_size['tablet']}of12",
                    "u-lg-size${col_size['smallscreen']}of12"
                ];
                break;
            case 'bootstrap4':
                $device_class_map = [
                    'mobile' => 'sm',
                    'tablet' => 'md',
                    'smallscreen' => 'lg',
                    'largescreen' => 'xl'
                ];

                $current_size = $col_size['mobile'];

                $classes = ["col-${col_size['mobile']}"];
                foreach ($col_size as $device => $size) {
                    // Only add classes on size changes
                    if ($current_size !== $size) {
                        $classes[] = "col-${device_class_map[$device]}-${size}";
                    }
                    $current_size = $size;
                }
                break;
            default:
                $classes = [];
        }

        return trim(implode(' ', $classes));
    }

    /**
     * Function to create framework-specific board column-classes
     *
     * @param $active_framework
     * @param $col_size
     *
     * @return string
     */
    private function get_page_col_classes($active_framework, $col_size): string
    {
        switch ($active_framework) {
            case 'bootstrap':
                return "board-span${col_size['smallscreen']}";
            case 'foundation':
                return "board-large-${col_size['smallscreen']}";
            default:
                return "board-col-xs-${col_size['mobile']} board-col-sm-${col_size['tablet']} board-col-md-${col_size['smallscreen']} board-col-lg-${col_size['largescreen']}";
        }
    }

    /**
     * Sets framework specific classes for items based on their settings.
     *
     * @param        $settings
     * @param string $item_type
     *
     * @return string
     */
    public function get_item_css_classes($settings, string $item_type = ''): string
    {
        if (isset($settings->devices)) {
            $display_type = $this->get_item_display_type($item_type);

            $devices_hidden = array_map(static function ($device) {
                return filter_var($device->hidden ?? 'false', FILTER_VALIDATE_BOOLEAN);
            }, (array)$settings->devices);

            $classes = [];

            $device_class_map = [
                'mobile' => 'sm',
                'tablet' => 'md',
                'smallscreen' => 'lg',
                'largescreen' => 'xl'
            ];

            switch ($this->eb_active_framework) {
                case 'bootstrap4':
                    // Return if element isn't hidden at some point
                    if ( ! in_array(true, $devices_hidden, true)) {
                        break;
                    }

                    $currently_hidden = $devices_hidden['mobile'];

                    if ($currently_hidden) {
                        $classes[] = 'd-none';
                    }

                    foreach ($devices_hidden as $device => $hidden) {
                        // Only add classes on visibility changes
                        if ($currently_hidden !== $hidden) {
                            $classes[] = ! $hidden ? "d-${device_class_map[$device]}-{$display_type}" : "d-${device_class_map[$device]}-none";
                        }

                        $currently_hidden = $hidden;
                    }

                    break;
                case 'suit':
                    foreach ($devices_hidden as $device => $hidden) {
                        $classes[] = $hidden ? "u-${device_class_map[$device]}-hidden" : '';
                    }
                    break;
                default:
                    // Set legacy map
                    $device_class_map = [
                        'mobile' => 'xs',
                        'tablet' => 'sm',
                        'smallscreen' => 'md',
                        'largescreen' => 'lg'
                    ];

                    foreach ($devices_hidden as $device => $hidden) {
                        $classes[] = $hidden ? "hidden-${device_class_map[$device]}" : '';
                    }
            }

            return implode(' ', array_filter($classes));
        }

        return '';
    }

    /**
     * Function to enqueue scripts for the active framework.
     */
    public function eb_framework_helper_enqueue_scripts(): void
    {
        DeprecationHandler::error(implode(PHP_EOL, [
            __CLASS__ . '::eb_framework_helper_enqueue_scripts is deprecated as of infomaker/everyboard v2.2.0 to be removed in v3.',
            'Everyboard does not provide Bootstrap 3 specific CSS and Javascript anymore.'
        ]));
    }

    /**
     * Function to return max number of columns for the active framework.
     *
     * @return int Number of columns for active framework.
     */
    public function get_max_cols(): int
    {
        switch ($this->eb_active_framework) {
            case 'bootstrap':
            case 'bootstrap3':
            case 'bootstrap4':
            case 'foundation':
            case 'suit':
                return 12;
            case 'bootstrap3_20col':
                return 20;
            default:
                return 100;
        }
    }

    /**
     * Function to get content size classes
     *
     * @param array $content_size
     *
     * @return string
     */
    public function get_content_size_classes(array $content_size): string
    {
        switch ($this->eb_active_framework) {
            case 'bootstrap':
                return "content-size-span${content_size['smallscreen']}";
            case 'foundation':
                return "content-size-large-${content_size['smallscreen']}";
            default:
                return "content-size-xs-${content_size['mobile']} content-size-sm-${content_size['tablet']} content-size-md-${content_size['smallscreen']} content-size-lg-${content_size['largescreen']}";
        }
    }

    /**
     * Determine if the framework requires a grid clearfix
     *
     * @return bool
     * @since 1.0.0
     */
    public function need_clearfix(): bool
    {
        return $this->eb_active_framework !== 'bootstrap4';
    }

    /**
     * @param string $item_type
     *
     * @return string
     * @since 1.0.0
     */
    private function get_item_display_type(string $item_type = ''): string
    {
        if ($item_type === 'row' && $this->eb_active_framework === 'bootstrap4') {
            return 'flex';
        }

        return 'block';
    }

    /**
     * Create a formatted class list for a row in the active framework.
     *
     * @return string
     */
    public function get_row_classes(): string
    {
        switch ($this->eb_active_framework) {
            case 'foundation':
            case 'bootstrap3':
            case 'bootstrap3_20col':
                $classes = 'row';
                break;
            case 'bootstrap4':
                $classes = 'row align-items-stretch';
                break;
            case 'bootstrap':
                $classes = 'row-fluid';
                break;
            case 'suit':
                $classes = 'Grid Grid--fit';
                break;
            default:
                $classes = 'row rendered_row';
                break;
        }

        return $classes;
    }
}
