<?php
/**
 * Created by PhpStorm.
 * User: fbarinov
 * Date: 10.08.2018
 * Time: 14:00
 */

namespace app\models;


use yii\db\ActiveRecord;

/**
 * Class ChatModel
 * @package app\models
 *
 * @property int created_at
 * @property int updated_at
 */

abstract class ChatModel extends ActiveRecord
{
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if(empty($this->getAttribute('created_at'))){
                $this->setAttribute('created_at',time());
            }
            $this->setAttribute('updated_at',time());
            return true;
        } else {
            return false;
        }
    }

    public static function findOneOrCreate($condition)
    {
        $model = parent::findOne($condition);
        if(is_null($model)){
            return new static();
        }
        return $model;
    }
}