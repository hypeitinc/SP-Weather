<?php
/*------------------------------------------------------------------------
# mod_sp_weather - Weather Module by JoomShaper.com
# ------------------------------------------------------------------------
# Author    JoomShaper http://www.joomshaper.com
# Copyright (C) 2010 - 2019 JoomShaper.com. All Rights Reserved.
# License - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomshaper.com
-------------------------------------------------------------------------*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$location   = (trim($params->get('locationTranslated'))=='') ? $params->get('location') : $params->get('locationTranslated');
$forecast = ( isset($data['forecast']) && $data['forecast']) ? $data['forecast'] : array();
$data = $data['query']['results']['channel'];

?>
    <div id="weather_sp1_id<?php echo $moduleID; ?>" class="weather_sp1<?php echo $moduleclass_sfx; ?>">

        <div class="weather_sp1_c">
            <div class="weather_sp1_cleft">
                <img class="spw_icon_big" src="<?php echo  $helper->icon( $data['item']['condition']['code'] ) ?>" title="<?php 
                echo $helper->txt2lng($data['item']['condition']['text']);
                ?>" alt="<?php echo $helper->txt2lng($data['item']['condition']['text']); ?>" />

                <br style="clear:both" />
                <p class="spw_current_temp">
                    <?php if ($params->get('tempUnit')=='f') { ?>
                        <?php echo  $data['item']['condition']['temp']. JText::_('SP_WEATHER_F'); ?>	
                    <?php } else { ?>
                        <?php echo $data['item']['condition']['temp']. JText::_('SP_WEATHER_C'); ?>
                    <?php } ?>
                </p>
            </div>
            
            <div class="weather_sp1_cright">
                <?php if($params->get('city')==1) { ?>
                    <p class="weather_sp1_city">
                        <?php echo $location ?>
                    </p> 
                <?php } ?>

                <?php if($params->get('condition')==1) { ?>
                <div class="spw_row">
                    <?php echo $helper->txt2lng($data['item']['condition']['text']); ?>
                </div>
                <?php } ?>

                <?php if($params->get('humidity')==1) { ?>
                    <div class="spw_row">
                        <?php echo JText::_('SP_WEATHER_HUMIDITY');  ?>: <?php echo $helper->Numeric2Lang($data['atmosphere']['humidity']); ?>%
                    </div>
                <?php } ?>

                <?php if($params->get('wind')==1) { ?>
                    <div class="spw_row"><?php echo JText::_('SP_WEATHER_WIND');  ?>: <?php 
                        $compass = array('N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW', 'N');
                        $data['wind']['direction'] =  (isset($data['wind']['direction']) && $data['wind']['direction']) ? $compass[round($data['wind']['direction'] / 22.5)] . JText::_('SP_WEATHER_AT') : '';

                        echo JText::_($data['wind']['direction']) . $helper->Numeric2Lang($data['wind']['speed']) . ' ' . JText::_(($data['units']['speed'])); ?>
                    </div>
                <?php } ?>
            </div> <!-- /.weather_sp1_cright -->

            <div style="clear:both"></div>		
        </div> <!-- /.weather_sp1_c -->

        <div style="clear:both"></div>
        <?php if ($params->get('forecast')!='disabled') { ?>
            <div class="weather_sp1_forecasts layout-<?php echo $params->get('tmpl_layout', ''); ?>">
                <?php

                $fcast = (int) $params->get('forecast');
                $j = 1;
                unset($forecast[0]);

                foreach($forecast as $i=>$value ) {     
                    if($fcast<$j) break;

                    if ($params->get('tmpl_layout')=='list') { ?>
                        <div class="list_<?php echo ($i%2 ? 'even' : 'odd') ?>">
                            <span class="weather_sp1_list_day">
                                <?php  echo $helper->txt2lng(JHtml::date($value->dt , 'D')); ?>
                            </span>
                            <?php if ($params->get('tempUnit')=='f') { ?>
                                <span class="weather_sp1_list_temp">
                                    <?php  echo $helper->convertUnit( $helper->tempConvert($value->temp->min) , 'f' ) . '&nbsp;' . $params->get('separator') . '&nbsp;' . $helper->convertUnit( $helper->tempConvert($value->temp->max) , 'f' ); ?>
                                </span>
                            <?php } else { ?>
                                <span class="weather_sp1_list_temp">
                                    <?php  echo $helper->convertUnit( $value->temp->min, 'c' ) . '&nbsp;' . $params->get('separator') . '&nbsp;' . $helper->convertUnit( $value->temp->max, 'c' ); ?>
                                </span>
                            <?php } ?>
                            <span class="weather_sp1_list_icon">
                                <img class="spw_icon" src="<?php echo $helper->icon( $value->weather[0]->icon ); ?>" align="right" title="<?php echo $helper->txt2lng( $value->weather[0]->main ); ?>" alt="<?php echo $helper->txt2lng($value->weather[0]->description); ?>" />
                            </span>
                            <div style="clear:both"></div>
                        </div>
                        <?php } else { ?> 
                            <div class="block_<?php echo ($i%2 ? 'even' : 'odd') ?>" style="float:left;width:<?php echo round(100/$fcast) ?>%">
                                <span class="weather_sp1_day">
                                    <?php  echo $helper->txt2lng(JHtml::date($value->dt , 'D')); ?>
                                </span>
                                <br style="clear:both" />
                                <span class="weather_sp1_icon">
                                    <img  class="spw_icon" src="<?php echo $helper->icon( $value->weather[0]->icon ); ?>" title="<?php  echo $helper->txt2lng( $value->weather[0]->description ); ?>" alt="<?php echo $helper->txt2lng( $value->weather[0]->main ); ?>" />
                                </span>
                                <br style="clear:both" />
                                <?php if ($params->get('tempUnit')=='f') { ?>
                                    <span class="weather_sp1_temp">
                                        <?php echo $helper->convertUnit( $helper->tempConvert($value->temp->min) , 'f' ) . '&nbsp;' . $params->get('separator') . '&nbsp;' . $helper->convertUnit( $helper->tempConvert($value->temp->max) , 'f' ); ?>
                                    </span>
                                <?php } else { ?>
                                    <span class="weather_sp1_temp">
                                        <?php echo $helper->convertUnit( $value->temp->min, 'c' ) . '&nbsp;' . $params->get('separator') . '&nbsp;' . $helper->convertUnit( $value->temp->max, 'c' ); ?>
                                    </span>
                                <?php } ?>   
                            <br style="clear:both" />
                        </div>
                    <?php } ?>
                <?php $j++; } ?>
            </div>
        <?php } ?>

    <div style="clear:both"></div>
</div>