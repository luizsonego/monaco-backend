<?php

namespace app\service;

use Yii;

class SendMailServices
{

  /**
   * @param string $from
   * @param string $to
   * @param array $body
   * @param string $template
   * @param string $subject
   */
  public static function sendMail($from = '', $to, $subject, $body, $template)
  {
    Yii::$app->mailer->compose($template, ['body' => $body])
      ->setFrom($from)
      ->setTo($to)
      ->setSubject($subject)
      ->send();
    // return $send;
  }
}