<?php
/**
 * Copyright (c) Enalean, 2022-Present. All Rights Reserved.
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

namespace Tuleap\Tracker\Artifact\ChangesetValue\ArtifactLink;

use Tuleap\Tracker\Artifact\Artifact;
use Tuleap\Tracker\Artifact\Link\ForwardLinkProxy;
use Tuleap\Tracker\Artifact\RetrieveArtifact;

/**
 * I hold a reverse link retrieved from database storage
 * @psalm-immutable
 */
final class StoredReverseLink implements ReverseLink
{
    private function __construct(private int $id, private string $type)
    {
    }

    public static function fromRow(RetrieveArtifact $artifact_retriever, \PFUser $user, StoredLinkRow $row): ?self
    {
        $artifact = $artifact_retriever->getArtifactById($row->getId());
        if (! $artifact || ! $artifact->userCanView($user)) {
            return null;
        }
        return new self($artifact->getId(), $row->getType() ?? \Tracker_FormElement_Field_ArtifactLink::NO_TYPE);
    }

    public function getSourceArtifactId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function convertIntoForwardLinkCollection(Artifact $artifact): CollectionOfForwardLinks
    {
        return new CollectionOfForwardLinks([ForwardLinkProxy::buildFromData($artifact->getId(), $this->type)]);
    }
}
