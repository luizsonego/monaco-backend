<?php

namespace app\models;

use app\behaviors\GenerateUuid;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Profile extends ActiveRecord
{
  public static function tableName()
  {
    return '{{%profile}}';
  }

  public static function primaryKey()
  {
    return ['id'];
  }

  public function behaviors()
  {
    return [
      'timestamp' => [
        'class' => TimestampBehavior::className(),
      ],
      'generateUUid' => [
        'class' => GenerateUuid::className(),
      ],
    ];
  }

  public function rules()
  {
    return [
      [
        [
          'name',
          'document',
          'phone',
          'whatsapp',
          'email',
          'bank_account',
          'time_contract',
          'observation',
        ],
        'string'
      ],
      [['user_id'], 'integer']
    ];
  }

}