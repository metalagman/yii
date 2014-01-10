<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

class UpdateAction extends CAction
{
    public $modelClass, $successMessage, $errorMessage;

    public function run($id)
    {
        $class = $this->modelClass;
        $model = $class::loadModel($id);
        $model->scenario = 'update';

        $model->performAjaxValidation();

        if ($model->isPosted) {
            $model->loadPostData();

            if ($model->validate()) {
                $model->trySave();
                Yii::app()->user->setFlash('success', $this->successMessage ? : 'Success!');
                $this->controller->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::app()->user->setFlash('error', $this->errorMessage ? : 'Error!');
            }
        }

        $this->controller->render('update', ['model' => $model]);
    }
}