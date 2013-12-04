<?php
/**
 * Created by PhpStorm.
 * User: lagman
 * Date: 04.12.13
 * Time: 11:04
 */
class FullCalendarWidget extends CWidget
{

    /** @var array */
    public $htmlOptions = [];

    /** @var EFullCalendar */
    protected $component;

    public function init()
    {
        Yii::import('application.extensions.fullcalendar.EFullCalendar');
        $this->component = Yii::app()->getComponent('fullcalendar') ? : new EFullCalendar;
        $this->component->init();
    }

    /**
     * @var string the name of the container element that contains the calendar. Defaults to 'div'.
     */
    public $tagName = 'div';

    public $options = [];

    /**
     * Run this widget.
     * This method registers necessary javascript and renders the needed HTML code.
     */
    public function run()
    {
        $id = $this->getId();
        $this->htmlOptions['id'] = $id;

        echo CHtml::openTag($this->tagName, $this->htmlOptions);
        echo CHtml::closeTag($this->tagName);

        $options = CMap::mergeArray($this->component->options, $this->options);
        $encodeOptions = CJavaScript::encode($options);

        Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $id, "$('#$id').fullCalendar($encodeOptions);");
    }
}