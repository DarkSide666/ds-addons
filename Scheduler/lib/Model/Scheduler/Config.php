<?php
namespace Scheduler;

class Model_Scheduler_Config extends \SQL_Model {
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
