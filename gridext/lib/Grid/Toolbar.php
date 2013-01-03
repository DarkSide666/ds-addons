<?php
/**
 * Toolbar allows you to add simple toolbar for your grid
 */
namespace GridExt;

class Grid_Toolbar extends \AbstractView {
    public $template_file = 'gridext_toolbar';
    public $grid;
	
	/**
	 * Initialization
	 */
	function init(){
		parent::init();
        if(!$this->owner instanceof \Grid) throw $this->exception(__CLASS__ . ' can only be added to Grid!');
        $this->grid = $this->owner;
        //$this->api->addHook("pre-render", array($this, "preRender"));
    }
    
    /**
     * Pre-render toolbar
     */
    //function preRender($obj=null, $enclosure=null, $parent_id=undefined){}
	
    /**
     * Default template
     */
    function defaultTemplate(){
        return array($this->template_file);
    }
}
