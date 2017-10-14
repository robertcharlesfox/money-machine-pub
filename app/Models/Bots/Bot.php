<?php

use Sunra\PhpSimple\HtmlDomParser;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverDimension;

abstract class Bot extends Eloquent
{
    public $driver;

    public function helperGetAUrl($url, $id_element, $identifier = 'id')
    {
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
    }

    public function makeDriver($url, $session_name = '', $maximize_window = false, $refresh = false, $width = 800, $height = 950)
    {
        if ($session_name && Cache::has($session_name)) {
            $session_id = Cache::pull($session_name);
            $this->driver = RemoteWebDriver::createBySessionID($session_id, 'http://127.0.0.1:4445/wd/hub');
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
        if ($maximize_window) {
            $this->driver->manage()->window()->maximize();
        }
        Cache::put($session_name, $this->driver->getSessionID(), 30);
    }
}
