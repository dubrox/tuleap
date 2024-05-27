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

declare(strict_types=1);

namespace Tuleap\CrossTracker\Report\Query\Advanced;

use PFUser;
use Tracker;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\VisitorParameters;
use Tuleap\Tracker\Report\Query\Advanced\InvalidSelectablesCollection;

final readonly class InvalidSelectableCollectorParameters implements VisitorParameters
{
    /**
     * @param Tracker[] $trackers
     */
    public function __construct(
        public InvalidSelectablesCollection $invalid_selectables_collection,
        public array $trackers,
        public PFUser $user,
    ) {
    }

    /**
     * @return int[]
     */
    public function getTrackersIds(): array
    {
        return array_map(
            function (Tracker $tracker) {
                return $tracker->getId();
            },
            $this->trackers
        );
    }
}
