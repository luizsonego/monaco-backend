<?php

namespace app\modules\v1\controllers;

use app\helpers\TokenAuthenticationHelper;
use app\models\Status;
use app\models\Transactions;
use app\models\Wallet;
use Yii;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;

class MovementsController extends ActiveController
{
  public $modelClass = 'app\models\Profile';

  public function actions()
  {
    $actions = parent::actions();
    unset(
      $actions['create'],
      $actions['update'],
      $actions['view'],
      $actions['index']
    );

    return $actions;
  }

  public function behaviors()
  {
    return [
      'corsFilter' => [
        'class' => Cors::className(),
        'cors' => [
          'Origin' => ['*'],
          'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
          'Access-Control-Request-Headers' => ['*'],
        ],
      ],
      'verbs' => [
        'class' => VerbFilter::className(),
        'actions' => [
          'create' => ['POST', 'PUT', 'GET'],
          'update' => ['POST', 'PUT', 'PATCH', 'GET'],
          'view' => ['GET'],
          'index' => ['GET'],
        ],
      ]
    ];
  }

  public function actionListMovements()
  {
    try {
      $user = TokenAuthenticationHelper::token();
  
      $transactions = Transactions::find()->where(['user_id' => $user['id']])->all();

      $response['status'] = Status::STATUS_SUCCESS;
      $response['message'] = 'Success';
      $response['data'] = $transactions;
      
    } catch (\Throwable $th) {
      Yii::$app->response->statusCode = Status::STATUS_INTERNAL_SERVER_ERROR;
      $response['status'] = Status::STATUS_ERROR;
      $response['message'] = 'Error';
      $response['data'] = $th->getMessage();
    }

    return $response;
  }

  public function actionWallet()
  {
    try {
      $user = TokenAuthenticationHelper::token();

      $transactions = Wallet::find()->where(['user_id' => $user['id']])->one();

      $response['status'] = Status::STATUS_SUCCESS;
      $response['message'] = 'Success';
      $response['data'] = $transactions;
    } catch (\Throwable $th) {
      Yii::$app->response->statusCode = Status::STATUS_INTERNAL_SERVER_ERROR;
      $response['status'] = Status::STATUS_ERROR;
      $response['message'] = 'Error';
      $response['data'] = $th->getMessage();
    }

    return $response;
  }


  public function actionListAllMovements()
  {
    $user = TokenAuthenticationHelper::token();
    if ($user->access_given !== 99) {
      return [
        'status' => Status::STATUS_UNAUTHORIZED,
        'message' => 'You are not authorized to access this page.',
        'data' => []
      ];
    }

    $transactions = Transactions::find()
      ->all();

    return [
      'status' => Status::STATUS_SUCCESS,
      'message' => 'Success!',
      'data' => $transactions
    ];

  }

}