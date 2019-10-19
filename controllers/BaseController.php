<?php
namespace app\controllers;

/**
 * Базовый контроллер.
 */
class BaseController extends \yii\rest\Controller
{
    /**
     * Добавляет заголовок для кросс-доменного доступа.
     */
    public function beforeAction($action)
    {
        header('Access-Control-Allow-Origin: *');
        return parent::beforeAction($action);
    }

    /**
     * Находит модель по ID.
     * 
     * @param string $id
     * @param bool $checkAccess Сравнить владельца модели с текущим
     * @return \yii\db\ActiveRecord
     */
    public function findModel($id, $checkAccess = false)
    {
        $model = $this->modelClass::findOne($id);
        if ($model === null) {
            throw new \yii\web\NotFoundHttpException();
        }

        if ($checkAccess && $model->owner_id !== \Yii::$app->user->id) {
            throw new \yii\web\ForbiddenHttpException();
        }

        return $model;
    }

    /**
     * Сохраняет модель.
     * 
     * @param \yii\db\ActiveRecord $model
     * @param array $attrs Названия возращаемых атрибутов
     * @param int $statusCode Код HTTP-ответа, по умолчанию 200
     * @throws \yii\web\ServerErrorHttpException при ошибке сохранения
     */
    protected function saveModel($model, $attrs, $statusCode = 200)
    {
        $model->load(\Yii::$app->request->post(), '');

        if ($model->save()) {
            \Yii::$app->response->setStatusCode($statusCode);
            return $model->getAttributes($attrs);
        }

        if ($model->hasErrors()) {
            \Yii::$app->response->setStatusCode(422);
            return $model->getFirstErrors();
        }

        throw new \yii\web\ServerErrorHttpException();
    }
}
