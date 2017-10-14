<?php namespace PredictIt;

use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverDimension;

use Cache;
use PiContest;
use PiQuestion;

class Navigator extends Base
{
    public $driver;
    private $sleep_time = 200000;

    public function visitQuestionMarket(PiQuestion $question, $session_name = 'TraderBot')
    {
        $this->makePiDriver($session_name, true);
        return $this->helperGetAUrl($question->url_of_market, 'data1');
    }

    public function visitCompetitionPage(PiContest $contest, $session_name = 'TraderBot')
    {
        $this->makePiDriver($session_name, true);
        $url = $contest->selenium_url ? $contest->selenium_url : $contest->url_of_answer;
        return $this->helperGetAUrl($url, 'contractList');
    }

    public function helperGetAUrl($url, $id_element, $identifier = 'id')
    {
        // @todo: if we're already on the right page, sometimes we refresh and sometimes we don't.
        // obviously needs a parameter
        // $current_url = $this->driver->getCurrentURL();
        // if (!stristr($current_url, $url)) {
            $this->driver->get($url);
            switch ($identifier) {
                case 'id':
                    $this->driver->wait()->until(
                      WebDriverExpectedCondition::refreshed(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id($id_element)))
                    );
                    break;
                
                case 'class':
                    $this->driver->wait()->until(
                      WebDriverExpectedCondition::refreshed(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className($id_element)))
                    );
                    break;
            }
        // }

        for ($i=0; $i < 7; $i++) { 
            usleep($this->sleep_time);
            $html = $this->driver->getPageSource();
            if ($html) {
                if ($i>0) {
                    usleep($this->sleep_time);
                }
                break;
            }
            echo $i . "--Dom is missing! " . PHP_EOL;
            usleep($this->sleep_time);
        }
        return $html;
    }

    public function makePiDriver($session_name, $authenticate = false)
    {
        $this->makeDriver('https://www.predictit.org', $session_name);
        if ($authenticate && $this->driver->getCurrentURL() == 'https://www.predictit.org/') {
            $this->piLogin();
        }
    }
    
    public function makeDriver($url, $session_name = '', $refresh = false, $width = 800, $height = 950)
    {
        // Cache::forget($session_name);
        if ($session_name && Cache::has($session_name)) {
            $session_id = Cache::pull($session_name);
            $this->driver = RemoteWebDriver::createBySessionID($session_id, 'http://127.0.0.1:4445/wd/hub');
            // @todo: check that this worked. If not, create new session from scratch.
            if ($refresh) {
                $this->driver->get($url);
            }
        }
        else {
            $capabilities = array(WebDriverCapabilityType::BROWSER_NAME => 'chrome');
            $this->driver = RemoteWebDriver::create('http://127.0.0.1:4445/wd/hub', $capabilities);
            $this->driver->get($url);

            $dim = new WebDriverDimension($width, $height);
            $this->driver->manage()->window()->setSize($dim);
        }
        Cache::put($session_name, $this->driver->getSessionID(), 30);
    }

    private function piLogin()
    {
        $dim = new WebDriverDimension(1200, 850);
        $this->driver->manage()->window()->setSize($dim);

        // Make sure the button to open the signin modal exists.
        $this->driver->wait()->until(
          WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::xpath('//a[@href="#SignInModal"]'))
        );
        $this->driver->findElement(WebDriverBy::xpath('//a[@href="#SignInModal"]'))->click();

        // Wait until the modal opens and the fields are available to enter data into.
        $this->driver->wait()->until(
          WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('Email'))
        );
        usleep($this->sleep_time);
        $this->driver->findElement(WebDriverBy::id('Email'))->sendKeys('robertcharlesfox@gmail.com');
        usleep($this->sleep_time);
        $this->driver->findElement(WebDriverBy::id('Password'))->sendKeys(env('PI_PW'));
        usleep($this->sleep_time);

        $this->driver->findElement(WebDriverBy::id('loginForm'))->submit();

        // Wait until the authenticated response page loads up.
        $this->driver->wait()->until(
          WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('shares'))
        );

        $dim = new WebDriverDimension(800, 850);
        $this->driver->manage()->window()->setSize($dim);
    }
}
