<?php

namespace app\modules\v1\controllers;

use app\controllers\SiteController as ControllersSiteController;
use app\models\Status;
use app\models\User;
use ImageKit\ImageKit;
use Symfony\Component\Dotenv\Dotenv;
use Yii;
use yii\filters\VerbFilter;

// Created By @hoaaah * Copyright (c) 2020 belajararief.com

$dotenv = new Dotenv();
$dotenv->load(Yii::getAlias('@env'));

class SiteController extends ControllersSiteController
{
  public $enableCsrfValidation = false;

  // public static function allowedDomains()
  // {
  //     return [
  //         // '*',                        // star allows all domains
  //         'http://localhost:3000',
  //     ];
  // }

  /**
   * Renders the index view for the module
   * @return string
   */
  public function behaviors()
  {
    return [
      'corsFilter' => [
        'class' => \yii\filters\Cors::className(),
      ],
      'verbs' => [
        'class' => VerbFilter::className(),
        'actions' => [
          'create' => ['POST', 'PUT', 'GET'],
        ],
      ],
    ];
  }

  public function actionIndex()
  {
    return [
      'status' => Status::STATUS_OK,
      'message' =>
        'You may customize this page by editing the following file:' .
        __FILE__,
      'data' => '',
    ];
  }

  public function actionAuth()
  {

    $public_key = $_ENV['IMAGEKIT_PUBLIC_KEY'];
    $private_key = $_ENV['IMAGEKIT_PRIVATE_KEY'];
    $url_end_point = $_ENV['IMAGEKIT_ENDPOINT_URL'];

    $imageKit = new ImageKit(
      $public_key,
      $private_key,
      $url_end_point
    );

    // date_default_timezone_set('UTC');
    // $date = strtotime('-90 minutes');

    $authenticationParamters = $imageKit->getAuthenticationParameters();

    $headers = Yii::$app->response->headers;
    $headers->set('Access-Control-Allow-Origin', '*');
    $headers->set(
      'Access-Control-Allow-Headers',
      'Origin, X-Requested-With, Content-Type, Accept'
    );
    $headers->set('Date', 'Wed, 21 Oct 2015 07:28:00 GMT');

    return $authenticationParamters;
  }


}