<?php
/**
 * Created by PhpStorm.
 * User: dlorenz
 * Date: 07/10/16
 * Time: 08:58
 */

namespace comwrap_cbt\screenshot_test;

/**
 * Class AbstractScreenshotApi
 * @package comwrap_cbt\screenshot_test
 */
abstract class AbstractScreenshotApi implements InterfaceScreenshotApi {

    public $baseUrl = "https://crossbrowsertesting.com/api/v3/screenshots";
    public $currentTest = null;
    public $allTests = array();
    public $recordCout = 0;

    private $username;
    private $password;

    /**
     * AbstractScreenshotApi constructor.
     * @param $username
     * @param $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param $params
     * @return mixed
     */
    abstract public function startNewTest($params);

    /**
     * @return mixed
     */
    abstract public function updateTestInfo();

    /**
     * @return mixed
     */
    abstract public function getTestId();

    /**
     * @return mixed
     */
    abstract public function printTestBrowsers();

    /**
     * @return mixed
     */
    abstract public function isTestComplete();

    /**
     * @return mixed
     */
    abstract public function getScreenshotBrowsers();

    /**
     * @param bool $params
     * @return mixed
     */
    abstract public function getAllTests($params = false);

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
    abstract public function getCompareSingleScreenshot(
        $screenshotTestId = false,
        $screenshotTestVersion = false,
        $screenshotTarget = false,
        $screenshotBase = false,
        $format = 'json',
        $tolerance = 30,
        $callback = null
    );

    /**
     * @param $api_url
     * @param string $method
     * @param bool $params
     * @return mixed
     */
    public function callApi($api_url, $method = 'GET', $params = false) {
        $apiResult = new \stdClass();
        $process = curl_init();
        switch ($method){
            case "POST":
                curl_setopt($process, CURLOPT_POST, 1);
                if ($params){
                    curl_setopt($process, CURLOPT_POSTFIELDS, http_build_query($params));
                    curl_setopt($process, CURLOPT_HTTPHEADER, array('User-Agent: php')); //important
                }
                break;
            case "PUT":
                curl_setopt($process, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($params){
                    curl_setopt($process, CURLOPT_POSTFIELDS, http_build_query($params));
                    curl_setopt($process, CURLOPT_HTTPHEADER, array('User-Agent: php')); //important
                }
                break;
            case 'DELETE':
                curl_setopt($process, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                if ($params){
                    $api_url = sprintf("%s?%s", $api_url, http_build_query($params));
                }
        }
        // Optional Authentication:
        curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($process, CURLOPT_USERPWD, $this->username . ":" . $this->password);
        curl_setopt($process, CURLOPT_URL, $api_url);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        $apiResult->content = curl_exec($process);
        $apiResult->httpResponse = curl_getinfo($process);
        $apiResult->errorMessage =  curl_error($process);
        $apiResult->params = $params;
        curl_close($process);
        //print_r($apiResult);
        $paramsString = $params ? http_build_query($params) : '';
        $response = json_decode($apiResult->content);
        if ($apiResult->httpResponse['http_code'] != 200){
            $message = 'Error calling "' . $apiResult->httpResponse['url'] . '" ';
            $message .= (isset($paramsString) ? 'with params "'.$paramsString.'" ' : ' ');
            $message .= '. Returned HTTP status ' . $apiResult->httpResponse['http_code'] . ' ';
            $message .= (isset($apiResult->errorMessage) ? $apiResult->errorMessage : ' ');
            $message .= (isset($response->message) ? $response->message : ' ');
            die($message);
        } else {
            $response = json_decode($apiResult->content);
            if (isset($response->status)){
                die('Error calling "' . $apiResult->httpResponse['url'] . '"' .(isset($paramsString) ? 'with params "'.$paramsString.'"' : '') . '". ' . $response->message);
            }
        }
        return $response;
    }
}
?>