<?php
/**
 * Copyright (c) Enalean, 2023 - present. All Rights Reserved.
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

namespace Tuleap\PullRequest\Tests\Builders;

use Tuleap\PullRequest\Comment\Comment;
use Tuleap\PullRequest\PullRequest\Timeline\TimelineComment;

final class CommentTestBuilder
{
    private int $id              = 12;
    private int $pull_request_id = 54;
    private int $user_id         = 105;
    private int $post_date       = 1695212990;
    private int $parent_id       = 0;
    private string $color        = "";

    private function __construct(
        private readonly string $content,
        private readonly string $format,
    ) {
    }

    public static function aMarkdownComment(string $content): self
    {
        return new self(
            $content,
            TimelineComment::FORMAT_MARKDOWN
        );
    }

    public static function aTextComment(string $content): self
    {
        return new self(
            $content,
            TimelineComment::FORMAT_TEXT
        );
    }

    public function build(): Comment
    {
        return new Comment(
            $this->id,
            $this->pull_request_id,
            $this->user_id,
            $this->post_date,
            $this->content,
            $this->parent_id,
            $this->color,
            $this->format
        );
    }
}
