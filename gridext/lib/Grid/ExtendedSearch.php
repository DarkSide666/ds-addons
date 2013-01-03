<?php
/**
 * ExtendedSearch represents many-field filter
 */
namespace GridExt;

class Grid_ExtendedSearch extends \Filter {
	
	public $fields = array();		// DB fields to show in search form
	public $hide_on_load = true;	// Hide extended search form on load
	
	protected $token='qhs73hsjk7';	// Name of hidden token (random) to detect if extended search is enabled or not
	protected $bs;
	protected $bc;
	protected $bt;

	function init(){
		parent::init();
		
		// Add show/hide button to the grid (owner of search form)
		$this->bt = $this->owner->addButton('Extended Search')
			//->addClass('float-right')
			->setIcon('search');
		$this->bt->js('click',$this->js()->toggle());

		// Form styling
		//$this->addClass('float-right stacked');
		$this->addStyle(array('clear'=>'both'));
		$this->template->trySet('fieldset','atk-row');
		
		// Add submit buttons
		$this->bs = $this->addSubmit('Search');
		$this->bc = $this->addSubmit('Clear');
		
		// Show form if there is searching enabled
		if($this->recall($this->token)) {
			$this->hide_on_load = false;
			$this->bt->addClass('ui-state-active');
			//$this->bt->removeClass('ui-state-default'); // this don't work because ui-state-default is hard-coded into ATK buttons
			$this->bt->addStyle(array('font-weight'=>'bold')); // highlight button when enabled
		}
		
		// Hide extended search form if needed
		if($this->hide_on_load) $this->addStyle(array('display'=>'none'));
	}
	
	// public: set fields
	function useFields($fields){
		$this->fields=$fields;
		return $this;
	}
	
	// private: add fields in search form
	function _addFields($fields=null){
		$fields = $fields?:$this->fields;
		
		// create model clone and remove field specific settings (mandatory, readonly, editable, visible, system, hidden)
		$m = $this->view->model->newInstance();
		foreach($fields as $f){
			if($m->hasField($f)){
				$m->getField($f)->mandatory(false)->readonly(false)->editable(true)->visible(true)->system(false)->hidden(false);
			}
		}
		// import model clone in form (create appropriate form fields)
		$this->importFields($m,$fields);
	}
	
	function postInit(){
		// Add fields in search form
		$this->_addFields();
		
		// Remembers values and uses them as conditions
		// Text values use loose (%LIKE%) and numeric values use strict comparing
        foreach($this->elements as $x=>$field){
            if($field instanceof \Form_Field){
				
                $field->set($v=$this->recall($x));

                if($field->no_save)continue;
                if(!$v)continue;
                
                // decide which method of comparing to use
                $type = is_string($v) ? 'like' : (is_numeric($v) ? 'equal' : null);
				
                // also apply the condition
                if($m=$this->view->model){
					if($type=='equal'){
						$m->addCondition($x,$v);
					}elseif($type=='like'){
						$m->addCondition($x,'like','%'.$v.'%');
					}
                }elseif($q=$this->view->dq){
					if($type=='equal'){
						$q->where($x,$v);
					}elseif($type=='like'){
						$q->where($x,'like','%'.$v.'%');
					}
                }
            }
		}
	}

	function submitted(){
		if($this->isClicked($this->bs)) $this->memorize($this->token,true);
		if($this->isClicked($this->bc)) $this->forget($this->token);
		parent::submitted();
	}
}
