<?php

namespace app\controllers;

use app\helpers\TokenAuthenticationHelper;
use app\models\Address;
use app\models\Bank;
use app\models\Post;
use app\models\User;
use app\models\Profile;
use app\models\Status;
use Exception;
use Yii;
use yii\db\Query;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\filters\VerbFilter;

/*
 * Created on Thu Feb 22 2018
 * By Heru Arief Wijaya
 * Copyright (c) 2018 belajararief.com
 */

class SiteController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => Cors::className(),
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create' => ['POST', 'PUT', 'GET'],
                ],
            ],
            // 'authenticator' => [
            //   'class' => \yii\filters\auth\HttpBearerAuth::class,
            // ]
        ];
    }


    protected function verbs()
    {
        return [
            'signup' => ['POST'],
            'login' => ['POST'],
        ];
    }

    public function actionIndex()
    {
        $post = Post::find()->all();
        return [
            'status' => Status::STATUS_OK,
            'message' => 'Hello :)',
            'data' => $post
        ];
    }


    public function actionView($id)
    {

        $post = Post::findOne($id);
        return [
            'status' => Status::STATUS_FOUND,
            'message' => 'Data Found',
            'data' => $post
        ];
    }

    public function actionSignup()
    {
        $role = TokenAuthenticationHelper::token();

        $model = new User();
        if ($role['access_given'] === 0) {
            $model->status = User::STATUS_INACTIVE;
        } else {
            if ($role['access_given'] === 99) {
                $model->status = User::STATUS_ACTIVE;
            } else {
                $model->status = User::STATUS_INACTIVE;
            }
        }

        $params = Yii::$app->request->post();
        if (!$params) {
            Yii::$app->response->statusCode = Status::STATUS_BAD_REQUEST;
            return [
                'status' => Status::STATUS_BAD_REQUEST,
                'message' => "Need username, password, and email.",
                'data' => ''
            ];
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (User::find()->where(['email' => $params['email']])->one()) {
                throw new Exception("email already in use", 500);
            }
            if (User::find()->where(['username' => $params['username']])->one()) {
                throw new Exception("email already in use", 500);
            }
            $model->attributes = $params;
            $model->username = $params['username'];
            $model->email = $params['email'];
            $model->access_given = 1;

            $model->setPassword($params['password']);
            $model->generateAuthKey();
            // $model->status = User::STATUS_ACTIVE;

            if (!$model->save()) {
                throw new Exception("Error Processing Request", 1);

            }


            $address = new Address();
            $address->user_id = $model->id;
            $address->save();

            $bank = new Bank();
            $bank->user_id = $model->id;
            $bank->save();

            $profile = new Profile();
            $profile->email = $params['email'];
            $profile->user_id = $model->id;
            $profile->name = $params['name'];
            $profile->address = $address->id;
            $profile->bank_account = $bank->id;
            $profile->selfie_cadastro = isset($params['selfie']) ? $params['selfie'] : 'sem foto';
            $profile->account_number = Profile::generateAccountNumber($params['name']);
            $profile->save();

            $wallet = new \app\models\Wallet();
            $wallet->user_id = $model->id;
            $wallet->income = 0;
            $wallet->amount = 0;
            $wallet->available_for_withdrawal = 0;
            $wallet->expense = 0;
            $wallet->save();

            $transaction->commit();

            Yii::$app->response->statusCode = Status::STATUS_CREATED;
            $response['status'] = Status::STATUS_CREATED;
            $response['message'] = 'You are now a member!';
            $response['data'] = \app\models\User::findByUsername($model->username);


        } catch (\Throwable $th) {
            $transaction->rollBack();
            $model->getErrors();
            $response['hasErrors'] = $model->hasErrors();
            $response['errors'] = $model->getErrors();
            $response['message'] = "Error saving data! {$th->getMessage()}";
            $response['data'] = [];
            $response['status'] = 500;
            // Yii::$app->response->statusCode = Status::STATUS_INTERNAL_SERVER_ERROR;

        }

        return $response;

    }

    public function actionLogin()
    {
        $params = Yii::$app->request->post();
        if (empty($params['username']) || empty($params['password']))
            return [
                'status' => Status::STATUS_BAD_REQUEST,
                'message' => "Need username and password.",
                'data' => ''
            ];

        $user = User::findByUsername($params['username']);

        if ($user->validatePassword($params['password'])) {
            if (isset($params['consumer']))
                $user->consumer = $params['consumer'];
            if (isset($params['access_given']))
                $user->access_given = $params['access_given'];

            Yii::$app->response->statusCode = Status::STATUS_FOUND;
            $user->generateAuthKey();
            $user->save();

            $number_account = Profile::find()->where(['user_id' => $user->id])->one();
            if (empty($number_account['account_number'])) {
                $number_account->account_number = Profile::generateAccountNumber($number_account['name']);
                $number_account->save();
            }

            return [
                'status' => Status::STATUS_FOUND,
                'message' => 'Login Succeed, save your token',
                'data' => [
                    'id' => $user->username,
                    'token' => $user->auth_key,
                    'email' => $user['email'],
                ]
            ];
        } else {
            Yii::$app->response->statusCode = Status::STATUS_UNAUTHORIZED;
            return [
                'status' => Status::STATUS_UNAUTHORIZED,
                'message' => 'Username and Password not found. Check Again!',
                'data' => ''
            ];
        }
    }

    public function actionRole()
    {
        try {
            $user = TokenAuthenticationHelper::token();

            return [
                'status' => Status::STATUS_FOUND,
                'message' => 'Data Found',
                'data' => $user['access_given']
            ];
        } catch (\Throwable $th) {
            Yii::$app->response->statusCode = Status::STATUS_UNAUTHORIZED;
            return [
                'status' => Status::STATUS_UNAUTHORIZED,
                'message' => 'You are not authorized to access this page.',
                'data' => []
            ];
        }
    }

    public function actionForgot()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $params = Yii::$app->request->bodyParams;

            if (empty($params['email'])) {
                return [
                    'status' => Status::STATUS_BAD_REQUEST,
                    'message' => "Need email.",
                    'data' => []
                ];
            }

            $username = $params['email'];
            $user = User::findByEmail($username);
            if (!$user) {
                return [
                    'status' => Status::STATUS_BAD_REQUEST,
                    'message' => "Cadastro nao localizado",
                    'data' => []
                ];
            }

            $profile = Profile::find()->where(['user_id' => $user['id']])->one();

            $tokenReset = Yii::$app->security->generateRandomString(80) . '_' . time();
            $getEmail = strtolower($user['email']);
            $nameAdmin = "Monaco Bank";
            $emailAdmin = $_ENV['EMAIL_NO_REPLAY'];
            $subject = "Reset Password";
            $logo = "";
            $linkReset = "$_ENV[HOST_URL_APP]/resetar-senha/$tokenReset";
            $user->password_reset_token = $tokenReset;

            $user->save(false);
            $transaction->commit();

            $name = '=?UTF-8?B?' . base64_encode($nameAdmin) . '?=';
            $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
            $headers = "From: $name <{$emailAdmin}>\r\n" .
                "Reply-To: {$emailAdmin}\r\n" .
                "MIME-Version: 1.0\r\n" .
                "Content-type: text/html; charset=UTF-8";

            \app\service\SendMailServices::sendMail(
                $_ENV['EMAIL_NO_REPLAY'],
                $getEmail,
                'Reset Password',
                [
                    'customer-name' => $profile['name'],
                    'link-reset-url' => $linkReset,
                ],
                'reset-password'
            );

            $response['status'] = Status::STATUS_OK;
            $response['message'] = "Forgot password";
            $response['data'] = [];


        } catch (\Throwable $th) {
            $transaction->rollBack();
            $response['status'] = Status::STATUS_ERROR;
            $response['message'] = $th->getMessage();
            $response['data'] = [];

        }

        return $response;
    }


    public function actionReset($token)
    {
        $params = Yii::$app->request->post();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!User::isPasswordResetTokenValid($token)) {
                return [
                    'status' => Status::STATUS_BAD_REQUEST,
                    'message' => "Token Expirado.",
                    'data' => []
                ];
            }

            if (empty($params['password'])) {
                return [
                    'status' => Status::STATUS_BAD_REQUEST,
                    'message' => "Need password.",
                    'data' => []
                ];
            }
            $user = User::findByPasswordResetToken($token);
            $user->password_hash = Yii::$app->security->generatePasswordHash($params['password']);
            $user->setPassword($params['password']);
            $user->password_reset_token = null;
            $user->save();

            $transaction->commit();
            $response['status'] = Status::STATUS_ERROR;
            $response['message'] = "Alterado com sucesso";
            $response['data'] = [];

        } catch (\Throwable $th) {
            $transaction->rollBack();
            $response['status'] = Status::STATUS_OK;
            $response['message'] = $th->getMessage();
            $response['data'] = [];
        }

        return $response;
    }


    public function actionUser()
    {
        $user = TokenAuthenticationHelper::token();

        $response['status'] = Status::STATUS_OK;
        $response['message'] = "Alterado com sucesso";
        $response['data'] = $user;

        return $response;
    }
    public function actionUpdatePass()
    {
        $transaction = Yii::$app->db->beginTransaction();
        $params = Yii::$app->request->post();
        try {
            $user = TokenAuthenticationHelper::token();

            if (!$user->validatePassword($params['oldPass'])) {
                $response['status'] = Status::STATUS_BAD_REQUEST;
                $response['message'] = "Senha Incorreta";
                $response['data'] = [];
                return $response;
            }
            $user->setPassword($params['password']);
            $user->save();
            $transaction->commit();

            $response['status'] = Status::STATUS_ACCEPTED;
            $response['message'] = "Senha alterada com sucesso";
            $response['data'] = $user;

        } catch (\Throwable $th) {
            $transaction->rollBack();
            $response['status'] = Status::STATUS_ERROR;
            $response['message'] = $th->getMessage();
            $response['data'] = [];
        }


        return $response;
    }


    public function actionDelete()
    {
        $role = TokenAuthenticationHelper::token();

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if ($role->access_given !== 99) {
                throw new Exception('You are not authorized to access this page.', Status::STATUS_BAD_REQUEST);
            }

            $params = Yii::$app->request->post();

            $user = User::find()->where(['id' => $params['id']])->one();
            $profile = Profile::find()->where(['user_id' => $user['id']])->one();

            $username = $user['username'];
            $user->status = User::STATUS_DELETED;
            $user->username = "{$username}_DELETED";

            $profile->deleted_at = time();

            $user->save();
            $profile->save();

            $transaction->commit();

            $response['status'] = Status::STATUS_ACCEPTED;
            $response['message'] = "User deleted";
            $response['data'] = [];

        } catch (\Throwable $th) {
            $transaction->rollBack();
            $response['status'] = Status::STATUS_ERROR;
            $response['message'] = $th->getMessage();
            $response['data'] = [];
        }

        return $response;

    }

    public function actionNewUsers()
    {
        $role = TokenAuthenticationHelper::token();

        try {

            if ($role->access_given !== 99) {
                throw new Exception('You are not authorized to access this page.', Status::STATUS_BAD_REQUEST);
            }

            $user = (new Query())
                ->select(['*'])
                ->from('user U')
                ->leftJoin('profile P', 'P.user_id = U.id')
                ->where(['status' => User::STATUS_INACTIVE])
                ->all();

            $response['status'] = Status::STATUS_ACCEPTED;
            $response['message'] = "Success";
            $response['data'] = $user;

        } catch (\Throwable $th) {
            $response['status'] = Status::STATUS_ERROR;
            $response['message'] = $th->getMessage();
            $response['data'] = [];
        }

        return $response;
    }

    public function actionUserBlocked()
    {
        $role = TokenAuthenticationHelper::token();

        try {

            if ($role->access_given !== 99) {
                throw new Exception('You are not authorized to access this page.', Status::STATUS_BAD_REQUEST);
            }

            $user = (new Query())
                ->select(['*'])
                ->from('user U')
                ->leftJoin('profile P', 'P.user_id = U.id')
                ->where(['status' => User::STATUS_DELETED])
                ->all();

            $response['status'] = Status::STATUS_ACCEPTED;
            $response['message'] = "Success";
            $response['data'] = $user;

        } catch (\Throwable $th) {
            $response['status'] = Status::STATUS_ERROR;
            $response['message'] = $th->getMessage();
            $response['data'] = [];
        }

        return $response;
    }

    public function actionActivateUser()
    {
        $role = TokenAuthenticationHelper::token();
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if ($role->access_given !== 99) {
                throw new Exception('You are not authorized to access this page.', Status::STATUS_BAD_REQUEST);
            }
            $params = Yii::$app->request->post();

            $user = User::find()->where(['id' => $params['id']])->one();
            $profile = Profile::find()->where(['user_id' => $user['id']])->one();

            $user->status = User::STATUS_ACTIVE;
            $profile->deleted_at = null;

            $user->save();
            $profile->save();

            $transaction->commit();

            $response['status'] = Status::STATUS_ACCEPTED;
            $response['message'] = "User activate";
            $response['data'] = [];

        } catch (\Throwable $th) {
            $transaction->rollBack();
            $response['status'] = Status::STATUS_ERROR;
            $response['message'] = $th->getMessage();
            $response['data'] = [];
        }

        return $response;
    }


}