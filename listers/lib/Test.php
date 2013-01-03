<?php
namespace listers;
class Test extends \View{
	function init(){
		parent::init();
		
        $cols = $this->add('Columns');
        $c1 = $cols->addColumn(6);
        $c2 = $cols->addColumn(6);
        $c1->add('H2')->set('Simple Lister');
        $c2->add('H2')->set('Tree Lister');

        // Connect DB
        $this->api->dbConnect();
		
		// Associative array
		$c1->add('H4')->set('Associative array');
		$l = $c1->add('listers/Lister_Tree');
		$l->setSource(array(
			'John',
			'Joe',
			'Anna'
		));
		
		// Array of hashes
		$c1->add('H4')->set('Array of hashes');
		$l = $c1->add('listers/Lister_Tree');
		$l->setSource( array(
			array('ids'=>10,'name'=>'John'),
			array('ids'=>20,'name'=>'Mary - kid of Joe','parent_id'=>40),
			array('ids'=>30,'name'=>'Kathy - kid of John','parent_id'=>10),
			array('ids'=>40,'name'=>'Joe'),
			array('ids'=>50,'name'=>'Peter - kid of John','parent_id'=>10),
			array('ids'=>60,'name'=>'Anna','parent_id'=>null),
			array('ids'=>70,'name'=>'Koko - kid of Kathy','parent_id'=>30),
			array('ids'=>80,'name'=>'Pako - kid of Kathy','parent_id'=>30),
		));

		// DSQL
		$c1->add('H4')->set('DSQL');
		$l = $c1->add('listers/Lister_Tree');
		$l->setSource( $this->api->db->dsql()
			->table('tmp_test')
			->field('*')
		);
		
		// SQL table
		$c1->add('H4')->set('SQL table');
		$l = $c1->add('listers/Lister_Tree');
		$l->setSource('tmp_test', array('id','name','parent_id'));
		
		// Model
		$c1->add('H4')->set('Model');
		$l = $c1->add('listers/Lister_Tree');
		$l->setModel('tests/Test');

		// --------------------------------------------------------------------
		// Associative array
		$c2->add('H4')->set('Associative array')
			->sub('Not available');

		// Array of hashes
		// DON'T USE FIELD NAMED "ID", because it's already built-in Model class as auto-incremental
		$c2->add('H4')->set('Array of hashes');
		$l = $c2->add('listers/Lister_Tree');
		$l->setSource( array(
			array('ids'=>10,'name'=>'John'),
			array('ids'=>20,'name'=>'Mary - kid of Joe','parent_id'=>40),
			array('ids'=>30,'name'=>'Kathy - kid of John','parent_id'=>10),
			array('ids'=>40,'name'=>'Joe'),
			array('ids'=>50,'name'=>'Peter - kid of John','parent_id'=>10),
			array('ids'=>60,'name'=>'Anna','parent_id'=>null),
			array('ids'=>70,'name'=>'Koko - kid of Kathy','parent_id'=>30),
			array('ids'=>80,'name'=>'Pako - kid of Kathy','parent_id'=>30),
		));
		$l->setRelationFields('ids','parent_id');

		// DSQL
		$c2->add('H4')->set('DSQL');
		$l = $c2->add('listers/Lister_Tree');
		$l->setSource($this->api->db->dsql()
			->table('tmp_test')
			->field('*')
		);
		$l->setRelationFields('id','parent_id');

		// SQL table
		$c2->add('H4')->set('SQL table');
		$l = $c2->add('listers/Lister_Tree');
		$l->setSource('tmp_test', array('id','name','parent_id'));
		$l->setRelationFields('id','parent_id');

		// Model
		$c2->add('H4')->set('Model');
		$l = $c2->add('listers/Lister_Tree');
		$l->setModel('tests/Test');
		$l->setRelationFields('id','parent_id');

	}
}
