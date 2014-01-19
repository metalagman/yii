<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 * @link http://vk.com/dev/openapi
 */

class VkOpenApi extends CWidget
{
    public
        $apiId;

    public function init()
    {
        assert(isset($this->apiId));

        /** @var CClientScript */
        $cs = Yii::app()->getClientScript();

        $cs->registerScriptFile('//vk.com/js/api/openapi.js', CClientScript::POS_HEAD, [
            'charset' => 'windows-1251',
        ]);

        $cs->registerScript('InitOpenApi', "
            VK.init({
                apiId: {$this->apiId}
            });
        ", CClientScript::POS_HEAD);
    }
}