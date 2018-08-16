<?php
/**
 * Created by PhpStorm.
 * User: fbarinov
 * Date: 07.08.2018
 * Time: 16:37
 */

namespace app\controllers;


use app\models\MessagesModel;
use app\models\User;
use georgique\yii2\jsonrpc\JsonRpcException;

class ChatController extends \yii\web\Controller
{
    protected $body_params;
    /** @var User */
    protected $_user;

    public function beforeAction($action)
    {
        // ...set `$this->enableCsrfValidation` here based on some conditions...
        // call parent method that will check CSRF if such property is true.
        $this->enableCsrfValidation = false;
        $this->body_params = \Yii::$app->request->getBodyParams();
        return parent::beforeAction($action);
    }

    /**
     * @param $name
     * @param null $default
     * @return null|mixed
     */
    public function getBodyParam($name, $default = null)
    {
        if (isset($this->body_params[$name])) {
            return $this->body_params[$name];
        }
        return $default;
    }
    // Note that URL patterns won't be used to resolve the method - this would not be resourse-wise.
    // Method string should simply be [[module.]controller.]action where module and controller parts
    // can be omitted, so default module and index controller will be used.
    public function actionTest()
    {

        $greetings = [
            'Hey', 'Hey man', 'Hi',
            'How’s it going?', 'How are you doing?',
            'What’s up?', 'What’s new?', 'What’s going on?',
            'How’s everything?', 'How are things?', 'How’s life?',
            'How’s your day?', 'How’s your day going?',
            'Good to see you', 'Nice to see you',
            'Long time no see', 'It’s been a while',
            'It’s nice to meet you', 'Pleased to meet you',
            'How have you been?',
            'How do you do!',
            'Yo!',
            'Are you OK?', 'You alright?', 'Alright mate?',
            'Howdy!',
            'Sup?', 'Whazzup?',
            'G’day mate!',
            'Hiya!',
        ];
        return [
            "success" => 1,
            "response" => $greetings[array_rand($greetings)],
            "date" => date('Y.m.d H:i:s'),
            "params" => $this->body_params,
        ];
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws JsonRpcException
     */
    public function actionRegister()
    {
        if (User::findOne(['nickname' => $this->getBodyParam('nickname')])) {
            throw new JsonRpcException(null, 'member already exists', 1001, null);
        }
        $this->_user = new User();
        $this->_user->setAttributes($this->body_params);
        if ($this->_user->validate() && $this->_user->save()) {
            return $this->actionLogin();
        } else {
            return [
                'error' => [
                    'code' => -32100,
                    'message' => 'Cannot save user',
                    'data' => $this->_user->getErrors(),
                ]
            ];
        }
    }

    public function actionLogin()
    {
        if (empty($this->_user)) {
            $this->_user = User::findOne(['nickname' => $this->getBodyParam('nickname')]);
        }
        $this->_user->validatePassword($this->getBodyParam('password'));
        $salt = time();
        $auth_key = md5($this->_user->nickname.$salt);
        $access_token = sha1($this->_user->password_hash . $salt);
        $this->_user->setAttribute('auth_key', $auth_key);
        $this->_user->setAttribute('access_token_hash', sha1($access_token.$salt).$salt);
        if ($this->_user->validate() && $this->_user->save()) {
            return [
                "auth_sid" => $auth_key,
                "auth_token" => $access_token
            ];
        } else {
            return [
                'error' => [
                    'code' => -32100,
                    'message' => 'Cannot save user',
                    'data' => $this->_user->getErrors(),
                ]
            ];
        }
    }


    /**
     * @return array
     * @throws JsonRpcException
     */
    public function actionSend_msg()
    {
        $this->_user = User::checkAuth($this->getBodyParam('auth_sid'),$this->getBodyParam('auth_token'));
        $message = new MessagesModel();
        $message->setAttributes($this->body_params);
        $message->setAttribute('user_id',$this->_user->id);
        $toUser = User::findOne(['nickname'=>$this->getBodyParam('to')]);
        if(is_null($toUser)){
            throw new JsonRpcException(null, 'destination user not found', 1004);
        }
        $message->setAttribute('to', $toUser->id);
        if(!$message->save()){
            return [
                'errors'=>$message->getErrors()
            ];
        }
        return [
            "success"=>1,
            "id"=>$message->id,
        ];
    }


    public function actionGet_msg()
    {
        $this->_user = User::checkAuth($this->getBodyParam('auth_sid'),$this->getBodyParam('auth_token'));
        return MessagesModel::search($this->body_params);
//        $messages = MessagesModel::find()->where
    }

    public function actionError()
    {
        return [
            'error' => [
                'class' => '\georgique\yii2\jsonrpc\ErrorHandler'
            ]
        ];
    }
}