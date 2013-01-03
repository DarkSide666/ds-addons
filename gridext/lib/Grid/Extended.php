<?php
namespace GridExt;

class Grid_Extended extends \Grid {

    protected $template_file='gridext_grid';

    /** Toolbar initialization */
    function addToolbar($class=null){
        if(!$class) $class=__NAMESPACE__ . '/Grid_Toolbar';
        return $this->add($class,null,'top_2');
    }

    /** ActionToolbar initialization */
    function addActionToolbar($actions=array(),$class=null){
        $tb = $this->addToolbar();
        if(!$class) $class=__NAMESPACE__ . '/Grid_ActionToolbar';
        if(!is_array($actions))$actions=array($actions);
        return $tb->add($class)->setActions($actions);
    }
    
    /**
     * Adds column with checkboxes on the basis of Model definition
     * 
     * @dst_field - should be Form_Field object or jQuery selector of 1 field
     * @cnt_field - should be jQuery selector of item counting container, optional
     * When passing fields as jQuery selectors format them like "#myfield" or ".myfield"
     */
    function addSelectable($dst_field,$cnt_field=null){
        $this->js_widget=null;
        $this->js(true)
            ->_load('ui.gridext_checkboxes')
            ->gridext_checkboxes(array('dst_field'=>$dst_field,'cnt_field'=>$cnt_field));
        $this->addColumn('checkbox','selected');

        $this->addOrder()
            ->useArray($this->columns)
            ->move('selected','first')
            ->now();
    }

    /** ExtendedSearch initialization  */
    function addExtendedSearch($fields,$class=null){
        if(!$class) $class=__NAMESPACE__ . '/Grid_ExtendedSearch';
        return $this->add($class,null,'extended_search')
            ->useWith($this)
            ->useFields($fields);
    }

    // Load JS and CSS on rendering and execute 
	function render(){
		$this->js(true)
			//->_load('gridext_univ')
			->_css('gridext');
		return parent::render();
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
            'css' => 'templates/css',
            'js' => 'templates/js',
        ))->setParent($l);
    }
}
