<?php
namespace tests;
class page_DSAddons extends \Page{
	function initMainPage(){
		parent::init();
		$this->add('H2')->set('DS-Addons');
		$t = $this->add('Tabs');
		$t->addTabURL($this->api->url('./gridext'),'Grid Extended');
		$t->addTabURL($this->api->url('./themeswitcher'),'ThemeSwitcher');
		$t->addTabURL($this->api->url('./menu'),'Menu');
		$t->addTabURL($this->api->url('./listers'),'Listers');
		$t->addTabURL($this->api->url('./cronexpression'),'CronExpression');
		$t->addTabURL($this->api->url('./querypath'),'QueryPath');
	}
	
	function page_themeswitcher(){
        $this->add('themeswitcher\Test','themeswitcher_test');
	}
	function page_menu(){
        $this->add('menu\Test','menu_test');
	}
	function page_listers(){
        $this->add('listers\Test','listers_test');
	}
	function page_gridext(){
        $this->add('GridExt\Test','gridext_test');
	}
	function page_cronexpression(){
        $this->add('CronExpression\Test','cronexpression_test');
	}
	function page_querypath(){
        $this->add('QueryPath\Test','querypath_test');
	}
}
