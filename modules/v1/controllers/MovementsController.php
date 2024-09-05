<?php

namespace app\modules\v1\controllers;

use app\helpers\TokenAuthenticationHelper;
use app\models\Bank;
use app\models\Profile;
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

  public function actionAdminListMovements($id)
  {
    try {
      $user = TokenAuthenticationHelper::token();

      if ($user->access_given !== 99) {
        return [
          'status' => Status::STATUS_UNAUTHORIZED,
          'message' => 'You are not authorized to access this page.',
          'data' => []
        ];
      }

      $getUser = Profile::find()->where(['id' => $id])->one();

      $transactions = Transactions::find()->where(['user_id' => $getUser['user_id']])->all();

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

  public function actionAdminGetMovement($idTransaction)
  {
    try {
      $user = TokenAuthenticationHelper::token();

      if ($user->access_given !== 99) {
        return [
          'status' => Status::STATUS_UNAUTHORIZED,
          'message' => 'You are not authorized to access this page.',
          'data' => []
        ];
      }

      // $getUser = Profile::find()->where(['id' => $idUser])->one();

      $transactions = Transactions::find()
        // ->where(['user_id' => $getUser['user_id']])
        ->andWhere(['id' => $idTransaction])
        ->one();

      if (empty($transactions)) {
        return [
          'status' => Status::STATUS_NOT_FOUND,
          'message' => 'Transaction not found',
          'data' => []
        ];
      }

      $response['status'] = Status::STATUS_SUCCESS;
      $response['message'] = 'Success';
      $response['data'] = $transactions;

    } catch (\Throwable $th) {
      Yii::$app->response->statusCode = Status::STATUS_INTERNAL_SERVER_ERROR;
      $response['status'] = Status::STATUS_ERROR;
      $response['message'] = $th->getMessage();
      $response['data'] = [];
    }

    return $response;
  }
  public function actionAdminUpdateMovement($idTransaction)
  {
    $params = Yii::$app->request->getBodyParams();
    $transaction = Yii::$app->db->beginTransaction();
    try {
      $user = TokenAuthenticationHelper::token();

      if ($user->access_given !== 99) {
        return [
          'status' => Status::STATUS_UNAUTHORIZED,
          'message' => 'You are not authorized to access this page.',
          'data' => []
        ];
      }

      // $getUser = Profile::find()->where(['id' => $idUser])->one();

      $transactions = Transactions::find()
        // ->where(['user_id' => $getUser['user_id']])
        ->andWhere(['id' => $idTransaction])
        ->one();

      if (empty($transactions)) {
        return [
          'status' => Status::STATUS_NOT_FOUND,
          'message' => 'Transaction not found',
          'data' => []
        ];
      }

      $transactions->attributes = $params;
      $transactions->save();
      $transaction->commit();

      $response['status'] = Status::STATUS_OK;
      $response['message'] = 'Success';
      $response['data'] = $transactions;

    } catch (\Throwable $th) {
      $transaction->rollBack();
      Yii::$app->response->statusCode = Status::STATUS_INTERNAL_SERVER_ERROR;
      $response['status'] = Status::STATUS_ERROR;
      $response['message'] = 'Error';
      $response['data'] = $th->getMessage();
    }

    return $response;
  }

  public function actionTransfer()
  {
    $params = Yii::$app->request->getBodyParams();
    $transaction = Yii::$app->db->beginTransaction();
    $amount = str_replace([',', '$ '], '', $params['amount']);
    $date = isset($params['date']) ? $params['date'] : date('Y-m-d');
    try {
      $user = TokenAuthenticationHelper::token();
      $perfil = Profile::findOne(['user_id' => $user['id']]);
      $user_transfer = Profile::findOne(['account_number' => $params['account_number']]);
      $transactions = new Transactions();
      $transactions_transfer = new Transactions();
      // $wallet = Wallet::findOne(['user_id' => $user->user_id]);
      // $wallet_transfer = Wallet::findOne(['user_id' => $user_transfer->user_id]);

      $data = [
        'user' => $perfil['id'],
        'id' => $user_transfer['id'],
        'account_number' => $user_transfer['account_number'],
        'name' => $user_transfer['name'],
        'email' => $user_transfer['email'],
        'amount' => $amount
      ];

      $response['status'] = Status::STATUS_OK;
      $response['message'] = 'Success';
      $response['data'] = $data;

    } catch (\Throwable $th) {
      $response['status'] = Status::STATUS_ERROR;
      $response['message'] = $th->getMessage();
      $response['data'] = [];
    }

    return $response;
  }
  public function actionTransferConfirmed()
  {
    $params = Yii::$app->request->getBodyParams();
    $transaction = Yii::$app->db->beginTransaction();
    $amount = str_replace([',', '$ '], '', $params['amount']);
    $date = isset($params['date']) ? $params['date'] : date('Y-m-d');

    try {
      $user = TokenAuthenticationHelper::token();
      $user_transfer = Profile::findOne(['account_number' => $params['account_number']]);
      $transactions = new Transactions();
      $transactions_transfer = new Transactions();
      $wallet = Wallet::findOne(['user_id' => $user->user_id]);
      $wallet_transfer = Wallet::findOne(['user_id' => $user_transfer->user_id]);
      //code...
    } catch (\Throwable $th) {
      //throw $th;
    }
  }

}