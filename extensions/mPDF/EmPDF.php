<?php
/**
 * Created by PhpStorm.
 * User: lagman
 * Date: 26.11.13
 * Time: 15:09
 */

class EmPDF extends CApplicationComponent
{
    public $defaultParams = [
        'mode' => '', //  This parameter specifies the mode of the new document.
        'format' => 'A4', // format A4, A5, ...
        'default_font_size' => 0, // Sets the default document font size in points (pt)
        'default_font' => '', // Sets the default font-family for the new document.
        'margin_left' => 15, // margin_left. Sets the page margins for the new document.
        'margin_right' => 15, // margin_right
        'margin_top' => 16, // margin_top
        'margin_bottom' => 16, // margin_bottom
        'margin_header' => 9, // margin_header
        'margin_footer' => 9, // margin_footer
        'orientation' => 'P', // landscape or portrait orientation
    ];

    public function init()
    {
        require_once(Yii::getPathOfAlias('application.vendors').'/mPDF/mpdf.php');

        $this->initDir('application.runtime.mpdf.tmp', '_MPDF_TEMP_PATH');
        $this->initDir('application.runtime.mpdf.ttfontdata', '_MPDF_TTFONTDATAPATH');
    }

    /**
     * @param string $alias Yii path alias to use
     * @param string $constant Constant to set
     */
    private function initDir($alias, $constant)
    {
        $path = Yii::getPathOfAlias($alias);
        if ($path != false) {
            @mkdir($path, 0777, true);
            define($constant, $path);
        }
    }

    /**
     * mPDF instances generator
     *
     * @return mPDF
     */
    public function entity()
    {
        $args = func_get_args();
        $args = CMap::mergeArray($this->defaultParams, $args);

        $r = new ReflectionClass('mPDF');
        return $r->newInstanceArgs(array_values($args));
    }
}