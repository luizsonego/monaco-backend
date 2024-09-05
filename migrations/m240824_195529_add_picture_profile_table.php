<?php

use yii\db\Migration;

/**
 * Class m240824_195529_add_picture_profile_table
 */
class m240824_195529_add_picture_profile_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('profile', 'selfie_cadastro', $this->string());
        $this->addColumn('profile', 'profile_pic', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('profile', 'selfie_cadastro');
        $this->dropColumn('profile', 'profile_pic');
    }

}
