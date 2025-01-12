<?php
/**
 * Copyright (c) Enalean, 2024 - Present. All Rights Reserved.
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

namespace Tuleap\PdfTemplate\Admin;

use Tuleap\Export\Pdf\Template\PdfTemplate;

/**
 * @psalm-immutable
 */
final readonly class PdfTemplatePresenter
{
    public string $update_url;

    private function __construct(
        public string $id,
        public string $label,
        public string $description,
        public string $style,
    ) {
        $this->update_url = DisplayPdfTemplateUpdateFormController::ROUTE . '/' . urlencode($id);
    }

    public static function fromPdfTemplate(PdfTemplate $template): self
    {
        return new self(
            $template->identifier->toString(),
            $template->label,
            $template->description,
            $template->style,
        );
    }

    public static function forCreation(): self
    {
        return new self(
            '',
            '',
            '',
            file_get_contents(__DIR__ . '/../Default/pdf-template-default.css'),
        );
    }
}
