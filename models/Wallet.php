<?php

namespace app\models;

use app\behaviors\GenerateUuid;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Wallet extends ActiveRecord
{
  public static function tableName()
  {
    return '{{%wallet}}';
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
        'attributes' => [
          ActiveRecord::EVENT_BEFORE_INSERT => [
            'created_at',
            'updated_at',
          ],
          ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
        ],
      ],
      'generateUUid' => [
        'class' => GenerateUuid::className(),
      ],
    ];
  }


  public function rules()
  {
    return [
      [['income', 'expense', 'amount', 'available_for_withdrawal'], 'number'],
      [['user_id'], 'integer'],
    ];
  }

}