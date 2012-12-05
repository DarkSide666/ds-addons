<?php
namespace CronExpression;

use Cron\CronExpression as CronExpression;

class Loader extends \AbstractObject {

    function init(){
        parent::init();
        
        $root = 'Cron'.DIRECTORY_SEPARATOR;
        require_once $root.'FieldInterface.php';
        require_once $root.'AbstractField.php';
        require_once $root.'DayOfMonthField.php';
        require_once $root.'DayOfWeekField.php';
        require_once $root.'HoursField.php';
        require_once $root.'MinutesField.php';
        require_once $root.'MonthField.php';
        require_once $root.'YearField.php';
        require_once $root.'FieldFactory.php';
        require_once $root.'CronExpression.php';
    }
    
    function factory($expression){
        return CronExpression::factory($expression);
    }
}
