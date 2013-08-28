<?php
namespace Scheduler;

class Page_Admin extends \Page{

	// Create tabs ------------------------------------------------------------
    function initMainPage(){
		$t = $this->add('Tabs');
		$t_tasks  = $t->addTabURL('./tasks','Tasks');
        $t_jobs   = $t->addTabURL('./jobs','Jobs');
		$t_config = $t->addTabURL('./config','Config');
	}

	// Tasks page -------------------------------------------------------------
	function page_tasks(){
		$m = $this->add(__NAMESPACE__ . '/Model_Scheduler_Task');
		$c = $this->add('CRUD',array('allow_add'=>true,'allow_edit'=>true,'allow_del'=>true));
		$c->setModel($m);
		if($g = $c->grid) {
			$g->addPaginator(15);

            // Replace formatters
            $g->setFormatter('last_status',__NAMESPACE__ . '/JobStatus');
            $g->setFormatter('next_status',__NAMESPACE__ . '/JobStatus');

			// add "Schedule now" button
			$g->addColumn('button','schedule_now');
			if($_GET['schedule_now']){
				// Execute model action "scheduleNow"
				$error = $m->scheduleNow($_GET['schedule_now']);
				// JS response
				if($error) {
	            	$chains = array(
	                	$g->js()->univ()->errorMessage($error)
	            	);
	            } else {
	            	$chains = array(
		                $g->js()->reload(),
	                	$g->js()->univ()->successMessage('Job scheduled')
	            	);
	            }
            	$this->js(null,$chains)->execute();
			}
		}
	}

	// Jobs page --------------------------------------------------------------
	function page_jobs(){
		$m = $this->add(__NAMESPACE__ . '/Model_Scheduler_Job');
		$g = $this->add('Grid');
		$g->setModel($m);
        $g->addPaginator(15);

        // add "Reschedule all" button
        $b = $g->addButton('Reschedule All');
        if($b->isClicked()){
            $error = $m->ref('scheduler_task_id')->rescheduleAll();
            if($error){
                $chains = array(
                    $g->js()->univ()->errorMessage($error)
                );
            } else {
                $chains = array(
                    $g->js()->reload(),
                    $g->js()->univ()->successMessage('Jobs rescheduled')
                );
            }
            $this->js(null,$chains)->execute();
        }
        
        // Add "Delete" functionality
        $g->addColumn('delete','Dzēst');

        // Replace formatters
        $g->setFormatter('status',__NAMESPACE__ . '/JobStatus');
        $g->setFormatter('messages',__NAMESPACE__ . '/JobMessages');
	}
    
    // Job messages page ------------------------------------------------------
	function page_jobs_messages(){
        // Load data
		$m = $this->add(__NAMESPACE__ . '/Model_Scheduler_Job');
        $m->tryLoad($_GET['id']);
        // Create HTML element
        $field = $this->add('HtmlElement')->setElement('pre');
        $field->setHTML($m['messages']?:'No messages');
        // Enforce rendering only of this object
        $_GET['cut_object']=$field->name;
	}

	// Config page ------------------------------------------------------------
	function page_config(){
        /*TO DO*/$this->add('View_Warning')->set('Not all of these settings are used / working in Scheduler.');
		$m = $this->add(__NAMESPACE__ . '/Model_Scheduler_Config');
		$c = $this->add('CRUD',array('allow_add'=>false,'allow_edit'=>true,'allow_del'=>false));
		$c->setModel($m);
	}

    // JavaScript, CSS, and template includes ---------------------------------
    
    // Load JS and CSS on rendering and execute 
	function render(){
		$this->js(true)
			//->_load('menu_dropdown_univ')
			->_css('bars')
			;
		return parent::render();
	}

    /** Default template of menu */
    function defaultTemplate(){
    	$this->addLocations(); // add addon files to pathfinder
		return parent::defaultTemplate();
	}

    /** Add addon files to pathfinder */
	function addLocations(){
        $l = $this->api->locate('addons', __NAMESPACE__, 'location');
		$addon = $this->api->locate('addons', __NAMESPACE__);
        $this->api->pathfinder->addLocation($addon, array(
        	'template' => 'templates',
            'css' => 'templates/css',
            'js' => 'js',
        ))->setParent($l);
	}
}
