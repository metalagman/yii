<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com> 
 */

class DeleteAction extends CAction
{
    public $modelClass, $successMessage, $errorMessage;

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
                Yii::app()->user->setFlash('success', $this->successMessage ?: 'Success!');
            } else {
                Yii::app()->user->setFlash('error', $this->errorMessage ?: 'Error!');
            }
            $this->controller->redirect(['index']);
        }
    }
}