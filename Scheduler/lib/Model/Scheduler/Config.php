<?php
namespace Scheduler;

class Model_Scheduler_Config extends \Model_Table {
	public $table = 'scheduler_config';

	function init(){
		parent::init();
		
		// Fields
        $this->addField('name')->mandatory('Required')->sortable(true)->editable(false);
        $this->addField('value')->mandatory('Required')->sortable(true);
		$this->addField('description')->type('text');

		// Set order
		$this->setOrder('name','asc');
		
	}
}
