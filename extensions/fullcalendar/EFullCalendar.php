<?php
/**
 * Created by PhpStorm.
 * User: lagman
 * Date: 04.12.13
 * Time: 10:55
 */

class EFullCalendar extends CApplicationComponent
{
    /** @var bool Force using files from cdn instead of local ones */
    public $useCDN = false;
    /** @var bool Use minified version of scripts */
    public $useMinified = true;
    /** @var string Version to use */
    public $version = "1.6.4";
    /** @var array Plugin options */
    public $options = [];

    public function init()
    {
        if (!$this->isInitialized) {
            /** @var CClientScript $cs */
            $cs = Yii::app()->clientScript;
            /** @var CAssetManager $am */
            $am = Yii::app()->assetManager;

            $cs->registerCoreScript('jquery');

            $jsFile = $this->useMinified ? 'fullcalendar.min.js ' : 'fullcalendar.js';
            $cssFile = 'fullcalendar.css';

            if ($this->useCDN) {
                $baseUrl = '//cdnjs.cloudflare.com/ajax/libs/fullcalendar/' . $this->version;
            } else {
                $baseUrl = $am->publish(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $this->version);
            }

            $cs->registerScriptFile($baseUrl . '/' . $jsFile);
            $cs->registerCssFile($baseUrl . '/' . $cssFile);

            $this->initFormat();
        }
    }

    protected function initFormat()
    {
        $locale = Yii::app()->getLocale();

        $this->options['monthNames'] = array_values($locale->getMonthNames('wide', true));
        $this->options['monthNamesShort'] = array_values($locale->getMonthNames('wide', true));

        $this->options['dayNames'] = array_values($locale->getWeekDayNames());
        $this->options['dayNamesShort'] = array_values($locale->getWeekDayNames('abbreviated'));

        $this->options['columnFormat'] = [
            'month' => 'ddd',
            'week' => 'ddd '.$locale->getDateFormat('short'),
            'day' => '',
        ];

        $this->options['timeFormat'] = [
            '' => $locale->getTimeFormat(),
            'agenda' => "{$locale->getTimeFormat()}{ - {$locale->getTimeFormat()}}",
        ];

        $this->options['axisFormat'] = $locale->getTimeFormat('short');

        /** @todo selection */
        $this->options['firstDay'] = 1;
    }

}