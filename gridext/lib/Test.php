<?php
namespace GridExt;

class Test extends \View{
	function init(){
		parent::init();
        $this->api->dbConnect();
        
        $t = $this->add('Tabs');
   		$t->addTab('Toolbars')->add(__NAMESPACE__ . '/TestToolbars');
   		$t->addTab('Extended Search')->add(__NAMESPACE__ . '/TestExtendedSearch');
    }
}



class TestExtendedSearch extends \View{
	function init(){
		parent::init();

        $cols = $this->add('Columns');
        $c1 = $cols->addColumn(6);
        $c2 = $cols->addColumn(6);

		// Grid
		$c1->add('H4')->set('Grid_Extended');
		$g = $c1->add(__NAMESPACE__ . '/Grid_Extended');
		$g->setModel('tests/Test');
		$g->addPaginator(4);
		$g->addQuickSearch(array('name'));
		$g->addExtendedSearch(array('id','name','parent_id'));

		// CRUD
		$c2->add('H4')->set('CRUD_Extended');
		$g = $c2->add(__NAMESPACE__ . '/CRUD_Extended');
		$g->setModel('tests/Test');
		if($g->grid) {
			$g->grid->addPaginator(4);
			$g->grid->addQuickSearch(array('name'));
			$g->grid->addExtendedSearch(array('id','name','parent_id'));
		}
	}
}



class TestToolbars extends \View{
	function init(){
		parent::init();

        $cols = $this->add('Columns');
        $c1 = $cols->addColumn(6);
        $c2 = $cols->addColumn(6);

		// --- Toolbar --------------------------------------------------------
        $c1->add('H2')->set('One Toolbar');
		// --------------------------------------------------------------------

		// Grid
		$g = $c1->add(__NAMESPACE__ . '/Grid_Extended');
		$g->setModel('tests/Test');
		$g->addPaginator(4);
		$g->addToolbar()
            ->add('Text')
            ->setHTML('<b>This is simple toolbar. You can add anything here or extend this with additional functionality!</b>');

		// --- Multiple Toolbars ----------------------------------------------
        $c1->add('H2')->set('Multiple Toolbars');
		// --------------------------------------------------------------------
		
		// Grid
		$g = $c1->add(__NAMESPACE__ . '/Grid_Extended');
		$g->setModel('tests/Test');
		$g->addPaginator(4);
		$g->addToolbar()
            ->add('Text')
            ->setHTML('<b>This is simple toolbar. You can add anything here or extend this with additional functionality!</b>');
		$g->addToolbar()
            ->add('Text')
            ->setHTML('<b>One more toolbar. This is #2.</b>');
		$g->addToolbar()
            ->add('Text')
            ->setHTML('<b>Last but not least toolbar.</b>');

		// --- Action Toolbar -------------------------------------------------
        $c2->add('H2')->set('Action Toolbar');
		// --------------------------------------------------------------------
		
		// Grid
		$g = $c2->add(__NAMESPACE__ . '/Grid_Extended');
		$g->setModel('tests/Test');
		$g->addPaginator(4);
		$g->addActionToolbar();
	}
}
