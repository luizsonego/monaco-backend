<?php

namespace app\models;

use app\behaviors\GenerateUuid;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Address extends ActiveRecord
{
  public static function tableName()
  {
    return '{{%address}}';
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
          'zip_code',
          'address',
          'number',
          'complement',
          'neighborhood',
          'city',
          'state',
          'country',
        ],
        'string'
      ],
      [['user_id'], 'integer']
    ];
  }

}