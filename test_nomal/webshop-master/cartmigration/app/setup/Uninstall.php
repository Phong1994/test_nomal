<?php

class LECM_Setup_Uninstall
{
    public function run($db)
    {
        $isUninstall = false;

        $version_install = Bootstrap::getVersionInstall();

        if(!$isUninstall && (version_compare($version_install, '1.0.0') >= 0)){
            $table_setting = $db->getTableName(Bootstrap::TABLE_SETTING);
            $table_map = $db->getTableName(Bootstrap::TABLE_MAP);
            $table_notice = $db->getTableName(Bootstrap::TABLE_NOTICE);
            $table_recent = $db->getTableName(Bootstrap::TABLE_RECENT);
            $drop_table_setting_query = "DROP TABLE IF EXISTS " . $table_setting;
            $drop_table_map_query = "DROP TABLE IF EXISTS " . $table_map;
            $drop_table_notice_query = "DROP TABLE IF EXISTS " . $table_notice;
            $drop_table_recent_query = "DROP TABLE IF EXISTS " . $table_recent;
            $db->queryRaw($drop_table_setting_query);
            $db->queryRaw($drop_table_map_query);
            $db->queryRaw($drop_table_notice_query);
            $db->queryRaw($drop_table_recent_query);
            $isUninstall = true;
            $version_file = _MODULE_APP_DIR_ . DS . 'setup' . DS . 'version';
            @unlink($version_file);
        }

        return $this;
    }
}
