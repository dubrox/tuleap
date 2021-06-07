<?php
/**
 * Copyright (c) Enalean, 2021-Present. All Rights Reserved.
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

namespace Tuleap\ProgramManagement\Domain\Program\Backlog\CreationCheck;

use Tuleap\ProgramManagement\Domain\Program\Plan\BuildProgram;
use Tuleap\ProgramManagement\Domain\Program\Plan\ProgramAccessException;
use Tuleap\ProgramManagement\Domain\Program\Plan\ProjectIsNotAProgramException;
use Tuleap\ProgramManagement\Domain\Program\ProgramIdentifier;
use Tuleap\ProgramManagement\Domain\ProgramTracker;
use Tuleap\Tracker\Artifact\CanSubmitNewArtifact;

final class CanSubmitNewArtifactHandler
{
    private BuildProgram $program_builder;
    private ProgramIncrementCreatorChecker $program_increment_creator_checker;

    public function __construct(
        BuildProgram $program_builder,
        ProgramIncrementCreatorChecker $program_increment_creator_checker
    ) {
        $this->program_builder                   = $program_builder;
        $this->program_increment_creator_checker = $program_increment_creator_checker;
    }

    public function handle(CanSubmitNewArtifact $event): void
    {
        $tracker      = $event->getTracker();
        $user         = $event->getUser();
        $tracker_data = new ProgramTracker($tracker);
        try {
            $program = ProgramIdentifier::fromId($this->program_builder, (int) $tracker->getGroupId(), $user);
        } catch (ProgramAccessException | ProjectIsNotAProgramException $e) {
            // Do not disable artifact submission. Keep it enabled
            return;
        }
        if (! $this->program_increment_creator_checker->canCreateAProgramIncrement($user, $tracker_data, $program)) {
            $event->disableArtifactSubmission();
        }
    }
}
