<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

class UpdateAction extends CAction
{
    public
        $modelClass,
        $successMessage = false,
        $errorMessage = false,
        $redirectAction = 'view',
        $redirectAppendId = true,
        $useTransaction = true,
        $viewFile = 'update';

    public function run($id)
    {
        $class = $this->modelClass;
        $model = $class::loadModel($id);
        $model->scenario = 'update';

        $model->performAjaxValidation();

        if ($model->isPosted) {
            $model->loadPostData();

            if ($model->validate()) {
                if ($this->useTransaction) {
                    $transaction = $model->getDbConnection()->beginTransaction();
                    try {
                        $model->trySave();
                        $transaction->commit();
                    } catch (Exception $e) {
                        $transaction->rollback();
                        if ($this->errorMessage != false)
                            Yii::app()->user->setFlash('error', $this->errorMessage);
                        throw $e;
                    }
                } else {
                    $model->trySave();
                }

                if ($this->successMessage != false)
                    Yii::app()->user->setFlash('success', $this->successMessage);

                $redirect = [$this->redirectAction];
                if ($this->redirectAppendId)
                    $redirect['id'] = $model->id;
                $this->controller->redirect($redirect);
            }
        }

        $this->controller->render($this->viewFile, ['model' => $model]);
    }
}