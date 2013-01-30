<?php
namespace RollingCurl;

/**
 * Class that represent a single curl request
 */
class RollingCurl_Request {
    public $url = false;
    public $method = 'GET';
    public $post_data = null;
    public $headers = null;
    public $options = null;
    public $callback_data = null;

    /**
     * @param string $url
     * @param string $method
     * @param  $post_data
     * @param  $headers
     * @param  $options
     * @return void
     */
    function __construct($url, $method = "GET", $post_data = null, $headers = null, $options = null, $callback_data = null) {
        $this->url = $url;
        $this->method = $method;
        $this->post_data = $post_data;
        $this->headers = $headers;
        $this->options = $options;
        $this->callback_data = $callback_data;
    }

    /**
     * @return void
     */
    public function __destruct() {
        unset($this->url, $this->method, $this->post_data, $this->headers, $this->options, $this->callback_data);
    }
}
