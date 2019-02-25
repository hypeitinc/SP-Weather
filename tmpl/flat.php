<?php
/*------------------------------------------------------------------------
# mod_sp_weather - Weather Module by JoomShaper.com
# ------------------------------------------------------------------------
# Author    JoomShaper http://www.joomshaper.com
# Copyright (C) 2010 - 2014 JoomShaper.com. All Rights Reserved.
# License - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomshaper.com
-------------------------------------------------------------------------*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$location   = (trim($params->get('locationTranslated'))=='') ? $params->get('location') : $params->get('locationTranslated');
$forecast = ( isset($data['forecast']) && $data['forecast']) ? $data['forecast'] : array();
$data = $data['query']['results']['channel'];
        
?>
<div id="sp-weather-id<?php echo $moduleID; ?>" class="sp-weather<?php echo $moduleclass_sfx; ?> flat-layout">

    <div class="sp-weather-current">
        <div class="media">
            <div class="pull-left">
                <div class="sp-weather-icon">
                    <i class="meteocons-<?php echo  $helper->iconFont( $data['item']['condition']['code'] ) ?>" title="<?php echo $helper->txt2lng($data['item']['condition']['text']); ?>" alt="<?php echo $helper->txt2lng($data['item']['condition']['text']); ?>"></i>
                </div>
                <div class="sp-weather-current-temp">
                    <?php $temp=$data['item']['condition']['temp'] ?>

                    <?php if ($params->get('tempUnit')=='f') { ?>
                        <?php echo  round($temp,1) . JText::_('SP_WEATHER_F'); ?>    
                    <?php } else { ?>
                        <?php echo round($temp,1) . JText::_('SP_WEATHER_C'); ?>
                    <?php } ?>
                </div>
            </div>

            <div class="media-body">
                <?php if($params->get('city')==1) { ?>
                <h4 class="media-heading sp-weather-city"><?php echo $location ?></h4> 
                <?php } ?>

                <?php if( ($params->get('condition')) || ($params->get('humidity')) ) { ?>
                <div class="sp-condition-humidity">
                    <?php if($params->get('condition')) { ?>
                    <span class="sp-condition">
                        <?php echo $helper->txt2lng($data['item']['condition']['text']); ?>
                    </span>
                    <?php } ?>
                    <?php if($params->get('humidity')) { ?>
                    <span class="sp-humidity">
                        <?php echo JText::_('SP_WEATHER_HUMIDITY');  ?>: <?php echo $helper->Numeric2Lang($data['atmosphere']['humidity']); ?>%
                    </span>
                    <?php } ?>
                </div>
                <?php } ?>

                <?php if($params->get('wind')==1) { ?>
                <div class="spw_row">
                    <?php echo JText::_('SP_WEATHER_WIND');  ?>: <?php 
                    $compass = array('N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW', 'N');

                    $data['wind']['direction'] = (isset($data['wind']['direction']) && $data['wind']['direction']) ? $compass[round($data['wind']['direction'] / 22.5)] . JText::_('SP_WEATHER_AT') : '';

                    echo JText::_($data['wind']['direction']) . $helper->Numeric2Lang(round($data['wind']['speed']*2.2369363)) . ' ' . JText::_(($data['units']['speed'])); ?>
                </div>
                <?php } ?>

            </div>
        </div><!--/.media-->	
    </div><!--/.sp-weather-current-->

    <?php if ($params->get('forecast')!='disabled') { ?>
    <div class="sp-weather-forcasts layout-<?php echo $params->get('tmpl_layout', ''); ?>">
        <?php
        $fcast = (int) $params->get('forecast');
        $j = 1;
        // unset today's forecast
        unset($forecast[0]);
        foreach($forecast as $i=>$value ) {
            if($fcast<$j) break;
            if ($params->get('tmpl_layout')=='list') { ?>
                <div class="list list-<?php echo ($i%2 ? 'even' : 'odd') ?>">

                    <div class="media">
                        <div class="pull-left">
                            <div class="sp-weather-icon">
                                <i class="meteocons-<?php echo $helper->iconFont( $value->weather[0]->icon ) ?>" title="<?php echo $helper->txt2lng($value->weather[0]->main); ?>" alt="<?php echo $helper->txt2lng($value->weather[0]->description); ?>"></i>
                            </div>
                        </div>

                        <div class="media-body">
                            <div class="sp-weather-day">
                                <?php echo $helper->txt2lng(JHtml::date($value->dt , 'D')); ?>
                            </div>
                            <?php if ($params->get('tempUnit')=='f') { ?>
                                <div class="sp-weather-temp">
                                    <?php echo $helper->convertUnit( $helper->tempConvert($value->temp->min) , 'f') . '&nbsp;' . $params->get('separator') . '&nbsp;' . $helper->convertUnit( $helper->tempConvert($value->temp->max) , 'f' ); ?>
                                </div>
                            <?php } else { ?>
                                <div class="sp-weather-temp">
                                    <?php echo $helper->convertUnit( $value->temp->min, 'c' ) . '&nbsp;' . $params->get('separator') . '&nbsp;' . $helper->convertUnit( $value->temp->max, 'c' ); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>				
            <?php } else { ?> 
                <div class="grid grid-<?php echo ($i%2 ? 'even' : 'odd') ?>" style="width:<?php echo round(100/$fcast)+5 ?>%">
                    <div class="media">
                        <div class="pull-left">
                            <div class="sp-weather-icon">
                                <i class="meteocons-<?php echo  $helper->iconFont( $value->weather[0]->icon ) ?>" title="<?php echo $helper->txt2lng($value->weather[0]->main); ?>" alt="<?php echo $helper->txt2lng($value->weather[0]->description); ?>"></i>
                            </div>
                        </div>
                        <div class="media-body">
                            <?php if ($params->get('tempUnit')=='f') { ?>
                                <div class="sp-weather-temp">
                                    <?php echo round($helper->convertUnit( $helper->tempConvert($value->temp->min) , 'f' )) . '&nbsp;' . $params->get('separator') . '&nbsp;' . round($helper->convertUnit( $helper->tempConvert($value->temp->max) , 'f' )); ?>
                                </div>
                            <?php } else { ?>
                                <div class="sp-weather-temp">
                                    <?php echo round($helper->convertUnit( $value->temp->min, 'c' )) . '&nbsp;' . $params->get('separator') . '&nbsp;' . round($helper->convertUnit( $value->temp->max, 'c' )); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php $j++; } ?>
    </div>
    <?php } ?>
</div>