<?php
namespace themeswitcher;
class page_Tests extends \Page{
	function init(){
		parent::init();
		$this->add('themeswitcher/View_ThemeSwitcher');
	}
}
