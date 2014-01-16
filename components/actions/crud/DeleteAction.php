<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com> 
 */

class DeleteAction extends CAction
{
    public
        $modelClass,
        $successMessage = false,
        $errorMessage = false,
        $redirectAction = 'index';

    public function run($id)
    {
        $class = $this->modelClass;
        $model = $class::loadModel($id);
        $success = false;

        $transaction = $model->dbConnection->beginTransaction();
        try {
            $success = $model->delete();
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
            if (defined(YII_DEBUG))
                throw $e;
        }

        if (!Yii::app()->request->isAjaxRequest) {
            if ($success) {
                if ($this->successMessage != false)
                    Yii::app()->user->setFlash('success', $this->successMessage);
            } else {
                if ($this->errorMessage != false)
                    Yii::app()->user->setFlash('error', $this->errorMessage);
            }
            $this->controller->redirect([$this->redirectAction]);
        }
    }
}