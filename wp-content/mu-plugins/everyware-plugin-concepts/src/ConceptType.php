<?php declare(strict_types=1);

namespace Everyware\Concepts;

use Infomaker\Everyware\Support\GenericPropertyObject;
use Tightenco\Collect\Contracts\Support\Arrayable;
use Tightenco\Collect\Contracts\Support\Jsonable;
use WP_Term;
use function is_array;

/**
 * Class ConceptType
 *
 * @property int id
 * @property string description
 * @property int parent
 *
 * @package Everyware\Concepts
 */
class ConceptType extends GenericPropertyObject
{
    public function __construct($term = [])
    {
        $this->fill($this->getArrayableItems($term));
    }

    private function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        }
        if ($items instanceof WP_Term) {
            return array_change_key_case($items->to_array(), CASE_LOWER);
        }
        if ($items instanceof Arrayable) {
            return $items->toArray();
        }
        if ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        }

        return (array)$items;
    }
}
