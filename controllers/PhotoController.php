<?php
namespace app\controllers;

/**
 * Экшены управления фотографиями.
 */
class PhotoController extends BaseController
{
    /**
     * @var string Класс основной модели контроллера
     */
    public $modelClass = 'app\models\Photo';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'authenticator' => [
                'class' => \app\filters\BearerAuth::className(),
            ],
        ]);
    }

    /**
     * Загружает фото.
     *
     * @return array Данные о фото
     */
    public function actionCreate()
    {
        $model = new $this->modelClass;
        
        return $this->saveModel($model, ['id', 'name', 'url'], 201);
    }

    /**
     * Изменяет фото.
     *
     * @return array Данные о фото или ошибки валидации
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id, true);

        return $this->saveModel($model, ['id', 'name', 'url']);
    }

    /**
     * Запрашивает поле _method при post-запросе на обновление записи.
     * 
     * @return array Ошибка валидации
     */
    public function actionPatch($id)
    {
        \Yii::$app->response->setStatusCode(422);

        return ['_method' => 'Method must be equal to "patch".'];
    }

    /**
     * Удаляет фото.
     * 
     * @param string $id ID фото
     * @return bool
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id, true);
        $model->delete();

        return true;
    }

    /**
     * Получает одно фото.
     * 
     * @param string $id
     * @return array Данные о фото
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $data = $model->getAttributes(['id', 'name', 'url', 'owner_id', 'users']);

        return $data;
    }

    /**
     * Получает все фото.
     * 
     * @return array Данные о фото
     */
    public function actionIndex()
    {
        $models = $this->modelClass::find()->all();
        $data = [];
        foreach ($models as $model) {
            $data[] = $model->getAttributes(['id', 'name', 'url', 'owner_id', 'users']);
        }
       
        return $data;
    }
}
