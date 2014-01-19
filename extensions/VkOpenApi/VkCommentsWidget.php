<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 * @link http://vk.com/dev/widget_comments
 */

Yii::import('ext.VkOpenApi.VkOpenApi');

/**
 * Class VkCommentsWidget
 *
 * @mixin VkOpenApi
 */
class VkCommentsWidget extends VkOpenApi
{
    public
        $options = [],
        $htmlOptions = [];

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $id = $this->getId();
        $this->htmlOptions['id'] = $id;
        echo CHtml::tag('div', $this->htmlOptions);

        $elementId = CJavaScript::encode($id);
        $options = CJavaScript::encode($this->options);
        Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $id, "VK.Widgets.Comments({$elementId},{$options});");
    }
}