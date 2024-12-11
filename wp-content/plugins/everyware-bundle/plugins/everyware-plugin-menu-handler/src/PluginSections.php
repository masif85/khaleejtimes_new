<?php declare(strict_types=1);

namespace Everyware\Plugin\MenuHandler;

use Everyware\ProjectPlugin\Components\ComponentSettingsRepository;
use Everyware\ProjectPlugin\Components\SettingsProviders\CollectionDbProvider;
use Infomaker\Everyware\Support\Storage\CollectionDB;
use Infomaker\Everyware\Support\Str;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;

/**
 * Class PluginSections
 * @package Everyware\Plugin\MenuHandler
 */
class PluginSections extends ComponentSettingsRepository {
    
    public function __construct() {
        $OpenContentProvider = OpenContentProvider::setup( [
            'q'                      => QueryBuilder::propertySearch('Type', 'section'),
            'contenttypes'           => [ 'Concept' ],
            'limit'                  => 0
        ] );
    
        $OpenContentProvider->queryWithRequirements();
    
        $conceptArray = $OpenContentProvider->queryWithRequirements( [
            'limit'                  => $OpenContentProvider->hits(),
            'properties'             => array('uuid', 'Name')
        ]);
    
        $concepts = array();
        foreach ($conceptArray as $section) {
            $concepts[] = $section->name[0];
        }
        
        $this->sectionArray = $concepts;
    }

    /**
    * Return section array of all Section concepts in OC.
    */    
    public function returnSection(){
        return $this->sectionArray;
    }

}
