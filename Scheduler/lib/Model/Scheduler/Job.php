<?php
namespace Scheduler;

class Model_Scheduler_Job extends \Model_Table {
	public $table = 'scheduler_job';

	function init(){
		parent::init();
		
		// hasOne relations
		$this->hasOne(__NAMESPACE__ . '/Scheduler_Task','scheduler_task_id')->caption('Task')->sortable(true);
		
		// Fields
		$this->addField('created_dts')->type('datetime')->caption('Created')
			->mandatory('Required')->defaultValue(date('Y-m-d H:i:s'))->sortable(true);
		$this->addField('scheduled_dts')->type('datetime')->caption('Scheduled')->mandatory('Required')->sortable(true);
		$this->addField('executed_dts')->type('datetime')->caption('Executed')->sortable(true);
		$this->addField('finished_dts')->type('datetime')->caption('Finished')->sortable(true);
		$this->addField('status')
			->enum(array('pending','missed','running','success','error'))
			->sortable(true)->mandatory('Required')->defaultValue('pending');
        $this->addField('messages')->type('text');
        
		// Order
		$this->setOrder('scheduled_dts','desc');
		$this->setOrder('created_dts','desc');

		// Hooks
		$this->addHook('beforeSave',$this);
	}

	// Hooks
	function beforeSave(){
        if($this['finished_dts']){
            $s = $this['status']=='error' ? 'error' : 'success';
        }elseif($this['executed_dts']){
            $s = 'running';
        }else{
            $s = $this['status']=='missed' ? 'missed' : 'pending';
        }
        $this->set('status',$s);
	}

    /**
     * Check if job is missed (there should already be newer scheduled job)
     * @param $id - (optional) ID of job to check. If null, then check currently loaded job
     * @return boolean - Is missed or not.
     */
    function isMissed($id=null){
        // load Job model
        if($id) $this->tryLoad($id);
        if($this->loaded()){
            $id = $this->id;

            // if status is already "missed", then step out
            if($this['status']=='missed') return true;
            
            // check only if job status is "pending" and scheduled time has already passed
            if($this['status']=='pending' && strtotime($this['scheduled_dts']) < time()){
            
                // reference Task model
                $task = $this->ref('scheduler_task_id');
        
                // calculate next run time after current Job scheduled time
                $next_dts = $this->add('CronExpression/Loader')
                    ->factory($task['cron_expr'])
                    ->getNextRunDate($this['scheduled_dts'])
                    ->format('Y-m-d H:i:s');
                
                // is "missed"
                return strtotime($next_dts) < time();
            }
            
            // not "missed"
            return false;
            
        } else throw $this->exception(__CLASS__.".isMissed(): Can't load model with ID=".$id);
    }

    /**
     * Process / execute job
     * @return boolean - Was processing successful?
     */
    function process(){
        try {

            if(!$this->loaded()) throw $this->exception('Job:process() - Job not loaded');

            // Start execution
            $this->set('executed_dts',date('Y.m.d H:i:s'));
            $job = $this->saveAs(__NAMESPACE__.'/Scheduler_Job');
        
            // Process job
            $task = $job->ref('scheduler_task_id');
            if(!$task['class'] || !$task['action']) throw $this->exception('Class or Action is not defined for Task');
        
            // try adding Object and running action (method)
            $obj = $this->add('\\'.$task['class']);
            $obj->$task['action']();

            //$job->set('messages',"Executing job #{$job->id} => call {$task['class']}->{$task['action']}()"); // for debug
            $job->set('status','success');

        }catch(\BaseException $e){
            $job->set('messages',$e->getText()); // need getHTML(). Issue https://github.com/atk4/atk4/issues/185
            $job->set('status','error');

        }catch(\Exception $e){ // this still don't catch all exceptions, for example fatal ones :(
            $job->set('messages',$e->getMessage());
            $job->set('status','error');
        }

        // if ther was error, then echo out messages to console
        if($job['status']=='error'){
            // rollback all DB transactions. This is needed to be able to save Job afterwards.
            while($this->api->db->inTransaction()) $this->api->db->rollBack();
            // show exception in console
            echo "\n".date('Y.m.d H:i:s')." Exception in Job->process():\n".$job['messages']."\n\n";
        }

        // Finish execution, save job
        $job->set('finished_dts',date('Y.m.d H:i:s'));
        $job->save();
        
        // Schedule next job
        $task->scheduleNow();
    }

}
