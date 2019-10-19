<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель фотографии.
 */
class Photo extends ActiveRecord
{
    /**
     * @var UploadedFile Загружаемый файл
     */
    public $photo;

    /**
     * @var string Признак обновления файла.
     */
    public $_method;

    /**
     * @return array ID пользователей, с кем расшарено фото
     */
    public function getUsers()
    {
        return Share::find()
            ->select('id')
            ->where(['photo_id' => $this->id])
            ->column();
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['_method', 'compare', 'compareValue' => 'patch', 'skipOnEmpty' => false, 'on' => 'post'],
            ['photo', 'image', 'extensions' => 'jpg, jpeg, png'],
            ['name', 'default', 'value'=> 'Untitled'],
        ];
    }

    /**
     * Загружает файл для валидации.
     */
    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        $this->photo = \yii\web\UploadedFile::getInstanceByName('photo');

        if ($this->isNewRecord && $this->photo === null) {
            $this->addError('photo', 'Photo cannot be empty');
        }

        return true;
    }

    /**
     * Сохраняет файл после успешной валидации.
     */
    public function afterValidate()
    {
        parent::afterValidate();

        if (!$this->hasErrors() && $this->photo !== null) {
            $this->owner_id = \Yii::$app->user->id;

            // удалим старый файл @todo должно быть в afterSave()
            if ($this->path) {
                unlink($this->getRealPath());
            }

            // новое уникальное имя файла относительно папки загрузки
            $this->path = time().\Yii::$app->security->generateRandomString(10).'.'.$this->photo->extension;

            $this->photo->saveAs($this->getRealPath());
        }
    }

    /**
     * @return string Абсолютный путь картинки на сервере
     */
    public function getRealPath()
    {
        return realpath(\Yii::getAlias('@upload')).DIRECTORY_SEPARATOR.$this->path;
    }

    /**
     * @return string Абсолютный URL картинки
     */
    public function getUrl()
    {
        return \Yii::$app->params['imageUrl'] . $this->path;
    }

    /**
     * Удаляет фото из БД и с диска.
     */
    public function delete()
    {
        unlink($this->getRealPath());
        parent::delete();
    }
}
