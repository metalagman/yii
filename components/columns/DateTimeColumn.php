<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

class DateTimeColumn extends TbDataColumn
{
    public $dateWidth = 'medium';
    public $timeWidth = 'medium';

    public $options = [];

    public function init()
    {
        parent::init();

        $this->headerHtmlOptions['style'] = 'text-align: center';
        $this->htmlOptions['style'] = 'text-align: center';

        if ($this->timeWidth !== false)
            return;

        $defaultOptions = [
            'language' => Yii::app()->language,
            'format' => 'dd.mm.yyyy',
        ];

        $this->options = CMap::mergeArray($defaultOptions, $this->options);

        Yii::import('bootstrap.widgets.TbDatePicker');
        $widget = new TbDatePicker();
        $widget->options = $this->options;
        $widget->registerClientScript();
        $widget->registerLanguageScript();

        $this->filterInputOptions['class'] = 'dateFilter';
        $this->filterInputOptions['style'] = 'text-align: center';

        /** @var CClientScript $cs */
        $cs = Yii::app()->clientScript;
        $cs->registerScript('reinstallDatePicker', '
            function reinstallDatePicker(id, data) {
                $(".dateFilter").each(function(){
                    $(this).datepicker('.CJavaScript::encode($this->options).');
                }).on("changeDate", function() {
                    $(".datepicker").remove();
                });
            }

            reinstallDatePicker()
        ', CClientScript::POS_READY);

        $this->grid->afterAjaxUpdate = 'reinstallDatePicker';
    }

    protected function renderDataCellContent($row, $data)
    {
        if ($this->value !== null)
            $value = $this->evaluateExpression($this->value, array('data' => $data, 'row' => $row));
        elseif ($this->name !== null)
            $value = CHtml::value($data, $this->name);
        echo $value === null ?
            $this->grid->nullDisplay :
            Yii::app()->dateFormatter->formatDateTime($value, $this->dateWidth, $this->timeWidth);
    }
}