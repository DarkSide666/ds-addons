<?php
namespace Scheduler;

class Heartbeat extends \AbstractController {

	// Initialization
	function init() {
		parent::init();
		
		// Check if we're using it in CLI mode and in ApiCLI
		if(php_sapi_name()!='cli') throw $this->exception("This script can only be run from PHP CLI!");
		if(!$this->owner instanceof \ApiCLI) throw $this->exception(__CLASS__ . " should be added to ApiCLI object!");
		
		// Connect database
		try {
            $this->api->dbConnect();
        }catch(\BaseException $e){
            die("No database connection: ".$e->getText()."\n");
        }

		// Check required ATK version
		$this->api->requires('atk','4.2.4');
	}

    /**
     * Execute scheduled jobs
     */
    public function execute(){
        
        // Check for missed jobs and reschedule them if needed
        $this->rescheduleMissedJobs();

        // Execute runnable jobs
        $this->executeRunnableJobs();
    }

    /**
     * Reschedule missed jobs
     */
    private function rescheduleMissedJobs(){
        // Select pending jobs
        $job = $this->add(__NAMESPACE__.'/Model_Scheduler_Job_Pending');

        foreach($job as $junk){
            // load related task model
            $task = $job->ref('scheduler_task_id');
            
            // if job is missed and should be rescheduled, then set its status to "missed" and reschedule
            if($job->isMissed() && $task['if_missed']=='reschedule'){
                // change status
                $job->set('status','missed');
                $job = $job->saveAs(__NAMESPACE__.'/Scheduler_Job');
                // reschedule task
                $job->ref('scheduler_task_id')->scheduleNow();
            }
        }
    }

    /**
     * Execute runnable jobs
     */
    private function executeRunnableJobs(){
        // Select jobs which need execution
        $job = $this->add(__NAMESPACE__.'/Model_Scheduler_Job_Runnable');

        // Execute each job
        foreach($job as $junk) {
            $job->process();
        }
    }

}
