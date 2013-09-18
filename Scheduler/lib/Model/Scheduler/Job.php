<?php
namespace Scheduler;

class Model_Scheduler_Job extends \SQL_Model {
	public $table = 'scheduler_job';

	/**
	 * Define structure of model
	 *
	 * @return void
	 */
    function init(){
		parent::init();
		
		// hasOne relations
		$this->hasOne(__NAMESPACE__ . '/Scheduler_Task', 'scheduler_task_id')->caption('Task')->sortable(true);
		
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
		$this->setOrder('scheduled_dts asc, created_dts asc, id asc');

		// Hooks
		$this->addHook('beforeSave', $this);
	}

	/**
	 * Set hooks
	 *
	 * @return void
	 */
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
	 * Adds message to Jobs messages
	 *
	 * @param string|array $message
	 * @param array $options Options for grid. Can use array('show_headers'=>false) for example
	 *
	 * @return $this
	 */
    function addMessage($message, $options = null) {
        if (!$message) return $this;
        
        if (is_string($message)) {
            $message = nl2br($message);
        
        } elseif (is_array($message)) {
            /*
            $m = $this->add('Model');
            $m->setSource('Array', $message);

            $g = $this->add('Grid_Basic');
            $g->setModel($m);
            */
            
            /*
            $m = $this->add('Model');
            foreach($message[0] as $key=>$row) {
                $m->addField($key);
            }
            $m->setSource('ArrayAssoc', $message);

            $g = $this->add('Grid_Basic');
            $g->setModel($m);
            */

            // this works only for associative arrays !!!
            $g = $this->add('Grid_Basic', $options);
            foreach($message[0] as $key=>$row) {
                $g->addColumn('text', $key);
            }
            $g->setSource($message);
            $out = $g->getHTML(true, false);

            //var_dump($out);
            $message = $out;
        }
        
        $old = $this->get('messages');
        $this->set('messages', $old . ($old ? "<br />" : "") . $message);
        return $this;
    }
    
    /**
     * Sets Job status
     *
     * @param string $status
     *
     * @return $this
     */
    function setStatus($status) {
        $this->set('status', $status);
        return $this;
    }
	

    /**
     * Check if job is missed (there should already be newer scheduled job)
     *
     * @param $id - (optional) ID of job to check. If null, then check currently loaded job
     *
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
     *
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
        
            // for debug
            //$job->addMessage("Executing job #{$job->id} => call {$task['class']}->{$task['action']}()");
            
            // try adding Object and running action (method)
            // pass Job object itself as parameter like Object->Action(this Job)
            $obj = $this->add('\\'.$task['class']);
            $status = $obj->$task['action']($job);

            // set status
            $job->setStatus($status === false ? 'error' : 'success');

        }catch(\BaseException $e){
            $job->addMessage($e->getText()); // need getHTML(). Issue https://github.com/atk4/atk4/issues/185
            $job->setStatus('error');

        }catch(\Exception $e){ // this still don't catch all exceptions, for example fatal ones :(
            $job->addMessage($e->getMessage());
            $job->setStatus('error');
        }

        // if there was error, then echo out messages to console
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
