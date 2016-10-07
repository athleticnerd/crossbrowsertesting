<?php
/**
 * Created by PhpStorm.
 * User: dlorenz
 * Date: 07/10/16
 * Time: 11:48
 */

namespace comwrap_cbt;
use comwrap_cbt\screenshot_test\ScreenshotApiCalls;

class RunScreenshotTest
{
    /**
     * @param bool $username
     * @param bool $password
     * @param bool $url
     * @param bool $browserListName
     * @return ScreenshotApiCalls
     * @throws Exception
     */
    public function runNewTestFromBrowserList($username = false, $password = false, $url = false, $browserListName = false) {
        print EOL.'** Starting CBT from Browser list **'.EOL;

        if(!$username || !$password || !$url || !$browserListName) {
            throw new \Exception('Paramenter are incorrect.');
        }

        $params["url"] = $url;

        //set browsers
        //    $params["browsers"] = array();
        //    $params["browsers"][] = "Win7x64-C2|IE10|1400x1050";
        //    $params["browsers"][] = "Mac10.10|Chrome35x64";
        //    $params["browsers"][] = "GalaxyNote3-And44|MblChrome36";

        //other options
        $params["browser_list_name"] = $browserListName;
        //$params["login"] = "mydomain.com login"; //valid only if you've created a login profile with this name
        //$params["basic_username"] = "username"; //for basic auth urls only
        //$params["basic_password"] = "password"; //for basic auth urls only
        //$params["delay"] = 0; //delay for number of seconds to wait after page is loaded to start capturing screenshots

        //create api object and set auth info
        $screenshot = new ScreenshotApiCalls($username, $password);
        print "starting new screenshot test for " . $params["url"].EOL;

        $screenshot->startNewTest($params);

        print "screenshot_test_id is " . $screenshot->getTestId().EOL;
        print "view Screenshot Test on web here: https://app.crossbrowsertesting.com/screenshots/" . $screenshot->getTestId().EOL;

        print EOL."browsers to be tested are: ".EOL;
        $screenshot->printTestBrowsers();

        print "waiting on test to complete".EOL;
        $tries = 0;
        $maxTries = 100;
        while ($tries < $maxTries){
            if ($screenshot->isTestComplete()){
                print "screenshot test complete".EOL;

                return $screenshot;
            }
            else{
                sleep(2);
                $tries += 1;
            }
        }
        if ($tries >= $maxTries){
            throw new \Exception("screenshot did not complete after " . str($tries*2) . " seconds!");
        }
    }

    /**
     * @param bool $username
     * @param bool $password
     * @param bool $url
     * @param bool $browserListName
     * @return bool|mixed|null
     * @throws Exception
     */
    public function runAutomatedComparison($username = false, $password = false, $url = false, $browserListName = false) {
        /* Result URLs for user to view */
        $publicComparison = null;
        $privateResult = null;

        /* Parameters for function getCompareSingleScreenshot() */
        $screenshotTestId = null;
        $screenshotTestVersion = null;
        $screenshotTarget = null;
        $screenshotBase = null;

        /* return value of function */
        $comparison = null;

        print EOL . '** Starting new Layout Test **' . EOL;

        if(!$username || !$password || !$url || !$browserListName) {
            throw new \Exception('Paramenter are incorrect.');
        }

        try {
            $screenshot = $this->runNewTestFromBrowserList($username, $password, $url, $browserListName);

            foreach($screenshot->currentTest->versions as $testVersion) {
                $publicComparison = $testVersion->show_comparisons_public_url;
                $privateResult = $testVersion->show_results_web_url;

                $screenshotTestId = $screenshot->currentTest->screenshot_test_id;
                $screenshotTestVersion = $testVersion->version_id;
                $screenshotTarget = false;
                $screenshotBase = false;

                /* test is complete */
                if ($testVersion->result_count->total == $testVersion->result_count->successful && count($testVersion->results) > 1) {
                    if($testVersion->results[0]->state == "complete" && $testVersion->results[1]->state == "complete") {
                        $screenshotTarget = $testVersion->results[0]->result_id;
                        $screenshotBase = $testVersion->results[1]->result_id;
                        break;
                    }
                } elseif ($testVersion->result_count->failed > 0 || $testVersion->result_count->cancelled > 0) {
                    print EOL . '*** Test API failed ***' . EOL;
                    print 'Private: See here whats wrong: ' . $privateResult . EOL;
                    print 'Public: See here whats wrong: ' . $publicComparison . EOL;
                    die();
                }
            }

            if(isset($screenshotTestId, $screenshotTestVersion, $screenshotTarget, $screenshotBase)) {
                $comparison = $screenshot->getCompareSingleScreenshot($screenshotTestId, $screenshotTestVersion, $screenshotTarget, $screenshotBase, 'json', 30, null);
                print EOL . '** screenshot comparison complete **' . EOL;

                return $comparison;
            }
        } catch (\Exception $e) {
            print $e->getMessage();
        }
        return false;
    }

    /**
     * todo impl evaluation
     * @param null $comparison
     */
    public function evaluateComparison($comparison = null) {

    }

    public function testComparison($username = false, $password = false)
    {
        print EOL . '** Starting CrossBrowserTesting.com API v3 Run Screenshot Test example **' . EOL;
        //create api object and set auth info
        $screenshot = new ScreenshotApiCalls($username, $password);

        /* https://crossbrowsertesting.com/api/v3/screenshots/1831622/1928735/10305095/comparison/10305096?format=json&tolerance=30 */
        print serialize($screenshot->getCompareSingleScreenshot(1831622, 1928735, 10305095, 10305096, 'json', 30, null));

        print EOL . '** screenshot comparison complete **' . EOL;
    }


    function viewTestHistory($username = false, $password = false){
        if(!$username || !$password) {
            throw new \Exception('Paramenter are incorrect.');
        }
        print EOL."** Starting CrossBrowserTesting.com API v3 View Screenshot History example **".EOL;
        //set paging options
        $params["start"] = 0; //start with the last test run
        $params["num"] = 20; //how many to retrieve
        //filter results
        $params["url"] = "google"; //filter for only tests run that have 'google' somewhere in the URL
        $params["start_date"] = "2014-06-01"; //fitler to only tests run within a date range
        $params["end_date"] = "2014-10-01";
        $params["archived"] = false; //only include screenshot tests that are not archived
        //create api object and set auth info
        print "retrieving test history".EOL;
        $screenshots = new ScreenshotApiCalls($username, $password);
        $allTests = $screenshots->getAllTests($params);
        //show total number of tests
        print "There are " . $screenshots->recordCount . " tests for  the filters provided, showing " . count($allTests) . EOL;
        //print out results
        for ($i=0; $i<count($allTests); $i++){
            $test = $allTests[$i];
            $version = $test->versions[0];
            $start_date = $version->start_date;
            print ($i+1) . TAB . $start_date . TAB . $test->screenshot_test_id  . TAB . $test->url.EOL;
        }
    }

    function listScreenshotBrowsers($username = false, $password = false){
        if(!$username || !$password) {
            throw new \Exception('Paramenter are incorrect.');
        }

        print EOL."** Starting CrossBrowserTesting.com API v3 List Screenshot Browsers example **".EOL;
        //create api object and set auth info
        $screenshotApi = new ScreenshotApiCalls($username, $password);
        $oss = $screenshotApi->getScreenshotBrowsers();
        foreach ($oss as $os){
            foreach($os->browsers as $browser){
                print $os->name . TAB . $browser->name . TAB . $os->api_name . "|" . $browser->api_name.EOL;
            }
        }
    }
}