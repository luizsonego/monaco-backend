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
          'apelido',
          'document',
          'phone',
          'whatsapp',
          'email',
          'bank_account',
          'time_contract',
          'observation',
          'account_number',
          'selfie_cadastro',
          'profile_pic',
          'number_contract',
          'number_passport',
        ],
        'string'
      ],
      [['user_id'], 'integer']
    ];
  }

  public static function generateAccountNumber($documentNumber)
  {
    // Gerar uma hash do número usando crc32
    $hash = crc32(ltrim($documentNumber, '0'));
    // Formatar a hash como um número de sete dígitos
    $formattedHash = sprintf('%07d', $hash);
    $hashCode4limit = substr($formattedHash, 0, 4);

    // Gerar nove dígitos aleatórios
    $number = mt_rand(10000, 99999);

    // Inserir o hífen no oitavo dígito
    $formattedNumber = substr_replace($number . $hashCode4limit, '-', 8, 0);

    return $formattedNumber;
  }
}