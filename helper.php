<?php
/*------------------------------------------------------------------------
# mod_sp_weather - Weather Module by JoomShaper.com
# ------------------------------------------------------------------------
# author    JoomShaper http://www.joomshaper.com
# Copyright (C) 2010 - 2019 JoomShaper.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomshaper.com
-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modSPWeatherHelper {

    private $results = array('status'=> false);
    private $errors  = false;
    private $location;
    private $forecast_limit;
    private $api_key;
    private $params;
    private $moduleID;
    private $moduledir;
    private $api;
    private $cache_time;
    private $iconURL = 'http://openweathermap.org/img/w/%s.png';

    /**
    * Init Class Params
    * 
    * @param object $params
    * @param int $id
    */
    public function __construct($params, $id) {
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
        $this->params    = $params;
        $this->moduleID  = $id;
        $this->moduledir = basename(dirname(__FILE__));
        $this->location         = str_replace(' ', '%20', $this->params->get('location', 'San Francisco, US'));
        $this->forecast_limit   = $this->params->get('forecast', '7');
        $this->api_key          = $this->params->get('api_key', '');
        $this->cache_time       = $this->params->get('cacheTime', '900');
        
        // get current data
        $this->results['current']  = $this->_getWeatherData('current');

        // load current
        if($data_decode = json_decode($this->results['current'])) {
            if (isset($data_decode->main) && count((array)$data_decode->main)) {
                $this->results['status'] = true;
                $this->results['current'] = $data_decode;
            }
        } else {
            $this->throwError('CANNOT_DECODE_CURRENT_DATA');
        }
        
        // get forecast
        if($this->forecast_limit != 'disabled') { // if forecast is enable
            $this->results['forecast']  = $this->_getWeatherData('forecast');
            
            if($forecast_decode = json_decode($this->results['forecast']))  {
                if( count($forecast_decode->list) && $forecast_decode->list ) {
                    $this->results['forecast_status'] = true;
                    $this->results['forecast'] = (object) $forecast_decode;
                } else {
                 $this->throwError('CANNOT_FIND_FORECAST_DATA');
                }
            } else {
                $this->throwError('CANNOT_DECODE_FORECAST_DATA');
            }
        }

    }

    //Get Weather data
    private function _getWeatherData($type = 'current') {
        if($type == 'forecast') {
            $this->api  = 'http://api.openweathermap.org/data/2.5/forecast/daily?q='. $this->location .'&units=metric&cnt='. $this->forecast_limit .'&lang=en&appid=' . $this->api_key;
        } else {
            $this->api       = 'http://api.openweathermap.org/data/2.5/weather?q='. $this->location .'&units=metric&&appid=' . $this->api_key;
        }

        $results['data'] = array();
        // check cache dir or create cache dir
        $cache_path = JPATH_CACHE.'/'.$this->moduledir;
        if (!JFolder::exists($cache_path)){
            JFolder::create(JPATH_CACHE.'/'.$this->moduledir.'/'); 
        }
        
        if ($type == 'forecast') { // if data is forecast
            $cache_file = JPATH_CACHE.'/'.$this->moduledir.'/'.$this->moduleID.'-'.'forecast.json';
        } else { // if data is current weather
            $cache_file = JPATH_CACHE.'/'.$this->moduledir.'/'.$this->moduleID.'-'.'current.json';
        }
        // check cache file is exist and time isn't over:: default time is: 30 mins
        if (file_exists($cache_file) && (filemtime($cache_file) > (time() - 60 * $this->cache_time ))) {
            $results['data'] =  JFile::read($cache_file);
		} else {
            if( ini_get('allow_url_fopen') ) {
                try {
                    $results['data'] = file_get_contents($this->api);
                } catch (Exception $ex) {
                    $this->throwError('MAKESURE_FOPEN_OR_LCOATION');
                }
            } else {
                $results['data'] = $this->curl($this->api);
            }
            if( isset($results['data']) && !empty($results['data']) && count((array)$results['data']) ) {
                file_put_contents($cache_file, $results['data'], LOCK_EX);
            }
        }
        return $results['data'];
    }

    // Get Curl data
    protected function curl($url) {
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	    $data = curl_exec($ch);
        curl_close($ch);
        
	    return $data;
    }
    
    /**
    * Convert numeric number to language
    * 
    * @param int | string $number
    * @return language formatted text
    */
    public function Numeric2Lang($number, $prefix = 'SP_') {
        $number = (array) str_split($number);
        $formated = '';
        foreach($number as $no) {
            if (ctype_digit($no)) {
                $formated.=JText::_($prefix . $no);    
            } else $formated.=$no;
        }
        return $formated;
    }


    /**
    * Weather condition text converter
    * 
    * @param string $text
    * @return string
    */
    public function txt2lng($text) {
        $trans = array(" " => "_", "/" => "_", "(" => "", ')'=>'');
        $text = strtr($text, $trans);
        return JText::_('SP_WEATHER_'.strtoupper($text));
    }

    /**
    * Convert temparature
    * 
    * @param mixed $value
    * @param mixed $unit
    * @param mixed $tempType
    */
    public function convertUnit($value, $unit) {    
        $txt  = $this->Numeric2Lang($value);
        $txt .= ( strtolower($unit)=='c') ? JText::_('SP_WEATHER_'. 'C') : JText::_('SP_WEATHER_'. 'F');
        return $txt;
    }    

    /**
    * weather condition to icon file name
    * 
    * @param mixed $icon
    * @param mixed $path
    */
    public function icon($condition) {
        return sprintf($this->iconURL, $condition);
    } 

    /**
    * weather condition to icon font
    * 
    * @param mixed $icon
    * @param mixed $path
    */
    public function iconFont($condition = '') {
        $night       = (strpos($condition, 'n') !== false) ?'-night':'';
        $cond_number = (int)substr($condition, 0, -1);
        $fontIcon   = array(
            "0"     => 'other',
            "1"    => 'sunny',
            "2"    => 'cloudy',
            "3"    => 'mostly-cloudy',
            "4"    => 'partly-cloudy',
            "9"    => 'chance-of-storm',
            "10"   => 'rain',
            "11"    => 'thunderstorm', 
            "13"    => 'snow',
            "50"    => 'foggy',
        );
        return $fontIcon[$cond_number] . $night;
    }

    /**
    * Run function to load data from source
    * @return string
    */
    public function getData() {
        return $this->results;
    }

    // convert temperature 
    public function tempConvert($value, $convert_type = 'f') {
        if($convert_type == 'f') { // convert celsius to fahrenheit (f for fahrenheit)
            return $value * 1.8 + 32;
        } else { // convert fahrenheit to celsius
            return ($value - 32) / 1.8;
        }
    }

    // throw common error
    public function throwError($message = 'COMMON') {
        if(!$this->errors) {
            $this->errors = true;
            $this->results['status'] = false;
            $this->results['message']  = '';
            if ($message == 'INSERT_API_KEY') {
                $this->results['message'] .= '<p class="alert alert-warning">' . JText::_('MOD_SPWEATHER_ERROR_'. $message) .'</p>';
            } else {
                $this->results['message'] .= '<p class="alert alert-warning">' . JText::_('MOD_SPWEATHER_ERROR_'. $message) . ' ' . JText::_('MOD_SPWEATHER_ERROR_LOCATION_ERROR') .'</p>';
            }   
            echo $this->results['message'];
        }
    }

}
