<?php
/**
 * Created by PhpStorm.
 * User: lagman
 * Date: 14.11.13
 * Time: 10:49
 */

/**
 * Class JsonDataBehavior
 *
 * Encodes and decodes specified AR attribute with json
 */
class JsonDataBehavior extends CActiveRecordBehavior
{
    /**
     * @var string $attribute Attribute to work with
     */
    public $attribute;

    public function afterFind($event)
    {
        $this->owner->{$this->attribute} = CJSON::decode($this->owner->{$this->attribute});
    }

    public function beforeSave($event)
    {
        $this->owner->{$this->attribute} = CJSON::encode($this->owner->{$this->attribute});
    }
}