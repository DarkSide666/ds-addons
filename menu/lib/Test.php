<?php
namespace menu;
class Test extends \View{
	function init(){
		parent::init();
		
        // Connect DB
        $this->api->dbConnect();

        $t = $this->add('Tabs');
        $t1 = $t->addTab('Horizontal-left');
        $t2 = $t->addTab('Vertical-right');
        $t3 = $t->addTab('Populated with Array');
        $t4 = $t->addTab('Populated with DSQL');
        $t5 = $t->addTab('Populated with SQL table');
        $t6 = $t->addTab('Populated with Model');

        // Menu 1 - horizontal
        $m1 = $t1->add('menu/Menu_Dropdown')
			->setType('horizontal')	// horizontal|vertical
			->setPosition('left')	// left|right
            ->addMenuItem('index','Horiz. index 1')			// 0
            ->addMenuItem('item','Item 2')					// 1
            ->sub()
				->addMenuItem('item','Item 2.1')			// 2
				->addMenuItem('item','Item 2.2')			// 3
				->sub()
					->addMenuItem('item','Item 2.2.1')		// 4
					->addMenuItem('item','Item 2.2.2')		// 5
					->sub()
						->addMenuItem('item','Item 2.2.2.1')// 6
						->addMenuItem('item','Item 2.2.2.2')// 7
					->end()
					->addMenuItem('item','Item 2.2.3')		// 8
				->end()
				->addMenuItem('item','Item 2.3')			// 9
				->addMenuItem('item','Item 2.4 ')			// 10
				->sub()
					->addMenuItem('item','Item 2.4.1')		// 11
					->addMenuItem('item','Item 2.4.2')		// 12
				->end()
			->end()
            ->addMenuItem('item','Item 3')					// 13
            ->addMenuItem('item','Item 4')					// 14
			->sub()
				->addMenuItem('item','Item 4.1')			// 15
				->addMenuItem('item','Item 4.2')			// 16
				->addMenuItem('item','Item 4.3')			// 17
			->end()
            ->addMenuItem('item','Item 5')					// 18
            ->addMenuItem('item','Item 6')					// 19
            ;
        // Menu 2 - vertical
        $m2 = $t2->add('menu/Menu_Dropdown')
			->setType('vertical')	// horizontal|vertical
			->setPosition('right')	// left|right
            ->addMenuItem('index','Vert. index 1')			// 0
            ->addMenuItem('item','Item 2')					// 1
            ->addMenuItem('item','Item 3')					// 2
            ->sub()
				->addMenuItem('item','Item 3.1')			// 3
				->addMenuItem('item','Item 3.2')			// 4
				->addMenuItem('item','Item 3.3')			// 5
				->sub()
					->addMenuItem('item','Item 3.3.1')		// 6
				->end()
				->addMenuItem('item','Item 3.4 ')			// 7
			->end()
            ->addMenuItem('item','Item 4')					// 8
            ;
            
        // Menu 3 - populated by Array
        $m3 = $t3->add('menu/Menu_Dropdown')
			->setSource(array(
				array('ids'=>10,'page'=>'p1',		'name'=>'page 1'),
            	array('ids'=>20,'page'=>'p4/2',		'name'=>'page 2',	'parent_id'=>40),
            	array('ids'=>30,'page'=>'p1/3',		'name'=>'page 3',	'parent_id'=>10),
            	array('ids'=>40,'page'=>'p4',		'name'=>'page 4'),
            	array('ids'=>50,'page'=>'p1/5',		'name'=>'page 5',	'parent_id'=>10),
            	array('ids'=>60,'page'=>'p6',		'name'=>'page 6',	'parent_id'=>null),
            	array('ids'=>70,'page'=>'p1/3/7',	'name'=>'page 7',	'parent_id'=>30),
            	array('ids'=>80,'page'=>'p1/3/8',	'name'=>'page 8',	'parent_id'=>30),
			))
			->setRelationFields('ids','parent_id');

        // Menu 4 - populated by DSQL
        $m4 = $t4->add('menu/Menu_Dropdown')
			->setSource(
				$this->api->db->dsql()
     	       		->table('tmp_test')
            		->field('*')
            )
			->setRelationFields('id','parent_id');

        // Menu 5 - populated by SQL table
        $m5 = $t5->add('menu/Menu_Dropdown')
			->setSource('tmp_test', array('id','name','parent_id'))
			->setRelationFields('id','parent_id');

        // Menu 6 - populated by Model
        $m6 = $t6->add('menu/Menu_Dropdown')
			->setRelationFields('id','parent_id')
        	->setModel('tests/Test');
	
	}
}
