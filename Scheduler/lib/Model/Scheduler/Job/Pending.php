<?php
namespace Scheduler;

class Model_Scheduler_Job_Pending extends Model_Scheduler_Job {
    function init(){
        parent::init();
        $this->addCondition('status','pending');
    }
}
