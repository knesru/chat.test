<?php
/**
 * Created by PhpStorm.
 * User: fbarinov
 * Date: 14.08.2018
 * Time: 16:35
 */

namespace app\models;

/**
 * Class MessagesModel
 * @package app\models
 *
 * @property int id
 * @property string message
 * @property string from
 * @property string to
 * @property int unixtime
 *
 * @property User userFrom
 * @property User userTo
 */
class MessagesModel extends ChatModel
{
    public static function tableName()
    {
        return '{{%messages}}';
    }

    public function rules()
    {
        return [
            [['message', 'user_id'], 'required'],
            [['user_id'], 'integer'],
            [['to'], 'safe']
        ];
    }

    public function getUserTo()
    {
        return $this->hasOne(User::class, ['id' => 'to']);
    }

    public function getUserFrom()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public static function search($filter)
    {
        $messagesFilter = new self();
        $messagesFilter->setAttributes($filter);
        $messages = self::find()
            ->filterWhere($messagesFilter->attributes);
        if (!empty($filter['date_from'])) {
            $messages->andWhere(['>=', 'created_at', $filter['date_from']]);
        }
        $total = $messages->count();
        if (!empty($filter['limit'])) {
            $messages->limit($filter['limit']);
        }
        if (!empty($filter['offset'])) {
            $messages->offset($filter['offset']);
        }

        $result = [];
        //$sql = $messages->prepare(\Yii::$app->db->queryBuilder)->createCommand()->rawSql;
        /** @var MessagesModel[] $messagesResult */
        $messagesResult = $messages->all();
        foreach ($messagesResult as $message) {
            $msgAnswer = [];
            /*
             * "from": "mike",
          "to":"all",
          "date":"2018-08-01 23:59:59",
          "unixtime":"1533153599",
          "body":"Привет!"
             * */
            $msgAnswer['id']=$message->id;
            $msgAnswer['from']=$message->userFrom->nickname;
            $msgAnswer['to']=$message->userTo->nickname;
            $msgAnswer['date']=date('Y-m-d H:i:s',$message->created_at);
            $msgAnswer['unixtime']=$message->created_at;
            $msgAnswer['body']=$message->message;
            $result[] = $msgAnswer;
        }

        return [
            'total' => $total,
            'count' => count($result),
            'messages' => $result,
        ];
    }
}