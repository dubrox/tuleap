<?php
/**
 * Copyright (c) Enalean, 2018. All Rights Reserved.
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

namespace Tuleap\Docman\Upload;

final class DocumentUploadCleaner
{
    /**
     * @var DocumentUploadPathAllocator
     */
    private $path_allocator;
    /**
     * @var DocumentOngoingUploadDAO
     */
    private $dao;

    public function __construct(DocumentUploadPathAllocator $path_allocator, DocumentOngoingUploadDAO $dao)
    {
        $this->path_allocator = $path_allocator;
        $this->dao            = $dao;
    }

    public function deleteDanglingDocumentToUpload(\DateTimeImmutable $current_time)
    {
        $this->dao->deleteUnusableDocuments($current_time->getTimestamp());

        $document_being_uploaded_item_ids   = array_flip($this->dao->searchDocumentOngoingUploadItemIDs());
        $document_being_uploaded_filesystem = $this->path_allocator->getCurrentlyUsedAllocatedPathsPerExpectedItemIDs();
        foreach ($document_being_uploaded_filesystem as $expected_item_id => $path) {
            if (! isset($document_being_uploaded_item_ids[(int) $expected_item_id])) {
                unlink($path);
            }
        }
    }
}
