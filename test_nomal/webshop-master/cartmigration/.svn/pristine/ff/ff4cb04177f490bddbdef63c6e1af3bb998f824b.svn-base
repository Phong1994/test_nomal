<?php

class LECM_Setup_Install
{
    public function run($db)
    {
        $version_install = Bootstrap::getVersionInstall();

        if(version_compare($version_install, '4.0.1') < 0){
            $table_setting = $db->getTableName(Bootstrap::TABLE_SETTING);
            $create_table_setting_query = "CREATE TABLE IF NOT EXISTS `" . $table_setting . "`(`id` INTEGER PRIMARY KEY AUTOINCREMENT, `key` VARCHAR(255), `value` LONGTEXT)";
            $create_table_setting_result = $db->queryRaw($create_table_setting_query);

            $table_map = $db->getTableName(Bootstrap::TABLE_MAP);
            $create_table_map_query = "CREATE TABLE IF NOT EXISTS `" . $table_map . "` (`url_src` VARCHAR(255), `url_desc`  VARCHAR(255), `type` VARCHAR(255), `id_src` BIGINT, `id_desc` BIGINT, `code_src` TEXT, `code_desc` TEXT, `value` LONGTEXT)";
            $create_table_map_result = $db->queryRaw($create_table_map_query);

            $table_notice = $db->getTableName(Bootstrap::TABLE_NOTICE);
            $create_table_notice_query = "CREATE TABLE IF NOT EXISTS `" . $table_notice . "` (`id` INT(11) UNIQUE NOT NULL, `notice` LONGTEXT)";
            $create_table_notice_result = $db->queryRaw($create_table_notice_query);

            $table_recent = $db->getTableName(Bootstrap::TABLE_RECENT);
            $create_table_recent_query = "CREATE TABLE IF NOT EXISTS `". $table_recent . "` (`url_src` VARCHAR(255) NOT NULL,  `url_desc`  VARCHAR(255), `notice` LONGTEXT)";
            $create_table_recent_result = $db->queryRaw($create_table_recent_query);

            if($create_table_setting_result['result'] == 'success' && $create_table_map_result['result'] == 'success' && $create_table_notice_result && $create_table_recent_result['result'] == 'success'){
                $this->installSettingDefault($db);
                Bootstrap::setVersionInstall('4.0.1');
            }
        }

        return $this;
    }

    public function installSettingDefault($db)
    {
        $config = array(
            'storage' => 200,
            'taxes' => 4,
            'manufacturers' => 4,
            'categories' => 4,
            'products' => 4,
            'customers' => 4,
            'orders' => 4,
            'reviews' => 4,
            'pages'         => 4,
            'blocks'        => 4,
            'widgets'       => 4,
            'polls'         => 4,
            'transactions'  => 4,
            'newsletters'   => 4,
            'users'         => 4,
            'rules'         => 4,
            'cartrules'     => 4,
            'delay' => 0.01,
            'retry' => 30,
            'src_prefix' => '',
            'target_prefix' => '',
            'license' => '123456',
        );
        foreach($config as $key => $value){
            $db->insertObj(Bootstrap::TABLE_SETTING, array(
                'key' => $key,
                'value' => $value,
            ), false);
        }
        return $this;
    }
}
