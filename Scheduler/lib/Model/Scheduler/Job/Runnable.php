<?php
namespace Scheduler;

class Model_Scheduler_Job_Runnable extends Model_Scheduler_Job_Pending {
    function init(){
        parent::init();
        $this->addCondition('scheduled_dts','<=',$this->dsql()->expr('NOW()'));
        $this->setOrder('scheduled_dts','asc');
    }
}
