<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%initial_data_base}}`.
 */
class m240714_134600_create_initial_data_base_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%profile%}}', [
            'id' => $this->string(36)->notNull(),
            'name' => $this->string(90),
            'document' => $this->string()->comment('cpf/cnpj'),
            'phone' => $this->string(),
            'whatsapp' => $this->string(),
            'email' => $this->string(),
            'user_id' => $this->integer()->comment('id referencia com tabela de login'),
            'bank_account' => $this->string(),
            'address' => $this->string(),
            'time_contract' => $this->string(),
            'observation' => $this->string(),
            'created_at' => $this->string(),
            'updated_at' => $this->string(),
            'deleted_at' => $this->string(),
        ], $tableOptions);

        $this->createTable(
            '{{%transactions%}}',
            [
                'id' => $this->string(36)->notNull(),
                'percent' => $this->decimal(9, 2),
                'amount_money' => $this->decimal(19, 2)->defaultValue('0.00'),
                'wallet' => $this->string(),
                'user_id' => $this->integer(),
                'month_year' => $this->string(),
                'date' => $this->string(12),
                'type_transaction' => $this->integer(),
                'description' => $this->text(),
                'created_at' => $this->string(),
                'updated_at' => $this->string(),
                'deleted_at' => $this->string(),
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%wallet%}}',
            [
                'id' => $this->string(36)->notNull(),
                'income' => $this->decimal(19, 2)->defaultValue('0.00')->comment('receita'),
                'expense' => $this->decimal(19, 2)->defaultValue('0.00')->comment('despesa'),
                'amount' => $this->decimal(19, 2)->defaultValue('0.00'),
                'available_for_withdrawal' => $this->decimal(19, 2)->defaultValue('0.00'),
                'user_id' => $this->integer()->comment(''),
                'created_at' => $this->string(),
                'updated_at' => $this->string(),
                'deleted_at' => $this->string(),
            ],
            $tableOptions
        );
        $this->createTable('{{%address%}}', [
            'id' => $this->string(36)->notNull(),
            'zip_code' => $this->string(9),
            'address' => $this->string(),
            'number' => $this->string(),
            'complement' => $this->string(),
            'neighborhood' => $this->string(),
            'city' => $this->string(),
            'state' => $this->string(),
            'country' => $this->string(),
            'user_id' => $this->integer(),
            'created_at' => $this->string(),
            'updated_at' => $this->string(),
            'deleted_at' => $this->string(),
        ], $tableOptions);
        $this->createTable('{{%bank_account%}}', [
            'id' => $this->string(36)->notNull(),
            'bank' => $this->string(),
            'bank_agency' => $this->string(),
            'bank_account_type' => $this->string(),
            'bank_account_number' => $this->string(),
            'bank_pix' => $this->string(),
            'bank_iban' => $this->string(),
            'bank_swift' => $this->string(),
            'bank_office_phone' => $this->string(),
            'user_id' => $this->integer(),
            'created_at' => $this->string(),
            'updated_at' => $this->string(),
            'deleted_at' => $this->string(),
        ], $tableOptions);


        $this->insert('{{%profile%}}', [
            'id' => '4c3b7a7b-bf36-4f91-88d4-d8387e936b68',
            'name' => 'admin',
            'email' => 'admin@email.com',
            'user_id' => 1,
        ]);
        $this->insert('{{%wallet%}}', [
            'id' => 'd2303823-3948-440b-a508-4050326c9db3',
            'user_id' => 1,
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%profile}}');
        $this->dropTable('{{%transactions}}');
        $this->dropTable('{{%wallet}}');
        $this->dropTable('{{%address}}');
        $this->dropTable('{{%bank_account}}');
    }
}
