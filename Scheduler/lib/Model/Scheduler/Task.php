<?php
namespace Scheduler;

class Model_Scheduler_Task extends \SQL_Model {
	public $table = 'scheduler_task';
	public $title_field = 'code';

	function init(){
		parent::init();
		
		// Fields
		$this->addField('code')->mandatory('Required')->sortable(true);
		$this->addField('cron_expr')->mandatory('Required')->caption('Cron Expression');
		$this->addField('class')->mandatory('Required')->sortable(true);
		$this->addField('action')->mandatory('Required')->sortable(true);
		$this->addField('if_missed')->enum(array('reschedule', 'run'))
            ->defaultValue('reschedule')
            ->mandatory('Required')->caption('If missed')
            ->display(array('form'=>'Radio'));
		$this->addField('enabled')->type('boolean')->defaultValue(false)->mandatory('Required')->sortable(true);
		
		// hasMany relations
		$this->hasMany(__NAMESPACE__ . '/Scheduler_Job');
		$this->hasMany(__NAMESPACE__ . '/Scheduler_Job_Pending');
		$this->hasMany(__NAMESPACE__ . '/Scheduler_Job_Runnable');
		$this->hasMany(__NAMESPACE__ . '/Scheduler_Job_Running');

		// Calculated fields
		$this->addExpression('last_run')->set(function($m,$q){
			return $m->refSQL(__NAMESPACE__ . '/Scheduler_Job')
						->fieldQuery('finished_dts')
						->where('finished_dts','is not',null)
						->del('order')
						->order('finished_dts','desc')
						->limit(1);
		})->caption('Last Run');

		$this->addExpression('last_status')->set(function($m,$q){
			return $m->refSQL(__NAMESPACE__ . '/Scheduler_Job')
						->fieldQuery('status')
						->where('finished_dts','is not',null)
						->del('order')
						->order('finished_dts','desc')
						->limit(1);
		})->caption('Last Status');

		$this->addExpression('next_run')->set(function($m,$q){
			return $m->refSQL(__NAMESPACE__ . '/Scheduler_Job')
						->fieldQuery('scheduled_dts')
						->where('finished_dts','is',null)
						->del('order')
						->order('scheduled_dts','desc')
						->limit(1);
		})->caption('Next Run');

		$this->addExpression('next_status')->set(function($m,$q){
			return $m->refSQL(__NAMESPACE__ . '/Scheduler_Job')
						->fieldQuery('status')
						->where('finished_dts','is',null)
						->del('order')
						->order('scheduled_dts','desc')
						->limit(1);
		})->caption('Next Status');

		// Hooks
		$this->addHook('afterSave,beforeDelete',$this);
	}

	/* HOOKS --------------------------------------------------------------- */
	
	// Reschedule jobs of this task
	function afterSave(){
		$this->scheduleNow($this->id);
	}

	// Delete related jobs when task is deleted
	function beforeDelete(){
		$this->ref(__NAMESPACE__.'/Scheduler_Job')->deleteAll();
	}



	/* ACTIONS ------------------------------------------------------------- */

	/**
	 * Schedule job for one task
	 * 
	 * @param $id - (optional) task ID or array of task IDs. If NULL, then try to schedule loaded task.
	 * @return error text or false on success
	 */
	function scheduleNow($id=null){
		if(!$id) {
			if($this->loaded()) $id = array($this->id);
			else return "Unknown Task ID in action scheduleNow() or Task module not loaded!";
		}
		
		if(!is_array($id)) $id=array($id);

		foreach($id as $i){
		
			// load task data
			$this->tryLoad($i);
			if(!$this->loaded()) return "Can't find task with ID $i!";

			// delete all pending jobs of this task
			$this->ref(__NAMESPACE__.'/Scheduler_Job_Pending')->deleteAll();

			// if task is not enabled, then step out with error
			if(!$this['enabled'])
				return "Task ".$this[$this->title_field]." can't be scheduled because it's not enabled!";

			// if task have some currently running jobs, then do nothing
			if($this->ref(__NAMESPACE__.'/Scheduler_Job_Running')->count()->getOne() > 0)
				return "Task ".$this[$this->title_field]." can't be scheduled because it has currently running jobs!";

			// calculate next run time
			/*
			$next_dts = $this->add('CronExpression/Loader')
				->factory($this['cron_expr'])
				->getNextRunDate();
			*/
			$next_dts = $this->add('CronExpression/Loader')
				->factory($this['cron_expr'])
				->getNextRunDate();

			// insert new job
			$m = $this->ref(__NAMESPACE__.'/Scheduler_Job');
			$m->set('scheduled_dts',$next_dts->format('Y-m-d H:i:s'));
			$m->saveAndUnload();
		}

		// success
		return false;
	}

	/**
	 * Schedule jobs for all enabled tasks
	 * 
	 * @return error text or false on success
	 */
	function rescheduleAll(){
		// schedule jobs for all enabled tasks
		$m = $this->addCondition('enabled',true);
		foreach($m as $task){
			$error = $this->scheduleNow($task['id']);
			if($error) return $error;
		}

		// success
		return false;
	}
	
}
