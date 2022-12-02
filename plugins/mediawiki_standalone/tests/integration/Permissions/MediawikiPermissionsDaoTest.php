<?php
/**
 * Copyright (c) Enalean, 2022 - Present. All Rights Reserved.
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

declare(strict_types=1);

namespace Tuleap\MediawikiStandalone\Permissions;

use Tuleap\DB\DBFactory;
use Tuleap\Test\Builders\ProjectTestBuilder;
use Tuleap\Test\Builders\ProjectUGroupTestBuilder;
use Tuleap\Test\PHPUnit\TestCase;

final class MediawikiPermissionsDaoTest extends TestCase
{
    private const PROJECT_ID     = 1001;
    private const DEVELOPERS_ID  = 101;
    private const QA_ID          = 102;
    private const INTEGRATORS_ID = 103;

    private MediawikiPermissionsDao $dao;
    private \Project $project;
    private \ProjectUGroup $anonymous;
    private \ProjectUGroup $registered;
    private \ProjectUGroup $authenticated;
    private \ProjectUGroup $project_members;
    private \ProjectUGroup $developers_ugroup;
    private \ProjectUGroup $qa_ugroup;
    private \ProjectUGroup $integrators_ugroup;

    protected function setUp(): void
    {
        $this->dao = new MediawikiPermissionsDao();

        $this->project            = ProjectTestBuilder::aProject()->withId(self::PROJECT_ID)->build();
        $this->anonymous          = ProjectUGroupTestBuilder::buildAnonymous();
        $this->registered         = ProjectUGroupTestBuilder::buildRegistered();
        $this->authenticated      = ProjectUGroupTestBuilder::buildAuthenticated();
        $this->project_members    = ProjectUGroupTestBuilder::buildProjectMembers();
        $this->developers_ugroup  = ProjectUGroupTestBuilder::aCustomUserGroup(self::DEVELOPERS_ID)->build();
        $this->qa_ugroup          = ProjectUGroupTestBuilder::aCustomUserGroup(self::QA_ID)->build();
        $this->integrators_ugroup = ProjectUGroupTestBuilder::aCustomUserGroup(self::INTEGRATORS_ID)->build();
    }

    protected function tearDown(): void
    {
        DBFactory::getMainTuleapDBConnection()
            ->getDB()
            ->run('DELETE FROM plugin_mediawiki_standalone_permissions');
    }

    public function testSaveAndGetPermissions(): void
    {
        self::assertEquals(
            [],
            $this->dao->searchByProjectAndPermission($this->project, new PermissionRead())
        );
        self::assertEquals(
            [],
            $this->dao->searchByProjectAndPermission($this->project, new PermissionWrite())
        );

        $this->dao->saveProjectPermissions(
            $this->project,
            [$this->project_members, $this->developers_ugroup],
            [$this->project_members, $this->qa_ugroup],
        );

        self::assertEquals(
            [\ProjectUGroup::PROJECT_MEMBERS, self::DEVELOPERS_ID],
            $this->getReadersUgroupIds($this->project)
        );

        self::assertEquals(
            [\ProjectUGroup::PROJECT_MEMBERS, self::QA_ID],
            $this->getWritersUgroupIds($this->project)
        );
    }

    public function testDuplicatePermissions(): void
    {
        $this->dao->saveProjectPermissions(
            $this->project,
            [$this->project_members, $this->developers_ugroup],
            [$this->project_members, $this->integrators_ugroup],
        );

        $another_project = ProjectTestBuilder::aProject()->withId(1002)->build();
        $this->dao->saveProjectPermissions(
            $another_project,
            [$this->qa_ugroup],
            [$this->qa_ugroup],
        );

        $just_created_project = ProjectTestBuilder::aProject()->withId(1003)->build();
        $this->dao->duplicateProjectPermissions($this->project, $just_created_project, [
            self::DEVELOPERS_ID  => 201,
            self::INTEGRATORS_ID => 203,
        ]);

        self::assertEquals(
            [\ProjectUGroup::PROJECT_MEMBERS, 201],
            $this->getReadersUgroupIds($just_created_project)
        );

        self::assertEquals(
            [\ProjectUGroup::PROJECT_MEMBERS, 203],
            $this->getWritersUgroupIds($just_created_project)
        );
    }

    public function testUpdateAllAnonymousAccessToRegistered(): void
    {
        $this->dao->saveProjectPermissions(
            $this->project,
            [$this->registered],
            [$this->registered],
        );

        $another_project = ProjectTestBuilder::aProject()->withId(1002)->build();
        $this->dao->saveProjectPermissions(
            $another_project,
            [$this->anonymous],
            [$this->registered],
        );

        $yet_another_project = ProjectTestBuilder::aProject()->withId(1003)->build();
        $this->dao->saveProjectPermissions(
            $yet_another_project,
            [$this->project_members, $this->developers_ugroup],
            [$this->project_members, $this->developers_ugroup],
        );

        $this->dao->updateAllAnonymousAccessToRegistered();

        self::assertEquals(
            [\ProjectUGroup::REGISTERED],
            $this->getReadersUgroupIds($this->project)
        );
        self::assertEquals(
            [\ProjectUGroup::REGISTERED],
            $this->getReadersUgroupIds($another_project)
        );
        self::assertEquals(
            [\ProjectUGroup::PROJECT_MEMBERS, self::DEVELOPERS_ID],
            $this->getReadersUgroupIds($yet_another_project)
        );

        self::assertEquals(
            [\ProjectUGroup::REGISTERED],
            $this->getWritersUgroupIds($this->project)
        );
        self::assertEquals(
            [\ProjectUGroup::REGISTERED],
            $this->getWritersUgroupIds($another_project)
        );
        self::assertEquals(
            [\ProjectUGroup::PROJECT_MEMBERS, self::DEVELOPERS_ID],
            $this->getWritersUgroupIds($yet_another_project)
        );
    }

    public function testUpdateAllAuthenticatedAccessToRegistered(): void
    {
        $this->dao->saveProjectPermissions(
            $this->project,
            [$this->registered],
            [$this->registered],
        );

        $another_project = ProjectTestBuilder::aProject()->withId(1002)->build();
        $this->dao->saveProjectPermissions(
            $another_project,
            [$this->authenticated],
            [$this->authenticated],
        );

        $yet_another_project = ProjectTestBuilder::aProject()->withId(1003)->build();
        $this->dao->saveProjectPermissions(
            $yet_another_project,
            [$this->project_members, $this->developers_ugroup],
            [$this->project_members, $this->developers_ugroup],
        );

        $this->dao->updateAllAuthenticatedAccessToRegistered();

        self::assertEquals(
            [\ProjectUGroup::REGISTERED],
            $this->getReadersUgroupIds($this->project)
        );
        self::assertEquals(
            [\ProjectUGroup::REGISTERED],
            $this->getReadersUgroupIds($another_project)
        );
        self::assertEquals(
            [\ProjectUGroup::PROJECT_MEMBERS, self::DEVELOPERS_ID],
            $this->getReadersUgroupIds($yet_another_project)
        );

        self::assertEquals(
            [\ProjectUGroup::REGISTERED],
            $this->getWritersUgroupIds($this->project)
        );
        self::assertEquals(
            [\ProjectUGroup::REGISTERED],
            $this->getWritersUgroupIds($another_project)
        );
        self::assertEquals(
            [\ProjectUGroup::PROJECT_MEMBERS, self::DEVELOPERS_ID],
            $this->getWritersUgroupIds($yet_another_project)
        );
    }

    /**
     * @return int[]
     */
    private function getReadersUgroupIds(\Project $project): array
    {
        return array_column(
            $this->dao->searchByProjectAndPermission($project, new PermissionRead()),
            'ugroup_id'
        );
    }

    /**
     * @return int[]
     */
    private function getWritersUgroupIds(\Project $project): array
    {
        return array_column(
            $this->dao->searchByProjectAndPermission($project, new PermissionWrite()),
            'ugroup_id'
        );
    }
}
