<?php
namespace Scheduler;

class Model_Scheduler_Job_Running extends Model_Scheduler_Job {
    function init(){
        parent::init();
        $this->addCondition('status','running');
    }
}
