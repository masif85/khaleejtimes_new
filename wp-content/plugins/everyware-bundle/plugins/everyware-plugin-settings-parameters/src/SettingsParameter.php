<?php declare (strict_types = 1);

namespace Everyware\Plugin\SettingsParameters;

use Infomaker\Everyware\Support\Storage\CollectionModel;

/**
 * Responsable for retrieving a settings paramater from the database.
 */
class SettingsParameter extends CollectionModel
{
    protected $optionName = 'everyware_settings_parameters';

    /**
     * Tries to get a settings parameter by its key.
     *
     * @param [type] $key
     *
     * @return mixed
     */
    public static function getValue(string $key):? string
    {
        $parameter = static::collection()->all()->pull('parameters');

        return $parameter[$key] ?? null;
    }
}
