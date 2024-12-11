<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components;

use Everyware\ProjectPlugin\Components\Adapters\MetaboxAdapter;
use Everyware\ProjectPlugin\Components\Exceptions\InvalidMetaboxPosition;
use Everyware\ProjectPlugin\Components\Exceptions\InvalidMetaboxPriority;
use Exception;
use Infomaker\Everyware\Base\Models\Page;

class MetaboxManager
{
    protected $metaboxes = [];

    protected static $validPriorities = [
        'high',
        'low',
        'core',
        'sorted',
        'default'
    ];

    protected static $validPositions = [
        'normal',
        'side',
        'advanced'
    ];

    /**
     * @var bool
     */
    private $metaboxesAdded = false;

    /**
     * @var self
     */
    private static $instance;


    private function __construct()
    {
        if (is_admin()) {
            add_action('load-post.php', [static::class, 'registerAll']);
            add_action('load-post-new.php', [static::class, 'registerAll']);
        }
    }

    public function registerAll(): void
    {
        if ( ! $this->metaboxesAdded) {

            foreach ($this->metaboxes as $metabox) {
                $resolver = $metabox['resolver'];
                $component = $resolver(Page::current());
                MetaboxAdapter::init($component, $metabox['position'], $metabox['priority']);
            }
        }

        $this->metaboxesAdded = true;
    }

    /**
     * @param callable $resolver
     * @param string   $boxPosition
     * @param string   $boxPriority
     *
     * @throws InvalidMetaboxPosition
     * @throws InvalidMetaboxPriority
     */
    public function addMetabox(Callable $resolver, $boxPosition, $boxPriority): void
    {
        if ( ! array_key_exists($boxPosition, static::$validPositions)) {
            throw new InvalidMetaboxPosition('"' . $boxPosition . '" is not a valid position.');
        }

        if ( ! array_key_exists($boxPriority, static::$validPriorities)) {
            throw new InvalidMetaboxPriority('"' . $boxPriority . '" is not a valid priority.');
        }

        $this->metaboxes[] = [
            'priority' => $boxPriority,
            'position' => $boxPosition,
            'resolver' => $resolver
        ];
    }

    protected static function manager(): MetaboxManager
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param callable $resolver
     * @param string   $boxPosition
     * @param string   $boxPriority
     *
     * @throws InvalidMetaboxPosition
     * @throws InvalidMetaboxPriority
     */
    public static function register(Callable $resolver, $boxPosition = 'side', $boxPriority = 'default'): void
    {
        static::manager()->addMetabox($resolver, $boxPosition, $boxPriority);
    }
}
