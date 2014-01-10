<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

class IndexAction extends CAction
{
    public $modelClass;

    public function run()
    {
        $class = $this->modelClass;
        $model = new $class('search');
        $model->unsetAttributes();

        if ($params = Yii::app()->request->getParam($this->modelClass))
            $model->attributes = $params;

        $this->controller->render('index', array(
            'model' => $model,
        ));
    }
}