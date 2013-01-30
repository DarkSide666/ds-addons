<?php
namespace RollingCurl;
set_time_limit(300); // 5 min

class Test extends \View
{
    public $urls = array(
        "http://www.yahoo.com",         "http://www.google.com",
    /*    "http://www.facebook.com",      "http://www.youtube.com",
        "http://www.live.com",          "http://www.wikipedia.com",
        "http://www.blogger.com",       "http://www.msn.com",
        "http://www.baidu.com",         "http://www.yahoo.co.jp",
        "http://www.myspace.com",       "http://www.qq.com",
        "http://www.google.co.in",      "http://www.twitter.com",
        "http://www.google.de",         "http://www.microsoft.com",
        "http://www.google.cn",         "http://www.wordpress.com",
        "http://www.sina.com.cn",       "http://www.google.co.uk"
    */
    );
    
    protected $data; // fills automatically
    protected $totals; // total statistics
    protected $out; // output container
    protected $rc; // RollingCurl object
    protected $win_size = 5; // window size for multi-curl requests
    protected $counter = 0;
    
    private $css = array(
        'table' => array('width'=>'100%'),
        'th' => array('border'=>'1px solid gray','padding'=>'0 2px','background'=>'lightgray','font-weight'=>'bold','text-align'=>'left'),
        'td' => array('border'=>'1px solid gray','padding'=>'0 2px'),
    );

    function init() {
        parent::init();
        $self = $this;
        
        // add RollingCurl object to API
        $this->rc = $this->api->add(__NAMESPACE__ . '/RollingCurl');

        // prepare data array
        $this->_prepareDataArray();
        
        // create output container
        $this->out = $this->_add('table',$this);
        
        // execute single request tests
        $this->singleRequestTests();
        
        // execute multi request tests
        $this->multiRequestTests();

        // generate output
        $this->_output();
    }
    
    /**
     * Single request test
     * 
     * @return void
     */
    function singleRequestTests() {
        $type = 'single';
        $this->counter = 0;
        $this->totals[$type]['start'] = microtime(true);
        
        foreach($this->data as $id=>$data) {
            $this->rc
                ->setCallbefore(array($this,'_callbefore'))
                ->setCallback(array($this,'_callback'))
                ->get($data['url'],null,null,array('id'=>$id,'type'=>$type))
                ->execute();
        }
        
        $this->totals[$type]['finish'] = microtime(true);
        $this->totals[$type]['duration'] = $this->totals[$type]['finish'] - $this->totals[$type]['start'];
    }
    
    /**
     * Multi request test
     * 
     * @return void
     */
    function multiRequestTests() {
        $type = 'multi';
        $this->counter = 0;
        $this->totals[$type]['start'] = microtime(true);
        
        $this->rc
            ->setCallbefore(array($this,'_callbefore'))
            ->setCallback(array($this,'_callback'));
        
        foreach($this->data as $id=>$data) {
            $this->rc->get($data['url'],null,null,array('id'=>$id,'type'=>$type));
        }
        
        // execute multi curl requests
        $this->rc->execute($this->win_size);

        $this->totals[$type]['finish'] = microtime(true);
        $this->totals[$type]['duration'] = $this->totals[$type]['finish'] - $this->totals[$type]['start'];
    }
    
    /**
     * Callbefore method
     * 
     * @param RollingCurlRequest $request
     * @return void
     */
    function _callbefore($request){
        $id = $request->callback_data['id'];
        $type = $request->callback_data['type'];

        $this->data[$id][$type]['start'] = microtime(true);
    }
    
    /**
     * Callback method
     * 
     * @param string $response
     * @param object $info
     * @param RollingCurlRequest $request
     * @return void
     */
    function _callback($response, $info, $request){
        $id = $request->callback_data['id'];
        $type = $request->callback_data['type'];
        
        // fill data
        $this->counter++;
        $this->data[$id][$type]['order'] = $this->counter;
        $this->data[$id][$type]['finish'] = microtime(true);
        $this->data[$id][$type]['duration'] = $this->data[$id][$type]['finish'] - $this->data[$id][$type]['start'];
        $this->data[$id][$type]['response'] = $response;
        $this->data[$id][$type]['info'] = $info;
        $this->data[$id][$type]['request'] = $request;
    }
    
    /**
     * Prepare data array
     *
     * @return void
     */
    function _prepareDataArray() {
        foreach($this->urls as $id=>$url) {
            $this->data[$id] = array('url'=>$url);
        }
    }
    
