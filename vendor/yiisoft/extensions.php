<?php

$vendorDir = dirname(__DIR__);

return array (
  'kartik-v/yii2-mpdf' => 
  array (
    'name' => 'kartik-v/yii2-mpdf',
    'version' => 'dev-master',
    'alias' => 
    array (
      '@kartik/mpdf' => $vendorDir . '/kartik-v/yii2-mpdf/src',
    ),
  ),
  'yii2tech/ar-softdelete' => 
  array (
    'name' => 'yii2tech/ar-softdelete',
    'version' => '1.0.4.0',
    'alias' => 
    array (
      '@yii2tech/ar/softdelete' => $vendorDir . '/yii2tech/ar-softdelete/src',
    ),
  ),
  'yiisoft/yii2-swiftmailer' => 
  array (
    'name' => 'yiisoft/yii2-swiftmailer',
    'version' => '2.1.3.0',
    'alias' => 
    array (
      '@yii/swiftmailer' => $vendorDir . '/yiisoft/yii2-swiftmailer/src',
    ),
  ),
);
