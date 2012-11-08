<?php
namespace themeswitcher;
class Test extends \View{
	function init(){
		parent::init();
		$this->add('themeswitcher/View_ThemeSwitcher');
	}
}
