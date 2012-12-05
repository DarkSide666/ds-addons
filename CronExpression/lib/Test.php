<?php
namespace CronExpression;

class Test extends \View{
    function init(){
        parent::init();

        $a = array('* 15 * * *','@daily','@weekly','@monthly','3-59/15 2,6-12 */15 1 2-5');

        $cron = $this->add('CronExpression/Loader');

        foreach($a as $b){
            $f = $cron->factory($b);
            $p = $f->getPreviousRunDate()->format('Y-m-d H:i:s');
            $n = $f->getNextRunDate()->format('Y-m-d H:i:s');
            $this->add('View')->setHTML("Cron: <b>$b</b>, previous: <b>$p</b>, next: <b>$n</b>");
        }
    }
}
