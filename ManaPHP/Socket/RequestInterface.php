<?php

namespace ManaPHP\Socket;

interface RequestInterface
{
    /**
     * @return \ManaPHP\Socket\RequestContext
     */
    public function getContext();

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($name = null, $default = null);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name);

    /**
     * @return string
     */
    public function getClientIp();

    /**
     * @return string
     */
    public function getRequestId();

    /**
     * @param string $request_id
     *
     * @return void
     */
    public function setRequestId($request_id = null);
}