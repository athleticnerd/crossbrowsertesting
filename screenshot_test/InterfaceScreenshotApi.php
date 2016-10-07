<?php
/**
 * Created by PhpStorm.
 * User: dlorenz
 * Date: 07/10/16
 * Time: 08:42
 */

namespace comwrap_cbt\screenshot_test;

/**
 * Interface InterfaceScreenshotApi
 * @package comwrap_cbt\screenshot_test
 */
interface InterfaceScreenshotApi
{
    /**
     * ScreenshotApiInterface constructor.
     * @param $username
     * @param $password
     */
    public function __construct($username, $password);

    /**
     * @param $api_url
     * @param string $method
     * @param bool $params
     * @return mixed
     */
    public function callApi($api_url, $method = 'GET', $params = false);
}
?>