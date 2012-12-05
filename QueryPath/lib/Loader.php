<?php
namespace QueryPath;

class Loader extends \AbstractObject {

    function init(){
        parent::init();
        
        require_once 'QueryPath'.DIRECTORY_SEPARATOR.'QueryPath.php';
    }
    
    function qp($document = NULL, $string = NULL, $options = array()){
        return qp($document, $string, $options);
    }

    function htmlqp($document = NULL, $selector = NULL, $options = array()){
        return htmlqp($document, $selector, $options);
    }
}
