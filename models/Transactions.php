<?php

namespace app\models;

use app\behaviors\GenerateUuid;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Transactions extends ActiveRecord
{
  public static function tableName()
  {
    return '{{%transactions}}';
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
      [['wallet', 'month_year', 'date', 'description'], 'string'],
      [['user_id', 'type_transaction'], 'integer'],
      [['percent', 'amount_money'], 'number'],
    ];
  }

}