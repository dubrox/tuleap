<?php
/**
 * Copyright (c) Enalean, 2016-2017. All Rights Reserved.
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

namespace Tuleap\BotMattermost\SenderServices;

require_once dirname(__FILE__).'/../../bootstrap.php';

use TuleapTestCase;

class SenderTest extends TuleapTestCase
{

    private $encoder_message;
    private $botMattermost_client;
    private $logger;

    public function setUp()
    {
        parent::setUp();
        $this->encoder_message      = mock('Tuleap\\BotMattermost\\SenderServices\\EncoderMessage');
        $this->botMattermost_client = mock('Tuleap\\BotMattermost\\SenderServices\\ClientBotMattermost');
        $this->logger               = mock('Tuleap\\BotMattermost\\BotMattermostLogger');

        $this->sender = new Sender(
            $this->encoder_message,
            $this->botMattermost_client,
            $this->logger
        );
    }

    public function itVerifiedThatPushNotificationForEachChannels()
    {
        $message  = new Message();
        $bot      = mock('Tuleap\\BotMattermost\\Bot\\Bot');
        $channels = array('channel1', 'channel2');
        stub($bot)->getWebhookUrl()->returns('https:\/\/webhook_url.com');
        $message->setText('{"username":"toto","channel":"channel","icon_url":"https:\/\/avatar_url.com","text":"text"}');

        $this->sender->pushNotification($bot, $message, $channels);
        $this->botMattermost_client->expectCallCount('sendMessage', 2);
    }
}