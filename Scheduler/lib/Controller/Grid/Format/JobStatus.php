<?php
namespace Scheduler;

class Controller_Grid_Format_JobStatus extends \AbstractController {
    
    public $styles = array(
        'pending' => 'bar-lightgray',
        'missed'  => 'bar-orange',
        'running' => 'bar-yellow',
        'success' => 'bar-green',
        'error'   => 'bar-red'
    );
    
    function initField($field, $description){
        $g = $this->owner;
        $g->columns[$field]['thparam'].=' style="width: 80px; text-align: center"';
    }
    
    function formatField($field){
        $g = $this->owner;
        $val = $g->current_row[$field.'_original'];
        $class = $this->styles[$val];
        $g->current_row_html[$field] = '<span class="'.$class.'"><span>'.$val.'</span></span>';
    }
}
