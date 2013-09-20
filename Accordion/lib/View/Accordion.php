<?php
namespace Accordion;
/**
 * Implementation of jQuery UI Accordion
 *
 * Use: 
 *  $ac=$this->add('Accordion/View_Accordion');
 *  $ac->addTab('Tab1')->add('LoremIpsum'); // static content
 *  $ac->addTabURL('details','Details');  // AJAX content
 */
class View_Accordion extends \View
{
    // template file
    protected $template_file='accordion';

    // templates
    public $tab_template = null;
    
    // options
    public $options = array();
    protected $default_options = array(
        'collapsible' => true,       // allow to expand/collapse tabs
        'active' => false,           // start with all tabs collapsed
        'autoHeight' => true,        // automatic tab height
        'beforeActivate' => array(), // array of JS callbacks before tab activation
        'activate' => array(),       // array of JS callbacks after activation
        );
    

    // should we show loader indicator while loading tabs
    public $show_loader = true;

    function init()
    {
        parent::init();
        $this->tab_template = $this->template->cloneRegion('tabs');
        $this->template->del('tabs');
    }
    /* Set tabs option, for example, 'active'=>'zero-based index of tab */
    function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }
    function render()
    {
        // generate options
        $opts = array_merge_recursive($this->default_options, $this->options);
        
        // add remote loading JS chain
        $opts['activate'][] = $this->js(null,'
                $url = $(ui.newHeader[0]).children("a").attr("href");
                $panel = $(ui.newPanel[0]);
                if ($url) {
                    ' . ($this->show_loader ? '$panel.atk4_loader().atk4_loader("showLoader");' : '') . '
                    $panel.load($url, function(){
                        ' . ($this->show_loader ? '$panel.atk4_loader().atk4_loader("hideLoader");' : '') . '
                    });
                }
                ');
        
        // set JS chains as Accortion options
        if ($opts['beforeActivate']) {
            $opts['beforeActivate'] = $this->js(null, $opts['beforeActivate'])
                ->_enclose(null, true);
        }
        if ($opts['activate']) {
            $opts['activate'] = $this->js(null, $opts['activate'])
                ->_enclose(null, true);
        }
        
        // render JUI accordion
        $this->js(true)
            ->accordion($opts);
        
        return parent::render();
    }
    /* Add tab and returns its content object so that you can add static content */
    function addTab($title, $name = null)
    {
        // add container
        $tab = $this->add('View_HtmlElement', $name, 'tabs');
        
        // set template
        $tab->template = clone $this->tab_template;
        $tab->template->set(array(
                    'url'      => null,//'#'.$tab->name,
                    'tab_name' => $title,
                    'tab_id'   => $tab->short_name,
                    ));
        
        // return tab
        return $tab;
    }
    /* Add tab which loads dynamically. Returns $this for chaining */
    function addTabURL($page, $title = null, $name = null)
    {
        // generate title if not passed as argument
        if (is_null($title)) {
            $title = ucwords(preg_replace('/[_\/\.]+/', ' ', $page));
        }
        
        // add tab and change URL and ID
        $tab = $this->addTab($title, $name);
        $tab->template->set(array(
                    'url'       => $this->api->url($page,array('cut_page'=>1)),
                    'tab_id'    => basename($page),
        ));
        
        return $this;
    }

	/** Default template*/
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
            //'js' => 'templates/js',
        ))->setParent($l);
    }
}
