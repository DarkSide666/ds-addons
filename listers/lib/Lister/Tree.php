<?php
namespace listers;
class Lister_Tree extends \View {

     /** If lister data is retrieed from the SQL database, this will contain dynamic query. */
    public $dq=null;

    /** For other iterators, this variable will be used */
    public $iter=null;

    /** Contains ID of current record */
    public $current_id=null;

    /** Points to current row before it's being outputted. Used in formatRow() */
    public $current_row=array();

    /** Similar to $current_row, but will use for direct HTML output, no escaping. Use with care. */
    public $current_row_html=array();

    /** Name of the ID and Parent_ID fields in this lister */
    public $id_field='id'; // Don't use "id" with static array as source !!!
    public $parent_id_field=null;
    
    /** Data arrays */
    protected $items=array();
    protected $ref=array();

    /** Template tags */
    protected $list_tag='list';
    protected $item_tag='item';
    protected $template_file='lister_tree';

    /** Template chunks */
    protected $t_list;
    protected $t_item;

    function init(){
        parent::init();
        $this->api->addHook("pre-render", array($this, "preRender"));
    }

    /**
     *   Sets source data for the lister. If source is a model, use setModel() instead.
     *   If you don't add command ->setRelationFields(), then Lister will work as simple Lister
     *
     *   // Array of hashes
     *   $l->setSource( array( // DON'T USE FIELD NAMED "ID", because it's already built-in Model class as auto-incremental
     *       array('ids'=>10,'name'=>'John'),
     *       array('ids'=>20,'name'=>'Mary - kid of Joe','parent_id'=>40),
     *       array('ids'=>30,'name'=>'Kathy - kid of John','parent_id'=>10),
     *       array('ids'=>40,'name'=>'Joe'),
     *       array('ids'=>50,'name'=>'Peter - kid of John','parent_id'=>10),
     *       array('ids'=>60,'name'=>'Anna','parent_id'=>null),
     *       array('ids'=>70,'name'=>'Koko - kid of Kathy','parent_id'=>30),
     *       array('ids'=>80,'name'=>'Pako - kid of Kathy','parent_id'=>30),
     *   ));
     *   $l->setRelationFields('ids','parent_id');
     *
     *   // DSQL
     *   $l->setSource(
     *   );
     *   $l->setRelationFields('id','parent_id');
     *
     *   // SQL table
     *   $l->setSource('tmp_test', array('id','name','parent_id'));
     *   $l->setRelationFields('id','parent_id');
     *
     *   // Model
     *   $l->setModel('Test');
     *   $l->setRelationFields('id','parent_id');
     *
     **/
    function setSource($source,$fields=null){

        // Set DSQL
        if($source instanceof \DB_dsql){
            $this->dq=$source;
            return $this;
        }
        // SimpleXML and other objects
        if(is_object($source)){
            if($source instanceof \Model) throw $this->exception('Use setModel() for Models');
            if($source instanceof \Controller) throw $this->exception('Use setController() for Controllers');
            if($source instanceof \Iterator){
                $this->iter=$source;
                return $this;
            }

            // Cast non-iterable objects into array
            $source=(array)$source;
        }

        // Set Array as a data source
        if(is_array($source)){
            $m=$this->setModel('Model',$fields);
            if(is_array(reset($source))){
                $m->setSource('Array',$source);
            }else{
                $m->setSource('ArrayAssoc',$source);
            }

            return $this;
        }

        // Set manually
        $this->dq=$this->api->db->dsql();
        $this->dq
            ->table($source)
            ->field($fields?:'*');

        return $this;
    }

    /* Set field names for parent-child relationship */
    // IMPORTANT: don't use id_field='id' with simple array models, because that
    //            will be overwritten by Model class auto-incremental values !!!
    function setRelationFields($id_field,$parent_id_field){
        $this->id_field = $id_field;
        $this->parent_id_field = $parent_id_field;
        return $this;
    }

    /** Redefine and change $this->current_row to format data before it appears */
    function formatRow(){
        $this->hook('formatRow');
    }

    /** is Hierarchical? */
    function isHierarchical(){
        return $this->id_field && $this->parent_id_field;
    }

    /** Iterator of data entries */
    function getIterator(){
        if(is_null($i=$this->model?:$this->dq?:$this->iter))
            throw $this->exception('Please specify data source with setSource or setModel');
        return $i;
    }

    /** Prepare array with parent-child references */
    function prepareData(){
        $this->ref = array();
        foreach($this->getIterator() as $junk=>$item){
            // current values
            $id = $item[$this->id_field];
            
            // save item
            $this->items[$id] = $item;
            
            // save relation
            if(isset($item[$this->parent_id_field])) {
                $this->ref[$item[$this->parent_id_field]][] = $id;
            } else {
                $this->ref[undefined][] = $id;
            }
        }
    }

    /** Start pre-rendering */
    function preRender($obj=null, $enclosure=null, $parent_id=undefined){
        // prepare data
        $this->prepareData();

        // check required template tags
        if( !$this->template->is_set($this->list_tag) ||
            !$this->template->is_set($this->item_tag) ||
            !$this->template->is_set('Content')
        )
            throw $this->exception('Template must have "'.$this->list_tag.'", "'.$this->item_tag.'" and "Content" tags');

        // remember template chunks
        $this->t_list = clone $this->template->cloneRegion($this->list_tag);
        $this->t_item = clone $this->template->cloneRegion($this->item_tag);

        // remove item template from container template
        $this->template->set($this->item_tag,'');

        // pre-render tree
        $this->preRenderTree($enclosure, $parent_id);
    }

    /** Pre-render tree */
    function preRenderTree($enclosure=null, $parent_id=undefined){

        // if root of tree
        if (!$enclosure) {
            $enclosure = $this;
        } else {
            // add <ul> in [Content] tag (works starting from 2nd level)
            $enclosure = $enclosure->add('View',null,'Content');
            $enclosure->template = clone $this->t_list;
        }

        // add <li> elements in current <ul>
        if($this->ref[$parent_id]){

            foreach($this->ref[$parent_id] as $junk=>$this->current_id) {
                
                // add LI element
                $item = $enclosure->add('View',null,$this->item_tag);
                $item->template = clone $this->t_item;

                // hook
                $this->current_row = $this->items[$this->current_id];
                $this->formatRow();

                // try setting data in item template tags
                foreach($this->current_row as $key=>$val){
                    if(isset($this->current_row_html[$key])) continue;
                    $item->template->trySet($key,$val);
                }
                if($this->current_row_html)
                    $item->template->setHTML($this->current_row_html);
                $item->template->trySet('id',$this->current_id);

                // recursively call subelement prerender
                if(isset($this->ref[$this->current_id])){
                    $this->preRenderTree($item, $this->current_id);
                }
            }

        } else {

            // destroy tree if there are no elements in it
            $enclosure->destroy();

        }
    }

    /** Render */
    function render(){
        parent::render();
    }

    /** Default template of tree */
    function defaultTemplate(){
        $this->addLocations(); // add addon files to pathfinder
        return array($this->template_file);
    }

    /** Add addon files to pathfinder */
    function addLocations(){
        $l = $this->api->locate('addons', __NAMESPACE__, 'location');
        $addon = $this->api->locate('addons', __NAMESPACE__);
        $this->api->pathfinder->addLocation($addon, array(
            'template' => 'templates',
            //'css' => 'templates/css',
            //'js' => 'js',
        ))->setParent($l);
    }
}
