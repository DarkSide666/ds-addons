<?php
namespace RollingCurl;

/**
 * A group of curl requests.
 *
 * @throws RollingCurlGroup_Exception *
 */
class RollingCurlGroup {
    /**
     * @var string group name
     */
    protected $name;

    /**
     * @var int total number of requests in a group
     */
    protected $num_requests = 0;

    /**
     * @var int total number of finished requests in a group
     */
    protected $finished_requests = 0;

    /**
     * @var array requests array
     */
    private $requests = array();

    /**
     * @param string $name group name
     * @return void
     */
    function __construct($name) {
        $this->name = $name;
    }

    /**
     * @return void
     */
    public function __destruct() {
        unset($this->name, $this->num_requests, $this->finished_requests, $this->requests);
    }

    /**
     * Adds request to a group
     *
     * @throws RollingCurlGroup_Exception
     * @param RollingCurlGroup_Request|array $request
     * @return bool
     */
    function add($request) {
        if ($request instanceof RollingCurlGroup_Request) {
            $request->setGroup($this);
            $this->num_requests++;
            $this->requests[] = $request;
        }
        else if (is_array($request)) {
            foreach ($request as $req)
            $this->add($req);
        }
        else
            throw new RollingCurlGroup_Exception("add: Request needs to be of instance RollingCurlGroup_Request");

        return true;
    }

    /**
     * @throws RollingCurlGroup_Exception
     * @param RollingCurl $rc
     * @return bool
     */
    function addToRC(RollingCurl $rc){
        $ret = true;

        while (count($this->requests) > 0){
            $ret1 = $rc->add(array_shift($this->requests));
            if (!$ret1)
                $ret = false;
        }

        return $ret;
    }

    /**
     * Override to implement custom response processing.
     *
     * Don't forget to call parent::process().
     *
     * @param string $output received page body
     * @param array $info holds various information about response such as HTTP response code, content type, time taken to make request etc.
     * @param RollingCurlRequest $request request used
     * @return void
     */
    function process($output, $info, $request) {
        $this->finished_requests++;

        if ($this->finished_requests >= $this->num_requests)
            $this->finished();
    }

    /**
     * Override to execute code after all requests in a group are processed.
     *
     * @return void
     */
    function finished() {
    }

}



/**
 * Group version of rolling curl
 */
class GroupRollingCurl extends RollingCurl {

    /**
     * @var mixed common callback for all groups
     */
    private $group_callback = null;

    /**
     * @param string $output received page body
     * @param array $info holds various information about response such as HTTP response code, content type, time taken to make request etc.
     * @param RollingCurlRequest $request request used
     * @return void
     */
    protected function process($output, $info, $request) {
        if ($request instanceof RollingCurlGroup_Request)
            $request->process($output, $info);

        if (is_callable($this->group_callback))
            call_user_func($this->group_callback, $output, $info, $request);
    }

    /**
     * @param mixed $callback common callback for all groups
     * @return void
     */
    function __construct($callback = null) {
        $this->group_callback = $callback;

        parent::__construct(array(&$this, "process"));
    }

    /**
     * Adds a group to processing queue
     *
     * @param RollingCurlGroup|Request $request
     * @return bool
     */
    public function add($request) {
        if ($request instanceof RollingCurlGroup)
            return $request->addToRC($this);
        else
            return parent::add($request);
    }

    /**
     * Execute processing
     *
     * @param int $window_size Max number of simultaneous connections
     * @return bool|string
     */
    public function execute($window_size = null) {
        if (count($this->requests) == 0)
            return false;

        return parent::execute($window_size);
    }
}
