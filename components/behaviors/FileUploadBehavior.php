<?php
/**
 * Class FileUploadBehavior
 *
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 *
 * Simply attach this behavior to your model, specify attribute and file path.
 * You can use placeholders in path configuration:
 *
 * [[approot]] - application root
 * [[webroot]] - web root
 * [[model]] - model name
 * [[id]] - model id
 * [[basename]] - original filename with extension
 * [[filename]] - original filename without extension
 * [[extension]] - original extension
 * [[baseurl]] - site base url
 *
 * Usage example:
 *
 * public
 * function behaviors()
 * {
 *     return [
 *         'FileUpload' => [
 *             'class' => 'application.components.behaviors.FileUploadBehavior',
 *             'attribute' => 'fileUpload',
 *             'filePath' => '[[webroot]]/uploads/[[id]].[[extension]]',
 *             'fileUrl' => '/uploads/[[id]].[[extension]]',
 *         ],
 *     ];
 * }
*/

class FileUploadBehavior extends CActiveRecordBehavior
{
    public static $instances = [];

    /**
     * @var string Name of attribute which holds the attachment
     */
    public $attribute = 'upload';

    /**
     * @var string Path template to use in storing files
     */
    public $filePath = '[[webroot]]/uploads/[[id]].[[extension]]';

    /**
     * @var string where to store images
     */
    public $fileUrl = '/uploads/[[id]].[[extension]]';

    /**
     * @var CUploadedFile
     */
    protected $file;

    /**
     * Replaces all placeholders in path variable with corresponding values
     *
     * @param string $path
     * @param CActiveRecord $model
     * @param string $attribute
     * @return string
     */
    public static function resolvePath($path, CActiveRecord $model, $attribute)
    {
        $path = str_replace('[[baseurl]]', Yii::app()->getBaseUrl(true), $path);
        $path = str_replace('[[approot]]', Yii::getPathOfAlias('application'), $path);
        $path = str_replace('[[webroot]]', Yii::getPathOfAlias('webroot'), $path);
        $path = str_replace('[[model]]', lcfirst(get_class($model)), $path);
        $path = str_replace('[[attribute]]', lcfirst($attribute), $path);
        $path = str_replace('[[id]]', $model->id, $path);
        $pi = pathinfo($model->{$attribute});
        $path = str_replace('[[extension]]', strtolower($pi['extension']), $path);
        $path = str_replace('[[filename]]', $pi['filename'], $path);
        $path = str_replace('[[basename]]', $pi['filename'] . '.' . strtolower($pi['extension']), $path);
        return $path;
    }

    public function afterConstruct($event)
    {
        static::$instances[get_class($this->owner)][$this->attribute] = $this;
    }

    public function afterFind($event)
    {
        static::$instances[get_class($this->owner)][$this->attribute] = $this;
    }

    public function afterValidate($event)
    {
        $this->file = CUploadedFile::getInstance($this->owner, $this->attribute);

        // if file was uploaded, we store it's name
        if ($this->file instanceof CUploadedFile) {
            $this->owner->{$this->attribute} = $this->file->name;
        }

        parent::afterValidate($event);
    }

    public function beforeSave($event)
    {
        assert($this->owner->dbConnection->currentTransaction instanceof CDbTransaction);
        assert(strlen($this->attribute)>0);
        assert(strlen($this->filePath)>0);

        // if updating file we need to delete the old one
        if (!$this->owner->isNewRecord && $this->file instanceof CUploadedFile) {
            $class = get_class($this->owner);
            /** @var CActiveRecord $oldModel */
            $oldModel = $class::model()->findByPk($this->owner->id);
            $oldModel->cleanFiles();
        }

        parent::beforeSave($event);
    }

    public function afterSave($event)
    {
        if ($this->file instanceof CUploadedFile) {
            $path = $this->getUploadedFilePath($this->attribute);
            @mkdir(pathinfo($path, PATHINFO_DIRNAME), 777, true);
            if (!$this->file->saveAs($path)) {
                throw new Exception('File saving error');
            }
            $this->afterFileSave($path);
        }
        parent::afterSave($event);
    }

    public function beforeDelete($event)
    {
        $this->cleanFiles();
        return parent::beforeDelete($event);
    }

    /**
     * Removes files associated with attribute
     */
    protected function cleanFiles()
    {
        $path = $this->getPathTemplate('filePath', $this->attribute);
        @unlink($path);
    }

    /**
     * Returns path template by its name
     *
     * @param string $pathVar
     * @param string $attribute
     * @return string
     * @throws CException
     */
    protected function getPathTemplate($pathVar, $attribute)
    {
        if (empty(static::$instances[get_class($this->owner)][$attribute]))
            throw new CException('Missing behavior for attribute '.CVarDumper::dumpAsString($attribute));
        return static::$instances[get_class($this->owner)][$attribute]->{$pathVar};
    }

    public function getUploadedFilePath($attribute)
    {
        $path = $this->getPathTemplate('filePath', $attribute);
        return static::resolvePath($path, $this->owner, $attribute);
    }

    public function getUploadedFileUrl($attribute)
    {
        $path = $this->getPathTemplate('fileUrl', $attribute);
        return static::resolvePath($path, $this->owner, $attribute);
    }

    /**
     * Triggered after successfull file saving. Override to create additional functionality.
     * @param string $path
     */
    protected function afterFileSave($path)
    {

    }

}
