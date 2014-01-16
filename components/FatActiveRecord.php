<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 *
 * Class FatActiveRecord
 *
 * @property boolean isPosted
 */
abstract class FatActiveRecord extends CActiveRecord
{

    /**
     * @param $id
     * @return CActiveRecord
     * @throws CHttpException
     *
     */
    public static function loadModel($id)
    {
        $class = get_called_class();
        $model = $class::model()->findByPk((int)$id);
        if ($model === null)
            throw new CHttpException(404, Yii::t('site', 'The requested page does not exist.'));
        return $model;
    }


    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function trySave($runValidation = true, $attributes = NULL)
    {
        $r = $this->save($runValidation, $attributes);
        if (!$r) {
            $message = 'Object saving error. save() returned ' . CVarDumper::dumpAsString($r) .
                ' Last DB error: ' . CVarDumper::dumpAsString($this->getDbConnection()->pdoInstance->errorInfo());

            if (count($errors = $this->getErrors()))
                $message = 'Object saving error: ' . CVarDumper::dumpAsString($errors);

            throw new CHttpException(500, $message);
        }
    }

    public function checkTransaction()
    {
        if ($this->getDbConnection()->getCurrentTransaction() == null)
            throw new Exception('Active transaction is required');
    }

    public function performAjaxValidation($form = null)
    {
        if (Yii::app()->request->isAjaxRequest) {
            if ($form != null && $_POST['ajax'] != $form)
                return false;
            echo CActiveForm::validate($this);
            Yii::app()->end();
        }
        return false;
    }

    public function getIsPosted()
    {
        $class = get_class($this);
        $r = isset($_POST[$class]);
        if ($r)
            Yii::trace("$class model posted");
        return $r;
    }

    public function loadPostData()
    {
        $class = get_class($this);
        if (isset($_POST[$class]))
            $this->setAttributes($_POST[$class]);
    }

    public function safeSetAttributes($attributes)
    {
        $this->setAttributes(array_intersect_key($attributes, array_flip($this->safeAttributeNames)));
    }

    public static function getPrefixConstants($prefix, $returnList = false)
    {
        $class = get_called_class();
        $reflection = new ReflectionClass(get_called_class());

        $result = [];

        foreach ($reflection->getConstants() as $name => $value) {
            if (preg_match("/^{$prefix}/i", $name)) {
                $result[$name] = $value;
            }
        }

        if ($returnList == false) {
            return $result;
        }

        $list = [];

        foreach ($result as $key => $value) {
            $list[$value] = Yii::t('app', get_called_class() . '_' . $prefix . '_' . $value);
        }

        return $list;
    }

    public static function listData($textField = 'name', $valueField = 'id')
    {
        return CHtml::listData(static::model()->findAll(['select' => [$valueField, $textField]]), $valueField, $textField);
    }
}
