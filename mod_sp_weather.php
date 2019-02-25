<?php
/*------------------------------------------------------------------------
# mod_sp_weather - Weather Module by JoomShaper.com
# ------------------------------------------------------------------------
# Author    JoomShaper http://www.joomshaper.com
# Copyright (C) 2010 - 2019 JoomShaper.com. All Rights Reserved.
# License - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomshaper.com
-------------------------------------------------------------------------*/
// https://openweathermap.org
// no direct access
defined('_JEXEC') or die('Restricted access');    
$layout                 = $params->get('layout', 'default');
$moduleName             = basename(dirname(__FILE__));
$moduleID               = $module->id;
$document               = JFactory::getDocument();
$api_key                = $params->get('api_key', '');
$moduleclass_sfx        = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

// if not API KEY throw error 
if($api_key == ''){
    $html = '<p class="alert alert-warning">' . JText::_('MOD_SPWEATHER_APIKEY_DESC') .'</p>';
    echo $html;
    return false;
}

//Include helper.php
require_once (dirname(__FILE__).'/helper.php');
$helper     = new modSPWeatherHelper($params,$moduleID);
$data       = $helper->getData();

if($data['status']) {
    //backward compatibility
    $data['query']['results']['channel'] = $data;
    $data['query']['results']['channel']['item']['condition']['text'] = $data['current']->weather[0]->description;
    $data['query']['results']['channel']['item']['condition']['code'] = $data['current']->weather[0]->icon;
    
    $data['query']['results']['channel']['atmosphere']['humidity'] = $data['current']->main->humidity;
    $data['query']['results']['channel']['units']['speed'] = JText::_('SP_WEATHER_WIND_SPEED_UNIT_MPH');
    $data['query']['results']['channel']['wind']['speed'] = $data['current']->wind->speed;
    $data['query']['results']['channel']['wind']['direction'] = (isset($data['current']->wind->deg) && $data['current']->wind->deg) ? $data['current']->wind->deg : '';
    
    if ($params->get('tempUnit')=='f') {
        $data['query']['results']['channel']['item']['condition']['temp']  = $helper->tempConvert($data['current']->main->temp, 'f');
    } else {
        $data['query']['results']['channel']['item']['condition']['temp']  = $data['current']->main->temp;
    }
    
    if ($params->get('forecast')!='disabled') {
        $data['forecast'] = (array)$data['forecast']->list;
    }
} else {
    return false;
}
    

if ( (!empty($data['current']->main && !count((array)$data['current']->main)) ) || $data['status'] !== true) {
    echo '<p class="alert alert-warning">Cannot get ' . $params->get('location') . ' location in module ' . $moduleName . '. Please also make sure that you have inserted city name.</p>';
    return false;
} else {

    if ( ($layout == '_:default') ) {
        $document->addStylesheet(JURI::base(true) . '/modules/'.$moduleName.'/assets/css/' . $moduleName . '.css');
    } else {
        $document->addStylesheet(JURI::base(true) . '/modules/'.$moduleName.'/assets/css/flat.css');
    }

    require(JModuleHelper::getLayoutPath($moduleName, $layout));
}