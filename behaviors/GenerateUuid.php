<?php

namespace app\behaviors;

use Ramsey\Uuid\Uuid;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class GenerateUuid extends Behavior
{
  public function events()
  {
    return [
      ActiveRecord::EVENT_BEFORE_INSERT => 'generateUuid',
    ];
  }

  public function generateUuid()
  {
    $uuid = Uuid::uuid4();
    $this->owner->id = $uuid->toString();
  }
}