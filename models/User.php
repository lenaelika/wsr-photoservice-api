<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Модель пользователя.
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * @var string Оригинальный пороль пользователя
     */
    public $password;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['first_name', 'surname', 'phone', 'password'], 'required'],
            ['phone', 'string', 'min' => 11, 'max' => 11],
            ['phone', 'unique'],
        ];
    }

    /**
     * Генерит хеш пароля и токен доступа для сохранения в БД.
     */
    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->passhash = Yii::$app->security->generatePasswordHash($this->password);
        }

        return parent::beforeSave($insert);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['token' => $token]);
    }

    /**
     * Находит пользователя по телефону.
     *
     * @param string $phone
     * @return static|null
     */
    public static function findByPhone($phone)
    {
        return static::findOne(['phone' => $phone]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->passhash);
    }

    /**
     * Изменяет токен доступа пользователя в БД.
     * 
     * @param bool $gen Сгенерировать новый токен, иначе удалить
     */
    public function renewToken($gen = true)
    {
        $this->token = $gen ? Yii::$app->security->generateRandomString() : '';
        $this->save(false, ['token']);
    }
}
