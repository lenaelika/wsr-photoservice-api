<?php
namespace app\controllers;

/**
 * Экшены управления пользователями.
 */
class UserController extends BaseController
{
    /**
     * @var string Класс основной модели контроллера
     */
    public $modelClass = 'app\models\User';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'authenticator' => [
                'class' => \app\filters\BearerAuth::className(),
                'except' => ['signup', 'login'],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['signup', 'login'],
                        'allow' => true,
                    ], [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Создает пользователя.
     *
     * @return array ID созданного пользователя или ошибки валидации
     * @throws \yii\web\ServerErrorHttpException при ошибке сохранения
     */
    public function actionSignup()
    {
        $model = new $this->modelClass;

        return $this->saveModel($model, ['id'], 201);
    }

    /**
     * Аутентифицирует пользователя.
     *
     * @return array Токен доступа или ошибки валидации
     */
    public function actionLogin()
    {
        $model = new \app\models\LoginForm();
        $model->load(\Yii::$app->request->post(), '');

        if ($model->validate()) {
            $user = $model->getUser();
            $user->renewToken();

            return ['token' => $user->token];
        }

        $errors = $model->getFirstErrors();
        $code = isset($errors['login']) ? 404 : 422;
        \Yii::$app->response->setStatusCode($code);

        return $errors;
    }

    /**
     * Разлогинивает пользователя.
     *
     * @return bool
     */
    public function actionLogout()
    {
        $user = \Yii::$app->user->identity;
        $user->renewToken(false);

        return true;
    }

    /**
     * Поиск пользователей.
     * 
     * @param string $search Поисковая строка: имя, фамилия и/или телефон
     */
    public function actionSearch($search)
    {
        $search = trim($search);
        if (!$search) {
            throw new \yii\web\BadRequestHttpException("search string cannot be blank");
        }

        $words = preg_split('/\s+/', $search);

        $query = $this->modelClass::find()
            ->select(['id', 'first_name', 'surname', 'phone'])
            ->where(['like', 'first_name', $words[0]]);

        switch (count($words)) {
            case 1:
                // поиск по всем полям
                $query
                    ->orWhere(['like', 'surname', $words[0]])
                    ->orWhere(['like', 'phone', $words[0]]);
                break;
            case 3:
                // составной поиск
                $query->andWhere(['like', 'phone', $words[2]]);
            case 2:
                $query->andWhere(['like', 'surname', $words[1]]);
                break;
        }

        return $query
            ->andWhere(['!=', 'id', \Yii::$app->user->id])
            ->asArray()->all();
    }

    /**
     * Расшаривает набор фото с пользователем под указанным id.
     * 
     * @param int $id ID пользователя
     * @return array Ids фото, которые уже были ранее расшарены
     */
    public function actionShare($id)
    {
        $model = $this->findModel($id);
        $ids = \Yii::$app->request->post('photos');

        if (empty($ids) || !is_array($ids)) {
            \Yii::$app->response->setStatusCode(422);
            return ['message' => 'photos must be an array of ids.'];
        }

        // @todo

        return ['existing_photos' => $ids];
    }
}
