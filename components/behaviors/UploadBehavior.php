<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 *
 * Simply attach this behavior to your model
 * Usage example:
 *
 * public
 * function behaviors()
 * {
 *     return [
 *         'ImageUpload' => [
 *             'class' => 'application.components.behaviors.UploadBehavior',
 *             'attribute' => 'fileUpload',
 *             'uploadsDir' => Yii::app()->getBasePath() . '/../uploads/someModel/',
 *         ],
 *     ];
 * }
*/

/**
 * Class UploadBehavior
 */
class UploadBehavior extends CActiveRecordBehavior
{
    /**
     * @var string name of attribute which holds the attachment
     */
    public $attribute = 'upload';

    /**
     * @var string where to store images
     */
    public $uploadsDir;

    /**
     * @var CUploadedFile
     */
    protected $file;
    
    protected $filePath;

    public function afterValidate($event)
    {
        $this->file = CUploadedFile::getInstance($this->owner, $this->attribute);

        if ($this->owner->isNewRecord) {
            if ($this->file instanceof CUploadedFile) {
                $this->owner->{$this->attribute} = $this->file->name;
            } else {
                $this->owner->addError($this->attribute, 'Invalid file');
            }
        }

        parent::afterValidate($event);
    }

    public function beforeSave($event)
    {
        assert($this->owner->dbConnection->currentTransaction instanceof CDbTransaction);
        assert(strlen($this->attribute)>0);
        assert(strlen($this->uploadsDir)>0);

        // if updating file we need to delete the old one
        if (!$this->owner->isNewRecord && $this->file instanceof CUploadedFile) {
            $class = get_class($this->owner);
            $oldModel = $class::model()->findByPk($this->owner->id);
            $oldModel->cleanFiles();
        }

        parent::beforeSave($event);
    }

    public function afterSave($event)
    {
        assert($this->owner->dbConnection->currentTransaction instanceof CDbTransaction);
        assert(strlen($this->attribute)>0);
        assert(strlen($this->uploadsDir)>0);

        // create image dir if not exists
        @mkdir($this->uploadsDir, 777, true);

        if ($this->file instanceof CUploadedFile) {
            $filePath = $this->uploadsDir . $this->getUploadedFileName($this->owner);

            if (!$this->file->saveAs($filePath)) {
                throw new Exception('File saving error');
            }
        }

        parent::afterSave($event);
    }

    public function beforeDelete($event)
    {
        $this->cleanFiles();
        return parent::beforeDelete($event);
    }

    /**
     * @return string
     */
    public function getUploadedFileName()
    {
        return $this->owner->id . '.' . strtolower(pathinfo($this->owner->{$this->attribute}, PATHINFO_EXTENSION));
    }

    public function cleanFiles()
    {
        @unlink($this->uploadsDir . $this->getUploadedFileName());
    }

}