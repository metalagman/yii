<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

class AgeColumn extends TbDataColumn
{

    public function init()
    {
        parent::init();

        $this->headerHtmlOptions['style'] = 'text-align: center; width: 128px';
        $this->htmlOptions['style'] = 'text-align: center';
    }

    protected function renderDataCellContent($row, $data)
    {
        if ($this->value !== null)
            $value = $this->evaluateExpression($this->value, array('data' => $data, 'row' => $row));
        elseif ($this->name !== null)
            $value = CHtml::value($data, $this->name);
        if ($value === null) {
            echo $this->grid->nullDisplay;
        } else {
            $tz  = new DateTimeZone(Yii::app()->timeZone);

            $today = new DateTime('now', $tz);
            $today->setTime(0, 0, 0);

            $birthday = new DateTime($value);
            $age = $birthday->diff($today)->y;

            $value = $value . ' (' . Yii::t('app', "{n} year|{n} years", $age) . ')';

            if ($today->format('d.m') == $birthday->format('d.m')) {
                $value = '<i class="icon-gift"></i> '.$value;
            }

            echo $value;
        }
    }
}