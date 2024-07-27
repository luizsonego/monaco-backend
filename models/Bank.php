<?php

namespace app\models;

use app\behaviors\GenerateUuid;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Bank extends ActiveRecord
{
  public static function tableName()
  {
    return '{{%bank_account}}';
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
          'bank',
          'bank_agency',
          'bank_account_type',
          'bank_account_number',
          'bank_pix',
          'bank_iban',
          'bank_swift',
          'bank_office_phone'
        ],
        'string'
      ],
      [['user_id'], 'integer']
    ];
  }

}