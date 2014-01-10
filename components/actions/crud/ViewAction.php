<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

class ViewAction extends CAction
{
    public $modelClass;

    public function run($id)
    {
        $class = $this->modelClass;
        $model = $class::loadModel($id);
        $this->controller->render('view', ['model' => $model]);
    }
}