<?php
namespace GridExt;

class Test extends \View{
	function init(){
		parent::init();
        $this->api->dbConnect();
		
        $cols = $this->add('Columns');
        $c1 = $cols->addColumn(6);
        $c2 = $cols->addColumn(6);

        // --- ExtendedSearch -------------------------------------------------
        $c1->add('H2')->set('ExtendedSearch');
        // --------------------------------------------------------------------
        
		// Grid
		$c1->add('H4')->set('Grid_Extended');
		$g = $c1->add(__NAMESPACE__ . '/Grid_Extended');
		$g->setModel('tests/Test');
		$g->addPaginator(4);
		$g->addQuickSearch(array('name'));
		$g->addExtendedSearch(array('id','name','parent_id'));

		// CRUD
		$c1->add('H4')->set('CRUD_Extended');
		$g = $c1->add(__NAMESPACE__ . '/CRUD_Extended');
		$g->setModel('tests/Test');
		if($g->grid) {
			$g->grid->addPaginator(4);
			$g->grid->addQuickSearch(array('name'));
			$g->grid->addExtendedSearch(array('id','name','parent_id'));
		}

		// --- Toolbar --------------------------------------------------------
        $c2->add('H2')->set('Toolbar');
		// --------------------------------------------------------------------

		// Grid
		$c2->add('H4')->set('Grid_Extended');
		$g = $c2->add(__NAMESPACE__ . '/Grid_Extended');
		$g->setModel('tests/Test');
		$g->addPaginator(4);
		$g->addToolbar()
            ->add('Text')
            ->setHTML('<b>This is simple toolbar. You can add anything here or extend this with additional functionality!</b>');

		// --- Multiple Toolbars ----------------------------------------------
        $c2->add('H2')->set('Multiple Toolbars');
		// --------------------------------------------------------------------
		
		// Grid
		$c2->add('H4')->set('Grid_Extended');
		$g = $c2->add(__NAMESPACE__ . '/Grid_Extended');
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

		// --- ActionToolbar --------------------------------------------------
        $c2->add('H2')->set('ActionToolbar');
		// --------------------------------------------------------------------
		
		// Grid
		$c2->add('H4')->set('Grid_Extended');
		$g = $c2->add(__NAMESPACE__ . '/Grid_Extended');
		$g->setModel('tests/Test');
		$g->addPaginator(4);
		$g->addActionToolbar();

		// CRUD
		$c2->add('H4')->set('CRUD_Extended');
		$crud = $c2->add(__NAMESPACE__ . '/CRUD_Extended');
		$crud->setModel('tests/Test');
		if($g=$crud->grid) {
			$g->addPaginator(4);
			$g->addActionToolbar();
		}

	}
}
