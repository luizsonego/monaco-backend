<?php

namespace app\modules\v1\controllers;

use app\helpers\TokenAuthenticationHelper;
use app\models\Address;
use app\models\Bank;
use app\models\Profile;
use app\models\Status;
use app\models\Transactions;
use app\models\User;
use app\models\Wallet;
use Exception;
use Yii;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;


class ProfileController extends ActiveController
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
        'class' => \yii\filters\Cors::className(),
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

  public function actionUpdate($id)
  {
    $params = Yii::$app->request->getBodyParams();
    $transaction = Yii::$app->db->beginTransaction();

    try {
      $model = $this->modelClass::findOne($id);
      $model->attributes = $params;

      if (isset($params['email'])) {
        $user = User::findOne(['id' => $model->user_id]);
        $user->email = $params['email'];
        $user->save();
      }

      $address = Address::findOne(['user_id' => $model->user_id, 'id' => $model->address]);
      $address->attributes = $params;
      $address->save();

      $bank = Bank::findOne(['user_id' => $model->user_id, 'id' => $model->bank_account]);
      $bank->attributes = $params;
      $bank->save();

      $model->save();
      $transaction->commit();

      Yii::$app->response->statusCode = Status::STATUS_CREATED;
      $response['status'] = Status::STATUS_CREATED;
      $response['message'] = 'Data is updated!';
      $response['data'] = [];

    } catch (\Throwable $th) {
      $transaction->rollBack();
      Yii::$app->response->statusCode = Status::STATUS_BAD_REQUEST;
      $response['status'] = Status::STATUS_BAD_REQUEST;
      $response['message'] = $th->getMessage();
      $response['data'] = [];
    }

    return $response;
  }

  public function actionInvestiment($id)
  {
    $params = Yii::$app->request->getBodyParams();
    $transaction = Yii::$app->db->beginTransaction();
    $amount = str_replace([',', '$ '], '', $params['amount']);
    $date = isset($params['date']) ? $params['date'] : date('Y-m-d');

    try {
      $user = Profile::findOne(['id' => $id]);
      $transactions = new Transactions();
      $wallet = Wallet::findOne(['user_id' => $user->user_id]);

      $transactions->user_id = $user->user_id;
      $transactions->wallet = $wallet->id;
      $transactions->month_year = substr($date, 0, 7);
      $transactions->date = $date;
      $transactions->amount_money = $amount;
      $transactions->type_transaction = 1; //novo aporte
      isset($params['description']) ? $transactions->description = $params['description'] : $transactions->description = "Investimento de $params[amount] em $date";

      $wallet->amount += $amount;
      $wallet->income += $amount;

      $wallet->save();
      $transactions->save();

      $transaction->commit();

      Yii::$app->response->statusCode = Status::STATUS_CREATED;
      $response['status'] = Status::STATUS_CREATED;
      $response['message'] = 'Data is updated!';
      $response['data'] = [];


    } catch (\Throwable $th) {
      $transaction->rollBack();
      $response['status'] = Status::STATUS_BAD_REQUEST;
      $response['message'] = $th->getMessage();
      $response['data'] = [];
    }

    return $response;
  }

  public function actionPercentage($id)
  {
    $params = Yii::$app->request->getBodyParams();
    $transaction = Yii::$app->db->beginTransaction();

    try {
      $user = Profile::findOne(['id' => $id]);
      $transactions = new Transactions();
      $wallet = Wallet::findOne(['user_id' => $user->user_id]);

      $amountCalculateFromPercantage = $params['percent'] * $wallet->amount / 100;

      $transactions->user_id = $user->user_id;
      $transactions->wallet = $wallet->id;
      $transactions->month_year = date('Y-m');
      $transactions->date = date('Y-m-d');
      $transactions->percent = $params['percent'];
      $transactions->type_transaction = 2; //calculo de porcentagem
      $transactions->amount_money = $amountCalculateFromPercantage;

      isset($params['description']) ? $transactions->description = $params['description'] : $transactions->description = "Calculo no valor de $amountCalculateFromPercantage no mês de " . date('m/Y');

      $wallet->amount += $amountCalculateFromPercantage;
      $wallet->save();
      $transactions->save();

      $transaction->commit();
      Yii::$app->response->statusCode = Status::STATUS_CREATED;
      $response['status'] = Status::STATUS_CREATED;
      $response['message'] = 'Data is updated!';
      $response['data'] = [];

    } catch (\Throwable $th) {
      $transaction->rollBack();
      $response['status'] = Status::STATUS_BAD_REQUEST;
      $response['message'] = $th->getMessage();
      $response['data'] = [];
    }

    return $response;

  }

  public function actionWithdraw($id)
  {
    $params = Yii::$app->request->getBodyParams();
    $transaction = Yii::$app->db->beginTransaction();
    $amount = str_replace([',', '$ '], '', $params['amount']);
    $date = isset($params['date']) ? $params['date'] : date('Y-m-d');

    try {
      $user = Profile::findOne(['id' => $id]);
      $transactions = new Transactions();
      $wallet = Wallet::findOne(['user_id' => $user->user_id]);

      if ($wallet->amount < $amount) {
        $response['status'] = Status::STATUS_BAD_REQUEST;
        $response['message'] = "Saldo insuficiente!";
        $response['data'] = [];
        return $response;
        // throw new Exception('Saldo insuficiente!');
      }

      $transactions->user_id = $user->user_id;
      $transactions->wallet = $wallet->id;
      $transactions->month_year = substr($date, 0, 7);
      $transactions->date = $date;
      $transactions->amount_money = $amount;
      $transactions->type_transaction = 3; //saque
      isset($params['description']) ? $transactions->description = $params['description'] : $transactions->description = "Saque de $params[amount] em $date";
      $wallet->amount -= $amount;

      $wallet->save();
      $transactions->save();

      $transaction->commit();

      Yii::$app->response->statusCode = Status::STATUS_CREATED;
      $response['status'] = Status::STATUS_CREATED;
      $response['message'] = 'Data is updated!';
      $response['data'] = [];

    } catch (\Throwable $th) {
      $transaction->rollBack();
      $response['status'] = Status::STATUS_BAD_REQUEST;
      $response['message'] = $th->getMessage();
      $response['data'] = [];
    }
    return $response;
  }

  public function actionTransfer($id)
  {
    $params = Yii::$app->request->getBodyParams();
    $transaction = Yii::$app->db->beginTransaction();
    $amount = str_replace([',', '$ '], '', $params['amount']);
    $date = isset($params['date']) ? $params['date'] : date('Y-m-d');

    try {
      $user = Profile::findOne(['id' => $id]);
      $user_transfer = Profile::findOne(['id' => $params['user_id']]);
      $transactions = new Transactions();
      $transactions_transfer = new Transactions();
      $wallet = Wallet::findOne(['user_id' => $user->user_id]);
      $wallet_transfer = Wallet::findOne(['user_id' => $user_transfer->user_id]);

      if (!$wallet_transfer) {
        $response['status'] = Status::STATUS_BAD_REQUEST;
        $response['message'] = "Usuário não encontrado!";
        $response['data'] = [];
        return $response;
        // throw new Exception('User not found!');
      }

      if ($wallet->amount < $amount) {
        $response['status'] = Status::STATUS_BAD_REQUEST;
        $response['message'] = "Saldo insuficiente!";
        $response['data'] = [];
        return $response;
        // throw new Exception('Saldo insuficiente!');
      }

      // saindo
      $amount_transfer = number_format($amount, 2, '.', '');
      $transactions->user_id = $user->user_id;
      $transactions->wallet = $wallet->id;
      $transactions->month_year = substr($date, 0, 7);
      $transactions->date = $date;
      $transactions->amount_money = $amount;
      $transactions->type_transaction = 4; //transferencia
      isset($params['description']) ?
        $transactions->description = $params['description'] :
        $transactions->description = "Wire transfer off $amount_transfer to $user_transfer->name ($user_transfer->account_number) in $date";

      // chegando
      $transactions_transfer->user_id = $user_transfer->user_id;
      $transactions_transfer->wallet = $wallet_transfer->id;
      $transactions_transfer->month_year = substr($date, 0, 7);
      $transactions_transfer->date = $date;
      $transactions_transfer->amount_money = $amount;
      $transactions_transfer->type_transaction = 4; //transferencia
      isset($params['description']) ?
        $transactions_transfer->description = $params['description'] :
        $transactions_transfer->description = "Wire transfer off $amount_transfer by $user->name ($user->account_number) in $date";
      // $transactions_transfer->description = "Transferencia de $amount_transfer do usuário $user->user_id em $date";

      $wallet->amount -= $amount;
      $wallet_transfer->amount += $amount;

      $wallet->save();
      $wallet_transfer->save();
      $transactions_transfer->save();
      $transactions->save();

      $transaction->commit();
      Yii::$app->response->statusCode = Status::STATUS_CREATED;
      $response['status'] = Status::STATUS_CREATED;
      $response['message'] = 'Data is updated!';
      $response['data'] = [];
    } catch (\Throwable $th) {
      $transaction->rollBack();
      $response['status'] = Status::STATUS_BAD_REQUEST;
      $response['message'] = $th->getMessage();
      $response['data'] = [];
    }
    return $response;
  }

  public function actionIndex()
  {
    try {
      $user = TokenAuthenticationHelper::token();

      $profile = Profile::find()->where(['user_id' => $user['id']])->one();
      $address = Address::find()->where(['id' => $profile['address']])->one();
      $bank = Bank::find()->where(['id' => $profile['bank_account']])->one();

      $data['profile'] = $profile;
      $data['address'] = $address;
      $data['bank'] = $bank;

      $response['status'] = Status::STATUS_CREATED;
      $response['message'] = 'Success!';
      $response['data'] = $data;

    } catch (\Throwable $th) {

      $response['status'] = Status::STATUS_BAD_REQUEST;
      $response['message'] = $th->getMessage();
      $response['data'] = [];

    }

    return $response;
  }

  public function actionListInvestors()
  {
    try {
      $user = TokenAuthenticationHelper::token();

      if ($user->access_given !== 99) {
        Yii::$app->response->statusCode = Status::STATUS_BAD_REQUEST;
        $response['status'] = Status::STATUS_FORBIDDEN;
        $response['message'] = 'Você não tem permissão para acessar essa página';
        $response['data'] = [];
        return $response;
      }
      $profile = Profile::find()->where(['<>', 'name', 'admin'])->all();

      $response['status'] = Status::STATUS_ACCEPTED;
      $response['message'] = 'Success';
      $response['data'] = $profile;


    } catch (\Throwable $th) {
      Yii::$app->response->statusCode = Status::STATUS_BAD_REQUEST;
      $response['status'] = Status::STATUS_BAD_REQUEST;
      $response['message'] = $th->getMessage();
      $response['data'] = [];
    }

    return $response;
  }

  public function actionDescription($id)
  {
    try {
      $user = TokenAuthenticationHelper::token();
      if ($user->access_given !== 99) {
        Yii::$app->response->statusCode = Status::STATUS_BAD_REQUEST;
        $response['status'] = Status::STATUS_FORBIDDEN;
        $response['message'] = 'Você não tem permissão para acessar essa página';
        $response['data'] = [];
        return $response;
      }

      $profile = Profile::find()->where(['id' => $id])->one();
      $address = Address::find()->where(['id' => $profile['address']])->one();
      $bank = Bank::find()->where(['id' => $profile['bank_account']])->one();
      $wallet = Wallet::find()->where(['user_id' => $profile['user_id']])->one();
      $user = User::find()->select(['status', 'username'])->where(['id' => $profile['user_id']])->one();

      $data['profile'] = $profile;
      $data['address'] = $address;
      $data['bank'] = $bank;
      $data['wallet'] = $wallet;
      $data['user'] = $user;

      $response['status'] = Status::STATUS_CREATED;
      $response['message'] = 'Success!';
      $response['data'] = $data;

    } catch (\Throwable $th) {
      Yii::$app->response->statusCode = Status::STATUS_BAD_REQUEST;
      $response['status'] = Status::STATUS_BAD_REQUEST;
      $response['message'] = $th->getMessage();
      $response['data'] = [];
    }

    return $response;
  }

  public function actionAdminUser($id)
  {
    try {
      $user = TokenAuthenticationHelper::token();
      $profile = Profile::find()->where(['id' => $id])->one();
      $address = Address::find()->where(['id' => $profile['address']])->one();
      $bank = Bank::find()->where(['id' => $profile['bank_account']])->one();
      $wallet = Wallet::find()->where(['user_id' => $profile['user_id']])->one();

      $data['profile'] = $profile;
      $data['address'] = $address;
      $data['bank'] = $bank;
      $data['wallet'] = $wallet;

      $response['status'] = Status::STATUS_CREATED;
      $response['message'] = 'Success!';
      $response['data'] = $data;

    } catch (\Throwable $th) {
      Yii::$app->response->statusCode = Status::STATUS_BAD_REQUEST;
      $response['status'] = Status::STATUS_BAD_REQUEST;
      $response['message'] = $th->getMessage();
      $response['data'] = [];
    }

    return $response;
  }




}