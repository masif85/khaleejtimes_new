<?php declare(strict_types=1);

namespace Everyware\Concepts;

use Everyware\Concepts\Exceptions\InvalidConceptData;
use Infomaker\Everyware\Support\GenericPropertyObject;
use OcObject;

/**
 * OcConcept
 *
 * @property string         contenttype
 * @property string         Name
 * @property string         ParentUuid
 * @property OcConcept|null Parent
 * @property string         Type
 * @property string         uuid
 * @link    http://infomaker.se
 * @package Everyware\Concepts
 * @since   Everyware\Concepts\OcConcept 1.0.0
 */
class OcConcept extends GenericPropertyObject
{
    /**
     * @var array
     */
    protected static $requiredProperties = [
        'Name',
        'Type',
        'uuid',
        'contenttype',
        'ParentUuid'
    ];

    /**
     * All of the properties set on the container.
     *
     * @var array
     */
    protected $properties;

    private const PARENT_PROPERTY = 'Parent';

    /**
     * OcConcept constructor.
     *
     * @param OcObject $object
     *
     * @throws InvalidConceptData
     */
    public function __construct(OcObject $object)
    {
        foreach (static::$requiredProperties as $property) {
            $fetchedProperty = strtolower($property);
            if ( ! $object->offsetExists($fetchedProperty)) {
                throw new InvalidConceptData('The object dose not contain the required data.');
            }

            $this->set($property, implode('', $object->get($fetchedProperty)));
        }

        if ($object->offsetExists(strtolower(static::PARENT_PROPERTY))) {
            $this->set(static::PARENT_PROPERTY, $this->extractParent($object));
        }
    }

    public static function requiredProperties(int $generations = 0): array
    {
        $requiredProperties = static::$requiredProperties;

        $generation = [[]];

        for ($i = 0; $i <= $generations; $i++) {
            $parentPrefix = str_repeat(static::PARENT_PROPERTY . '.', $i);
            $generation[] = array_map(static function ($property) use ($parentPrefix) {
                return $parentPrefix . $property;
            }, $requiredProperties);
        }

        return array_merge(... $generation);
    }

    public function getParentUuid()
    {
        return $this->get('ParentUuid');
    }

    public function getPostTitle()
    {
        return $this->get('Name');
    }

    public function getPostMeta(): array
    {
        return [
            'oc_uuid' => $this->get('uuid')
        ];
    }

    public function hasParent(): bool
    {
        return $this->getParent() instanceof self;
    }

    public function getParent(): ?OcConcept
    {
        return $this->get(static::PARENT_PROPERTY);
    }

    /**
     * @param OcObject $object
     *
     * @return OcConcept|null
     * @throws InvalidConceptData
     */
    private function extractParent(OcObject $object): ?OcConcept
    {
        $parent = $object->get(static::PARENT_PROPERTY);

        if (empty($parent) || ! $parent[0] instanceof OcObject) {
            return null;
        }

        return new static($parent[0]);
    }
}
