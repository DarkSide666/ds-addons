<?php
class page_index extends Page {
	function init(){
		parent::init();
		
		$this->add('H2')->set('ds-addons tests');
		
		$t = $this->add('Tabs');
		$t->addTabURL($this->api->url('tests/menu'),'Menu');
		$t->addTabURL($this->api->url('tests/listers'),'Listers');
		$t->addTabURL($this->api->url('tests/gridext'),'Grid Extended');
	}
}