    /**
     * Generate output
     *
     * @return void
     */
    function _output() {
        
        // create header (titles)
        $tr = $this->_add('tr');
        $this->_add('th',$tr,'URL',array('rowspan'=>2));
        $this->_add('th',$tr,'SINGLE CURL',array('colspan'=>5))->setStyle('text-align','center');
        $this->_add('th',$tr,'MULTI CURL (window size <span style="color:red">'.$this->rc->window_size.'</span>)',array('colspan'=>5))->setStyle('text-align','center');

        $tr = $this->_add('tr');
        $this->_add('th',$tr,array('order','start','finish','duration (s)','data'));
        $this->_add('th',$tr,array('order','start','finish','duration (s)','data'));
        
        // create body (data)
        foreach($this->data as $id=>$data) {
            $tr = $this->_add('tr');
            
            $this->_add('td',$tr,'['.$id.'] '.$data['url']);
            
            foreach(array('single','multi') as $type) {
                $last_td = $this->_add('td',$tr,array(
                    '#'.$data[$type]['order'],
                    number_format($data[$type]['start'] - $this->totals[$type]['start'],3,'.',''),
                    number_format($data[$type]['finish'] - $this->totals[$type]['start'],3,'.',''),
                    number_format($data[$type]['duration'],3,'.',''),
                    null
                ));

                $b1 = $last_td->add('View')->setElement('i')->setAttr('title','Request')->addClass('ui-icon ui-icon-circle-arrow-n float-left')->setStyle('cursor','pointer');
                $b2 = $last_td->add('View')->setElement('i')->setAttr('title','Response')->addClass('ui-icon ui-icon-circle-arrow-s float-left')->setStyle('cursor','pointer');
                $b3 = $last_td->add('View')->setElement('i')->setAttr('title','Info')->addClass('ui-icon ui-icon-info float-left')->setStyle('cursor','pointer');
                
                $self = $this;
                /*
                $b1->add('misc/PageInFrame')->bindEvent('click','Request: '.$data['url'])
                    ->set(function($page)use($self,$data,$type){
                        $page->add('View')->setElement('pre')->set(print_r($data[$type]['request'],true));
                    });
                $b2->add('misc/PageInFrame')->bindEvent('click','Response: '.$data['url'])
                    ->set(function($page)use($self,$data,$type){
                        $page->add('View')->setElement('pre')->set(print_r($data[$type]['response'],true));
                    });
                $b3->add('misc/PageInFrame')->bindEvent('click','Info: '.$data['url'])
                    ->set(function($page)use($self,$data,$type){
                        $page->add('View')->setElement('pre')->set(print_r($data[$type]['info'],true));
                    });
                */
                $b1->js('click')->univ()
                    ->dialogBox(array('title'=>'Request: '.$data['url'],'autoOpen'=>true))
                    ->html('<pre>'.htmlentities(print_r($data[$type]['request'],true), ENT_COMPAT, 'UTF-8').'</pre>');
                $b2->js('click')->univ()
                    ->dialogBox(array('title'=>'Response: '.$data['url'],'autoOpen'=>true))
                    ->html('<pre>'.htmlentities(print_r($data[$type]['response'],true), ENT_COMPAT, 'UTF-8').'</pre>');
                $b3->js('click')->univ()
                    ->dialogBox(array('title'=>'Info: '.$data['url'],'autoOpen'=>true))
                    ->html('<pre>'.htmlentities(print_r($data[$type]['info'],true), ENT_COMPAT, 'UTF-8').'</pre>');
            }
        }
        
        // create footer (totals)
        $tr = $this->_add('tr');

        $this->_add('th',$tr,'Totals:',array('colspan'=>1))->setStyle('text-align','right');
        
        foreach(array('single','multi') as $type) {
            $this->_add('th',$tr,array(
                '',
                number_format($this->totals[$type]['start'] - $this->totals[$type]['start'],3,'.',''),
                number_format($this->totals[$type]['finish'] - $this->totals[$type]['start'],3,'.',''),
                '<span style="color:red">'.number_format($this->totals[$type]['duration'],3,'.','').'</span>',
                ''
            ));
        }
    }
    
    /**
     * Create simple ATK View with set type, content, attributes etc.
     *
     * @access private
     * @return View
     */
    private function _add($el = 'td', $p = null, $content = null, $attrs = null){
        if(is_null($p)) $p = $this->out;
        if(is_array($content)) {
            foreach($content as $c) $v = $this->_add($el, $p, $c, $attrs);
        } else {
            $v = $p->add('View')->setElement($el);
            if(isset($this->css[$el])) $v->addStyle($this->css[$el]);
            if(!is_null($content)) $v->setHTML($content);
            if(!is_null($attrs)) $v->setAttr($attrs);
        }
        return $v;
    }
}












