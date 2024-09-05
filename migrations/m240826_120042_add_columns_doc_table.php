<?php

use yii\db\Migration;

/**
 * Class m240826_120042_add_columns_doc_table
 */
class m240826_120042_add_columns_doc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('profile', 'number_contract', $this->string());
        $this->addColumn('profile', 'number_passport', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('profile', 'number_contract');
        $this->dropColumn('profile', 'number_passport');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240826_120042_add_columns_doc_table cannot be reverted.\n";

        return false;
    }
    */
}
