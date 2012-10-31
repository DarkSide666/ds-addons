<?php
namespace gridext;
class Grid_Extended extends \Grid {

    protected $template_file='grid_extended'; // grid_extended

    /** ExtendedSearch initialization  */
    function addExtendedSearch($fields,$class='gridext/Grid_ExtendedSearch'){
        return $this->add($class,null,'extended_search')
            ->useWith($this)
            ->useFields($fields);
    }

    /** Default template of grid */
    function defaultTemplate(){
        $this->addLocations(); // add addon files to pathfinder
        return array($this->template_file);
    }

    /** Add addon files to pathfinder */
    function addLocations(){
        $l = $this->api->locate('addons', __NAMESPACE__, 'location');
        $addon = $this->api->locate('addons', __NAMESPACE__);
        $this->api->pathfinder->addLocation($addon, array(
            'template' => 'templates',
            //'css' => 'templates/css',
            //'js' => 'js',
        ))->setParent($l);
    }
}
