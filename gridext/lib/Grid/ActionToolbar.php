<?php
/**
 * ActionToolbar allows you to add toolbar with actions implemented in related Model
 */
namespace GridExt;

class Grid_ActionToolbar extends \AbstractView {
    public $grid;
    public $actions; // array of available actions
    
    public $prefix = 'action_'; // prefix used in Model action method names
    
    public $template_file = 'gridext_action_toolbar';
    public $spot_form = 'action_form'; // template spot name for action dropdown
    public $spot_buttons = 'buttons'; // template spot name for buttonset
	
	/**
	 * Initialization
	 */
	function init(){
		parent::init();
        if(!$this->owner->grid instanceof \Grid) throw $this->exception(__CLASS__ . ' can only be added to Grid!');
        $this->grid = $this->owner->grid;
        $this->api->addHook("pre-render", array($this, "preRender"));
    }
    
	/**
	 * Set available actions
	 */
	function setActions($actions=array()){
        // no actions
        if(!$actions) {
            $this->actions = array();
            return $this;
        }
    
        // if hash array (like 'foo'=>'cool action','bar'=>'delete everything'), then use it.
        // if not, then set keys = values.
        if(count(array_filter(array_keys($actions), 'is_string')) > 0) {
            $a = $actions;
        } else {
            $a = array_combine($actions,$actions);
        }
        
        // check action
        $this->actions = array();
        $self=$this;
        array_walk($a, function($value, $key, $prefix)use($self){
            // create key
            $k = (strpos($key,$prefix)!==0 ? $prefix : '') . $key;

            // if action method doesn't exist in Grid model, then throw exception
            if(!method_exists($self->grid->model,$k))
                throw $self->exception("Method $k is not defined in model ".get_class($self->grid->model));

            // add to toolbar actions
            $self->actions[$k] = ucwords($value);
        }, $this->prefix);
        
        return $this;
	}

	/**
	 * Return available model actions
	 */
	function getActions(){
        if(!$this->grid->model)
            throw $this->exception("Grid don't have associated Model!");
        
        // if actions already defined, then use them
        if($this->actions) return $this->actions;

        // else try to get all methods from grid model starting with "action_"
        $a = array();
        foreach(get_class_methods($this->grid->model) as $method){
            if(strpos($method,$this->prefix)===0) $a[]=substr($method,strlen($this->prefix));
        }
        $this->setActions($a);
        return $this->actions;
	}
	
    /**
     * Pre-render toolbar
     */
    function preRender($obj=null, $enclosure=null, $parent_id=undefined){

		// Form
		$f = $this->add('Form',null,$this->spot_form);
        $f_selected = $f->addField('hidden','selected');
		$f->addField('DropDown','action','Actions')
                ->setEmptyText('- Select -')
                ->setValueList($this->getActions());
		$f->addSubmit('Execute');
		
		// Buttonset
		$bs = $this->add('Buttonset',null,$this->spot_buttons);
		
		// "Select All" button
        $bs->addButton('Select All')->js('click',$this->grid->js()->gridext_checkboxes('select_star'));

		// "Select Visible" button - show only if grid have paginator enabled
		if(!empty($this->grid->paginator)){
            $bs->addButton('Select Visible')->js('click',$this->grid->js()->gridext_checkboxes('select_all'));
		}
		
		// "Unselect All" button
		$bs->addButton('Unselect All')->js('click',$this->grid->js()->gridext_checkboxes('unselect_all'));
		
		// Info
		$info = $this->add('HtmlElement')->setElement('span')->setClass('info');
		$count = $info->add('HtmlElement')->setElement('span')->setClass('count');
		$info->add('HtmlElement')->setElement('span')->set('items selected');
		
		// Grid selectable column
		$this->grid->addSelectable($f_selected,$count);
		
		// Form submit
		if($f->isSubmitted()){
            
            // if there is no action ID POSTed, then show error message
            if($f->get('action')==='')
                $this->js()->univ()->errorMessage('No action chosen!')->execute();
            

            // if there are no ID list POSTed, then show error message
            $ids = json_decode($f->get('selected'));
            if($ids===null) $ids = $f->get('selected');
            if(empty($ids))
                $this->js()->univ()->errorMessage('No records selected!')->execute();
            
            // try to execute action on selected (or all) records
            $m = $this->grid->model; // maybe need ->newInstance(); ?
            if($ids!=='*') $m->addCondition('id',$ids);

            foreach($m as $junk){
                try{
                    $m->{$f->get('action')}();
                }catch(\BaseException $e){
                    $this->js()->univ()->errorMessage($e->getText())->execute();
                }catch(\Exception $e){
                    $this->js()->univ()->errorMessage($e->getMessage())->execute();
                }
            }
            
            // success
            $r = is_array($ids) ? implode(',',$ids) : 'ALL';
            $chains = array(
                $this->js()->univ()->successMessage('Action executed<hr />Model:<br />'.get_class($this->grid->model).'<hr />Action:<br />'.$f->get('action').'<hr />IDs:<br />'.$r),
                $this->grid->js()->reload()
            );
            $this->js(null,$chains)->execute();
		}
	}
	
    /**
     * Default template
     */
    function defaultTemplate(){
        return array($this->template_file);
    }
}
