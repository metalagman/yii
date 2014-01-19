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
 *             'class' => 'ext.behaviors.ImageUploadBehavior',
 *             'attribute' => 'imageFile',
 *             'imagesDir' => Yii::app()->getBasePath() . '/../images/someModel/',
 *         ],
 *     ];
 * }
*/

class ImageUploadBehavior extends CActiveRecordBehavior
{
    /**
     * @var string name of attribute which holds the attachment
     */
    public $attribute = 'image';

    /** @var array Size of thumb, Width, Height */
    public $thumbSize = [ 400, 300 ];

    /**
     * @var string where to store images
     */
    public $imagesDir;

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
                $this->owner->addError($this->attribute, 'Invalid image');
            }
        }

        parent::afterValidate($event);
    }

    public function beforeSave($event)
    {
        assert($this->owner->dbConnection->currentTransaction instanceof CDbTransaction);
        assert(strlen($this->attribute)>0);
        assert(strlen($this->imagesDir)>0);

        // if updating photo we need to delete the old one
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
        assert(strlen($this->imagesDir)>0);

        // create image dir if not exists
        @mkdir($this->imagesDir, 777, true);

        if ($this->file instanceof CUploadedFile) {
            $filePath = $this->imagesDir . $this->getImageFileName($this->owner);

            if ($this->file->saveAs($filePath)) {
                /** @var PhpThumb $thumb */
                $thumb = Yii::app()->phpThumb->create($filePath);
                $thumb->adaptiveResize($this->thumbSize[0] , $this->thumbSize[1]);
                $thumb->save($this->imagesDir . $this->getThumbFileName($this->owner));
            } else {
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
    public function getImageFileName()
    {
        return $this->owner->id . '.' . strtolower(pathinfo($this->owner->{$this->attribute}, PATHINFO_EXTENSION));
    }

    /**
     * @return string
     */
    public function getThumbFileName()
    {
        return $this->owner->id . '_thumb.' . strtolower(pathinfo($this->owner->{$this->attribute}, PATHINFO_EXTENSION));
    }

    public function cleanFiles()
    {
        @unlink($this->imagesDir . $this->getImageFileName());
        @unlink($this->imagesDir . $this->getThumbFileName());
    }

}