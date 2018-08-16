<?php
/**
 * Created by PhpStorm.
 * User: fbarinov
 * Date: 07.08.2018
 * Time: 16:26
 */

namespace app\controllers;
use \georgique\yii2\jsonrpc\Controller;
use const georgique\yii2\jsonrpc\JSON_RPC_PARAMS_PASS_BODY;

class WebApiController  extends Controller{
    public $paramsPassMethod = JSON_RPC_PARAMS_PASS_BODY;
    public function beforeAction($action)
    {
        // ...set `$this->enableCsrfValidation` here based on some conditions...
        // call parent method that will check CSRF if such property is true.
        $this->enableCsrfValidation = false;
        \Yii::$app->response->headers->set('Access-Control-Allow-Origin', '*');
        return parent::beforeAction($action);
    }
}