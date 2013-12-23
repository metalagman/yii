<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

class CurrencyColumn extends TbDataColumn
{
    public $currency = 'RUR';

    protected function renderDataCellContent($row, $data)
    {
        if ($this->value !== null)
            $value = $this->evaluateExpression($this->value, array('data' => $data, 'row' => $row));
        elseif ($this->name !== null)
            $value = CHtml::value($data, $this->name);
        echo $value === null ?
            $this->grid->nullDisplay :
            Yii::app()->numberFormatter->formatCurrency($value, $this->currency);
    }
}