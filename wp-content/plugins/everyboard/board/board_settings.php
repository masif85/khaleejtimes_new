<?php

class EveryBoard_Settings
{
    protected static $templates = [];
    private static $available_templates;
    private static $article_templates;
    private static $available_image_templates;

    public function __construct()
    {
        static::setup_article_templates();
    }

    /**
     * Fetch and match all templates in the current theme
     *
     * @return array
     */
    protected static function setup_article_templates(): array
    {
        if (isset(static::$article_templates)) {
            return static::$article_templates;
        }

        self::add_default_template_paths();

        $available_templates = [];
        foreach (static::$templates as $path) {
            if (file_exists($path) && $handle = opendir($path)) {
                while (false !== ($entry = readdir($handle))) {
                    if (preg_match("/\.php$/i", $entry)) {
                        $url = $path . "/" . $entry;
                        $raw = file_get_contents($url);

                        if (preg_match("/Article name:([a-zåäöÅÄÖ0-9\s\-_]{3,})/ui", $raw, $result)) {
                            $available_templates[trim($result[1])] = basename($url);
                        }

                        if (preg_match("/OC Template name:([a-zåäöÅÄÖ0-9\s\-_]{3,})/ui", $raw, $result)) {
                            $available_templates[trim($result[1])] = basename($url);
                        }
                    }
                }
            }
        }

        ksort($available_templates);

        return static::$article_templates = $available_templates;
    }

    public static function get_image_templates(array $available_templates = null): array
    {
        // If available templates has already been set, just return them.
        if (isset(static::$available_image_templates)) {
            return static::$available_image_templates;
        }

        self::add_default_template_paths();

        if ( ! isset($available_templates)) {
            $available_templates = ["None" => "None"];
        }

        $available_templates_temp = [];
        foreach (self::$templates as $path) {
            if (file_exists($path) && $handle = opendir($path)) {
                while (false !== ($entry = readdir($handle))) {
                    if (preg_match("/\.php$/i", $entry)) {
                        $url = $path . "/" . $entry;
                        $raw = file_get_contents($url);

                        if (preg_match("/Image name:([a-zåäöÅÄÖ0-9\s\-_]{3,})/ui", $raw, $result)) {
                            $available_templates_temp[trim($result[1])] = basename($url);
                        }
                    }
                }

            }
        }

        ksort($available_templates_temp);
        $available_templates = array_merge($available_templates, $available_templates_temp);

        return static::$available_image_templates = $available_templates;
    }

    /**
     * Function to get Available templates and show them in Boards
     *
     * @param array|null $available_templates
     *
     * @return array Available templates
     */
    public static function get_templates(array $available_templates = null): array
    {
        if (is_null(static::$available_templates) || $available_templates) {
            $available_templates_temp = static::get_all_templates();
            if ( ! isset($available_templates)) {
                $available_templates = ["None" => "None"];
            }

            $template_settings = get_option('template_settings', []);

            if (empty($template_settings)) {
                $template_settings = $available_templates_temp;
            }

            $diff = array_diff($available_templates_temp, $template_settings);

            foreach (array_keys($diff) as $key) {
                unset($available_templates_temp[$key]);
            }

            ksort($available_templates_temp);
            $available_templates = array_merge($available_templates, $available_templates_temp);

            static::$available_templates = $available_templates;
        }

        return static::$available_templates;
    }

    /**
     * Function to get all available templates and show them in Board settings
     * @return array Available templates
     */
    public static function get_all_templates(): array
    {
        static::setup_article_templates();

        return static::$article_templates;
    }

    /**
     * Function to return the part of an article template
     *
     * @param string $template_name File name for article template
     *
     * @return string           Real path to article template
     */
    public static function get_template_path(string $template_name): string
    {
        if (file_exists($template_name)) {
            return $template_name;
        }
        self::add_default_template_paths();

        foreach (self::$templates as $path) {
            $p = $path . "/" . $template_name;
            if (file_exists($p)) {
                return $p;
            }
        }

        return '';
    }

    /**
     * Function to decode and strip slashes in settings array or object
     *
     * @param mixed $settings
     *
     * @return mixed Object or Array depending on the parameter type
     */
    public static function decode_settings_array($settings)
    {
        if (is_array($settings)) {
            foreach ($settings as $key => $value) {
                if ( ! is_array($settings[$key])) {
                    $settings[$key] = stripslashes(urldecode($value));
                } else {
                    foreach ($settings[$key] as $sub_key => $sub_value) {
                        $settings[$key][$sub_key] = stripslashes(urldecode($sub_value));
                    }
                }
            }
        } elseif (is_object($settings)) {
            foreach ($settings as $key => $value) {
                if ( ! is_array($settings->$key) && ! is_object($settings->$key)) {
                    $settings->$key = stripslashes(urldecode($value));
                } else {
                    foreach ($settings->$key as $sub_key => $sub_value) {
                        $settings->$key->$sub_key = stripslashes(urldecode($sub_value));
                    }
                }
            }
        }

        return $settings;
    }

    /**
     * Add a template path to templates property
     *
     * @param string $path
     */
    public static function add_template_path(string $path): void
    {
        if ( ! in_array($path, self::$templates, true)) {
            self::$templates[] = $path;
        }
    }

    /**
     * Add default paths to templates
     */
    protected static function add_default_template_paths(): void
    {
        $paths = [
            get_stylesheet_directory(),
            get_template_directory(),
            get_stylesheet_directory() . '/templates/',
            get_template_directory() . '/templates/'
        ];

        foreach ($paths as $p) {
            static::add_template_path($p);
        }
    }
}
