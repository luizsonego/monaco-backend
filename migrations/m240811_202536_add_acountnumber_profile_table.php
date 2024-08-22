<?php

use yii\db\Migration;

/**
 * Class m240811_202536_add_acountnumber_profile_table
 */
class m240811_202536_add_acountnumber_profile_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('profile', 'account_number', $this->string());
        $this->addColumn('profile', 'apelido', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('profile', 'account_number');
        $this->dropColumn('profile', 'apelido');
    }

}
