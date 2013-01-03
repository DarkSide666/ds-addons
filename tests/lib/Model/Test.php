<?php
namespace tests;
class Model_Test extends \Model_Table {
	public $table = 'tmp_test';
	function init(){
		parent::init();
		$this->addField('name')->sortable(true);
		$this->addField('parent_id');
		
		// add auto DB creator addon
		$this->add('dynamic_model/Controller_AutoCreator');
		$this->insertData();
	}
	
	// If $delete=true, then we reset all data on every model initialization
	// This can lead to unpredictable results
	function insertData($delete=false){
        // delete current records
        if($delete)$this->_dsql()->delete();
        
        // insert new records
        if(!$this->count()->getOne()){
            for($i=1,$i_max=5;$i<=$i_max;$i++){
                for($j=1,$j_max=3;$j<=$j_max;$j++){
                    $id=($i-1)*$j_max+$j;
                    $this->set('id',$id);
                    $this->set('name',"Name [$i,$j]");
                    $this->set('parent_id',$i==1?null:$i);
                    $this->saveAndUnload();
                }
            }
        }
	}
	
	// Methods for Scheduler addon test
	function action_Foo(){echo "FOO[$this->id]";}
	function action_bar(){echo "BAR[$this->id]";}
	function action_siLEnt(){}
	function fake(){echo "FAKE FAKE FAKE";}
	function foo(){echo "FAKE FOO ACTION";}
	
	// ...
}
