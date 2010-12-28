<?php

/**
 *
 */
class b201012240808_add_table_frs_log extends ForgeUpgrade_Bucket {

    public function description() {
        return <<<EOT
Add the table frs_log to store actions on FRS elements.
EOT;
    }

    public function preUp() {
        $this->db = $this->getApi('ForgeUpgrade_Bucket_Db');
    }

    public function up() {
        $sql = "CREATE TABLE frs_log (
                  time int(11) NOT NULL default 0,
                  user_id int(11) NOT NULL default 0,
                  project_id int(11) NOT NULL default 0,
                  item_id int(11) NOT NULL,
                  action_id int(11) NOT NULL,
                  KEY idx_frs_log_time (time),
                  KEY idx_frs_log_project_id (project_id),
                  KEY idx_frs_log_user_id (user_id),
                  KEY idx_frs_log_action_id (action_id)
                );";
        $this->db->createTable('frs_log', $sql);
    }

    public function postUp() {
        if (!$this->db->tableNameExists('frs_log')) {
            throw new ForgeUpgrade_Bucket_Exception_UpgradeNotCompleteException('frs_log table is missing');
        }
    }
    
}

?>
