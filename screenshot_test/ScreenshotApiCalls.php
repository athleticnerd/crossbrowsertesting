<?php
/**
 * Created by PhpStorm.
 * User: dlorenz
 * Date: 07/10/16
 * Time: 09:10
 */

namespace comwrap_cbt\screenshot_test;

class ScreenshotApiCalls extends AbstractScreenshotApi {
    private $username;
    private $password;

    /**
     * ApiCalls constructor.
     * @param $username
     * @param $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        parent::__construct($username, $password);
    }

    /**
     * @param $params
     */
    public function startNewTest($params){
        $this->currentTest = $this->callApi($this->baseUrl, 'POST', $params);
    }

    /**
     * @return mixed
     */
    public function updateTestInfo(){
        $url = $this->baseUrl . "/" . $this->getTestId();
        return $this->callApi($url, 'GET');
    }

    /**
     * @return mixed
     */
    public function getTestId(){
        return $this->currentTest->screenshot_test_id;
    }

    /**
     *
     */
    public function printTestBrowsers(){
        if ($this->currentTest){
            foreach ($this->currentTest->versions[0]->results as $result) {
                print $result->os->name  . TAB . $result->browser->name . TAB . $result->resolution->name . EOL;
            }
        }
    }

    /**
     * @return bool
     */
    public function isTestComplete(){
        $this->currentTest = $this->updateTestInfo();
        return !$this->currentTest->versions[0]->active;
    }

    /**
     * @return mixed
     */
    public function getScreenshotBrowsers(){
        $url = $this->baseUrl . "/browsers";
        return $this->callApi($url, 'GET');
    }

    /**
     * @param bool $params
     * @return mixed
     */
    public function getAllTests($params = false){
        $url = $this->baseUrl;
        $result = $this->callApi($url, 'GET',$params);
        $this->recordCount = $result->meta->record_count;
        $this->allTests = $result->screenshots;
        return $this->allTests;
    }

    /**
     * @param bool $screenshotTestId
     * @param bool $screenshotTestVersion
     * @param bool $screenshotTarget
     * @param bool $screenshotBase
     * @param string $format
     * @param int $tolerance
     * @param null $callback
     * @return mixed
     */
    public function getCompareSingleScreenshot(
        $screenshotTestId = false,
        $screenshotTestVersion = false,
        $screenshotTarget = false,
        $screenshotBase = false,
        $format = 'json',
        $tolerance = 30,
        $callback = null
    ) {
        if($screenshotTestId == false ||
            $screenshotTestVersion == false ||
            $screenshotTarget == false ||
            $screenshotBase == false)
        {
            throw new \Exception('Screenshot parameters are incorrect.');
        }

        if($format != 'json' && $format != 'jsonp') {
            throw new \Exception('Format parameter is incorrect. (Only json or jsonp as string)');
        }

        $url = $this->baseUrl . DS . $screenshotTestId . DS . $screenshotTestVersion . DS . $screenshotTarget . DS . 'comparison' . DS . $screenshotBase . '?format=' . $format . '&tolerance=' . $tolerance;

        if($callback) {
            $url .= '&callback=' . $callback;
        }

        $return = $this->callApi($url, 'GET');

        return $return;
    }
}
?>