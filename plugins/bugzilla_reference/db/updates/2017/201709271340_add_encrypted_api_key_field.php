<?php
/**
 * Copyright (c) Enalean, 2017 - Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

class b201709271340_add_encrypted_api_key_field extends \Tuleap\ForgeUpgrade\Bucket
{
    public function description()
    {
        return 'Add a field in the Bugzilla reference table to store the encrypted API key';
    }

    public function preUp()
    {
        $this->db = $this->getApi('ForgeUpgrade_Bucket_Db');
    }

    public function up()
    {
        $sql = 'ALTER TABLE plugin_bugzilla_reference ADD COLUMN encrypted_api_key BLOB NOT NULL';
        $res = $this->db->dbh->exec($sql);
        if ($res === false) {
            throw new \Tuleap\ForgeUpgrade\Bucket\BucketUpgradeNotCompleteException(
                'An error occurred while adding encrypted_api_key column in plugin_bugzilla_reference table: ' . implode(', ', $this->db->dbh->errorInfo())
            );
        }
    }

    public function postUp()
    {
        if (! $this->db->columnNameExists('plugin_bugzilla_reference', 'encrypted_api_key')) {
            throw new \Tuleap\ForgeUpgrade\Bucket\BucketUpgradeNotCompleteException(
                'An error occurred while adding encrypted_api_key column in plugin_bugzilla_reference table'
            );
        }
    }
}
