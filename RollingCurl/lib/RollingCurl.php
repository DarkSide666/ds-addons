<?php
namespace RollingCurl;

/**
 * Class that holds a rolling queue of curl requests.
 *
 * @throws RollingCurlException
 */
class RollingCurl extends \AbstractObject {
    /**
     * @var int
     *
     * Window size is the max number of simultaneous connections allowed.
     *
     * REMEMBER TO RESPECT THE SERVERS:
     * Sending too many requests at one time can easily be perceived
     * as a DOS attack. Increase this window_size if you are making requests
     * to multiple servers or have permission from the receving server admins.
     */
    private $window_size = 5;

    /**
     * @var float
     *
     * Timeout is the timeout used for curl_multi_select.
     */
    private $timeout = 10;

    /**
     * @var string|array|closure
     *
     * Callbefore function to be applied exactly before request start executing.
     */
    private $callbefore;

    /**
     * @var string|array|closure
     *
     * Callback function to be applied to each result.
     */
    private $callback;

    /**
     * @var array
     *
     * Set your base options that you want to be used with EVERY request.
     */
    protected $options = array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/6.0 (Windows NT 6.2; WOW64; en-US; rv:16.0.1) Gecko/20121011 Firefox/16.0.1',
        /**CURLOPT_PROXY => 'kaste.vzd.gov.lv:8080',*/
    );

    /**
     * @var array
     */
    private $headers = array();

    /**
     * @var Request[]
     *
     * The request queue
     */
    private $requests = array();

    /**
     * @var RequestMap[]
     *
     * Maps handles to request indexes
     */
    private $requestMap = array();

    /**
     * Object initialization for ATK
     * 
     * @return void
     */
    public function init() {
        parent::init();
        if(!$this->owner instanceof \ApiCLI) throw $this->exception(__CLASS__ . ' can only be added to API!');
        //$this->api->curl = $this;
    }

    /**
     * @return void
     */
    public function __destruct() {
        unset($this->window_size, $this->callbefore, $this->callback, $this->options, $this->headers, $this->requests, $this->requestMap);
    }


    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        return (isset($this->{$name})) ? $this->{$name} : null;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function __set($name, $value) {
        // append the base options & headers
        if ($name == "options" || $name == "headers") {
            $this->{$name} = $value + $this->{$name};
        } else {
            $this->{$name} = $value;
        }
        return true;
    }

    /**
     * Callbefore function to be applied exactly before request start executing.
     *
     * Can be specified as 'my_function' or array($object, 'my_method')
     * or closure like function($request){}
     *
     * Function should take one parameter: $request - the original request
     *
     * @param mixed $callbefore
     * @return RollingCurl
     */
    function setCallbefore($callbefore = null) {
        $this->callbefore = $callbefore;
        return $this;
    }
    
    /**
     * Callback function to be applied to each result.
     *
     * Can be specified as 'my_function' or array($object, 'my_method')
     * or closure like function($response, $info, $request){}
     *
     * Function should take three parameters: $response, $info, $request.
     * $response is response body, $info is additional curl info,
     * $request is the original request
     *
     * @param mixed $callback
     * @return RollingCurl
     */
    function setCallback($callback = null) {
        $this->callback = $callback;
        return $this;
    }
    
    /**
     * Sets window size for multi curl requests
     * 
     * @param int $i
     * @return RollingCurl
     */
    function setWindowSize($i) {
        $this->window_size = $i;
        return $this;
    }

    /**
     * Add a request to the request queue
     *
     * @param Request $request
     * @return RollincCurl
     */
    public function addRequest($request) {
        $this->requests[] = $request;
        return $this;
    }

    /**
     * Create new Request and add it to the request queue
     *
     * @param string $url
     * @param string $method
     * @param  $post_data
     * @param  $headers
     * @param  $options
     * @param  $callback_data
     * @return RollingCurl
     */
    public function request($url, $method = "GET", $post_data = null, $headers = null, $options = null, $callback_data = null) {
        $this->addRequest(new RollingCurl_Request($url, $method, $post_data, $headers, $options, $callback_data));
        return $this;
    }

    /**
     * Perform GET request
     *
     * @param string $url
     * @param  $headers
     * @param  $options
     * @param  $callback_data
     * @return RollingCurl
     */
    public function get($url, $headers = null, $options = null, $callback_data = null) {
        return $this->request($url, "GET", null, $headers, $options, $callback_data);
    }

    /**
     * Perform POST request
     *
     * @param string $url
     * @param  $post_data
     * @param  $headers
     * @param  $options
     * @param  $callback_data
     * @return RollingCurl
     */
    public function post($url, $post_data = null, $headers = null, $options = null, $callback_data = null) {
        return $this->request($url, "POST", $post_data, $headers, $options, $callback_data);
    }

    /**
     * Execute processing
     *
     * @param int $window_size Max number of simultaneous connections
     * @return string|bool
     */
    public function execute($window_size = null) {
        // rolling curl window must always be greater than 1
        if (count($this->requests) == 1) {
            return $this->single_curl();
        } else {
            // start the rolling curl. window_size is the max number of simultaneous connections
            return $this->rolling_curl($window_size);
        }
    }

    /**
     * Performs a single curl request
     *
     * @access private
     * @return string|bool
     */
    private function single_curl() {
        $request = array_shift($this->requests);
        $options = $this->get_options($request);
        
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        
        if($callbefore = $this->callbefore) {
            if (is_callable($this->callbefore)) {
                call_user_func($callbefore, $request);
            }
        }
        
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);

        // it's not neccesary to set a callback for one-off requests
        if ($callback = $this->callback) {
            if (is_callable($this->callback)) {
                call_user_func($callback, $output, $info, $request);
            }
        }
        else
            return $output;
        
        return true;
    }

    /**
     * Performs multiple curl requests
     *
     * @access private
     * @throws RollingCurl_Exception
     * @param int $window_size Max number of simultaneous connections
     * @return bool
     */
    private function rolling_curl($window_size = null) {
        if ($window_size)
            $this->window_size = $window_size;

        // make sure the rolling window isn't greater than the # of urls
        if (count($this->requests) < $this->window_size)
            $this->window_size = count($this->requests);

        if ($this->window_size < 2) {
            throw new RollingCurl_Exception("Window size must be greater than 1");
        }

        $master = curl_multi_init();

        // start the first batch of requests
        for ($i = 0; $i < $this->window_size; $i++) {
            $this->_add_handle($master,$i);
        }

        do {
            while (($execrun = curl_multi_exec($master, $running)) == CURLM_CALL_MULTI_PERFORM) ;
            if ($execrun != CURLM_OK)
                break; // TODO: CURL error checking ???
            
            // a request was just completed -- find out which one
            while ($done = curl_multi_info_read($master)) {

                // get the info and content returned on the request
                $info = curl_getinfo($done['handle']);
                $output = curl_multi_getcontent($done['handle']);

                // send the return values to the callback function
                if($callback = $this->callback) {
                    if (is_callable($callback)) {
                        $key = (string) $done['handle'];
                        $request = $this->requests[$this->requestMap[$key]];
                        unset($this->requestMap[$key]);
                        call_user_func($callback, $output, $info, $request);
                    }
                }

                // start a new request (it's important to do this before removing the old one)
                if ($i < count($this->requests) && isset($this->requests[$i])) {
                    $this->_add_handle($master,$i);
                    $i++;
                }

                // remove the curl handle that just completed
                curl_multi_remove_handle($master, $done['handle']);

            }

            // Block for data in / output; error handling is done by curl_multi_exec
            if ($running)
                curl_multi_select($master, $this->timeout);

        } while ($running);
        curl_multi_close($master);
        return true;
    }
    
    /**
     * Helper function to add new curl handle to multi-curl queue
     * 
     * @access private
     * @param Resource $master multi_curl_init resource
     * @param int $i ID of request
     * @return void
     */
    private function _add_handle($master,$i) {
        $ch = curl_init();
        $options = $this->get_options($this->requests[$i]);
        curl_setopt_array($ch, $options);
        curl_multi_add_handle($master, $ch);

        // Add to our request Maps
        $key = (string) $ch;
        $this->requestMap[$key] = $i;

        // call before function
        if($callbefore = $this->callbefore) {
            if (is_callable($callbefore)) {
                call_user_func($callbefore, $this->requests[$i]);
            }
        }
    }


    /**
     * Helper function to set up a new request by setting the appropriate options
     *
     * @access private
     * @param Request $request
     * @return array
     */
    private function get_options($request) {
        // options for this entire curl object
        $options = $this->__get('options');
        if (ini_get('safe_mode') == 'Off' || !ini_get('safe_mode')) {
            $options[CURLOPT_FOLLOWLOCATION] = 1;
            $options[CURLOPT_MAXREDIRS] = 5;
        }
        $headers = $request->headers;

        // append custom options for this specific request
        if ($request->options) {
            $options = $request->options + $options;
        }

        // set the request URL
        $options[CURLOPT_URL] = $request->url;

        // posting data w/ this request?
        if ($request->post_data) {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $request->post_data;
        }
        if ($headers) {
            $options[CURLOPT_HEADER] = 0;
            $options[CURLOPT_HTTPHEADER] = $headers;
        }

        return $options;
    }

}
