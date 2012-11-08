<?php
namespace gridext;
class Test extends \View{
	function init(){
		parent::init();
		
        $cols = $this->add('Columns');
        $c1 = $cols->addColumn();
        $cs = $cols->addColumn('5%');
        $c2 = $cols->addColumn();
        $c1->add('H2')->set('Simple Grid');
        $c2->add('H2')->set('Extended Grid');

        // Connect DB
        $this->api->dbConnect();
		
        // ------------------------------------------------
		// Grid with paginator and quicksearch
		$c1->add('H4')->set('Grid with Paginator and QuickSearch');
		$g = $c1->add('Grid');
		$g->setModel('tests/TicketType');
		$g->addPaginator(5);
		$g->addQuickSearch(array('name'));

		// CRUD with paginator and quicksearch
		$c1->add('H4')->set('CRUD with Paginator and QuickSearch');
		$g = $c1->add('CRUD');
		$g->setModel('tests/TicketType');
		if($g->grid) {
			$g->grid->addPaginator(5);
			$g->grid->addQuickSearch(array('name'));
		}

		// ------------------------------------------------
		// Grid with paginator and quicksearch
		$c2->add('H4')->set('Grid_Extended with Paginator, QuickSearch and ExtendedSearch');
		$g = $c2->add('gridext/Grid_Extended');
		$g->setModel('tests/TicketType');
		$g->addPaginator(5);
		$g->addQuickSearch(array('name'));
		$g->addExtendedSearch(array('id','name','parent_id'));

		// CRUD with paginator and quicksearch
		$c2->add('H4')->set('CRUD_Extended with Paginator, QuickSearch and ExtendedSearch');
		$g = $c2->add('gridext/CRUD_Extended');
		$g->setModel('tests/TicketType');
		if($g->grid) {
			$g->grid->addPaginator(5);
			$g->grid->addQuickSearch(array('name'));
			$g->grid->addExtendedSearch(array('id','name','parent_id'));
		}

	}
}
