<?php
/**
 * Class ImageUploadBehavior
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
 * [[profile]] - thumbnail profile name
 *
 * public
 * function behaviors()
 * {
 *     return [
 *         'ImageUpload' => [
 *              'class' => 'application.components.behaviors.ImageUploadBehavior',
 *              'attribute' => 'image',
 *              'thumbs' => [
 *                  'thumb' => ['width' => 400, 'height' => 300],
 *              ],
 *              'filePath' => '[[webroot]]/images/[[model]]/[[id]].[[extension]]',
 *              'fileUrl' => '/images/[[model]]/[[id]].[[extension]]',
 *              'thumbPath' => '[[webroot]]/images/[[model]]/[[profile]]_[[id]].[[extension]]',
 *              'thumbUrl' => '/images/[[model]]/[[profile]]_[[id]].[[extension]]',
 *         ],
 *     ];
 * }
*/

class ImageUploadBehavior extends FileUploadBehavior
{
    public $attribute = 'image';

    /**
     * @var array Thumbnail profiles, array of [width, height]
     */
    public $thumbs = [
        'thumb' => ['width'=> 200, 'height' => 150],
    ];

    /**
     * @var string Path template for thumbnails. Please use the [[profile]] placeholder.
     */
    public $thumbPath = '[[webroot]]/images/[[profile]]_[[id]].[[extension]]';

    /**
     * @var string Url template for thumbnails
     */
    public $thumbUrl = '/images/[[profile]]_[[id]].[[extension]]';

    public $filePath = '[[webroot]]/images/[[id]].[[extension]]';
    public $fileUrl = '/images/[[id]].[[extension]]';

    /**
     * @param string $path
     * @param CActiveRecord $model
     * @param string $attribute
     * @param string $profile
     * @return string
     */
    public static function resolveProfilePath($path, CActiveRecord $model, $attribute, $profile)
    {
        $path = static::resolvePath($path, $model, $attribute);
        $path = str_replace('[[profile]]', $profile, $path);
        return $path;
    }

    public function cleanFiles()
    {
        parent::cleanFiles();
        foreach(array_keys($this->thumbs) as $profile) {
            @unlink($this->getThumbFilePath($this->attribute, $profile));
        }
    }

    protected function afterFileSave($path)
    {
        foreach ($this->thumbs as $profile=>$config) {
            /** @var PhpThumb $thumb */
            $thumb = Yii::app()->phpThumb->create($path);
            $thumb->adaptiveResize($config['width'] , $config['height']);
            $thumb->save(static::getThumbFilePath($this->attribute, $profile));
        }
    }

    /**
     * @param string $attribute
     * @param string $profile
     * @return string
     */
    public function getThumbFilePath($attribute, $profile = 'thumb')
    {
        $path = $this->getPathTemplate('thumbPath', $attribute);
        return static::resolveProfilePath($path, $this->owner, $attribute, $profile);
    }

    /**
     * @param string $attribute
     * @param string $profile
     * @return string
     */
    public function getThumbFileUrl($attribute, $profile = 'thumb')
    {
        $path = $this->getPathTemplate('thumbUrl', $attribute);
        return static::resolveProfilePath($path, $this->owner, $attribute, $profile);
    }

}