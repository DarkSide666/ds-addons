<?php
namespace GridExt;

class CRUD_Extended extends \CRUD {
	function init(){
        $this->grid_class=__NAMESPACE__ . '/Grid_Extended';
        parent::init();
	}
}
