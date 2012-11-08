<?php
namespace tests;
class Model_TicketType extends \Model_Table {
	public $table = 'ticket_type';
	function init(){
		parent::init();
		$this->addField('name');
		$this->addField('parent_id');
	}
}
