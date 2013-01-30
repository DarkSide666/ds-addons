<?php
namespace RollingCurl;

/**
 * ...
 */
abstract class RollingCurlGroup_Request extends RollingCurl_Request {
    private $group = null;

    /**
     * Set group for this request
     *
     * @throws RollingCurlGroup_Exception
     * @param group The group to be set
     */
    function setGroup($group) {
        if (!($group instanceof RollingCurlGroup))
            throw new RollingCurlGroup_Exception("setGroup: group needs to be of instance RollingCurlGroup");

        $this->group = $group;
    }

    /**
     * Process the request
     *
     *
     */
    function process($output, $info) {
        if ($this->group)
            $this->group->process($output, $info, $this);
    }

    /**
     * @return void
     */
    public function __destruct() {
        unset($this->group);
        parent::__destruct();
    }
}
