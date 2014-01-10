<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com> 
 */

class CreateAction extends CAction
{
    public
        $modelClass,
        $successMessage = false,
        $errorMessage = false,
        $redirectAction = 'view',
        $redirectAppendId = true,
        $useTransaction = true,
        $viewFile = 'create';

    public function run()
    {
        $class = $this->modelClass;
        /** @var CActiveRecord $model */
        $model = new $class('create');
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