<?php
namespace Scheduler;

class Controller_Grid_Format_JobMessages extends \AbstractController {
    
    public $page;
    
    function initField($field, $description){
        $g = $this->owner;
        $this->page = './'.$field;
        
        $g->js(true)->_selector('#'.$g->name.' .button_'.$field)
            ->addClass('bar-lightgray')->css('cursor','pointer');
        
        $g->js('click')->_selector('#'.$g->name.' .button_'.$field)
            ->univ()
            ->frameURL($description['descr'], array($this->api->url($this->page),
                $g->model->id_field => $g->js()
                    ->_selectorThis()
                    ->closest('tr')
                    ->attr('data-id')
            ));
        
        $g->columns[$field]['thparam'].=' style="width: 80px; text-align: center"';
    }
    
    function formatField($field){
        $g = $this->owner;
        $val = $g->current_row[$field.'_original'];
        $g->current_row_html[$field] = $val ? '<span class="button_'.$field.'"><span>Show</span></span>' : '';
    }
}
