<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

class EmbeededVideoWidget extends CWidget
{
    public
        $model,
        $attribute,

        $width = 640,
        $height = 480,

        $htmlOptions = [];

    public function init()
    {
        assert($this->model instanceof CActiveRecord);
    }

    public function run()
    {
        $value = $this->model->{$this->attribute};

        $patterns = [
            '/youtube\.com\/watch\?v=([^\&\?\/]+)/',
            '/youtube\.com\/embed\/([^\&\?\/]+)/',
            '/youtube\.com\/v\/([^\&\?\/]+)/',
            '/youtu\.be\/([^\&\?\/]+)/',
        ];

        $youtubeId = null;

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value, $id)) {
                $youtubeId = $id[1];
            }
        }

        if (isset($youtubeId)) {
            $options = CMap::mergeArray([
                'type' => 'text/html',
                'width' => $this->width,
                'height' => $this->height,
                'src' => "http://www.youtube.com/embed/{$youtubeId}",
                'frameborder' => 0,
            ], $this->htmlOptions);
           echo CHtml::tag('iframe', $options);
        }
    }
}