<?php

/*
 * This file is a part of the LorhondelPHP project.
 *
 * Copyright (c) 2015-present David Cole <david.cole1340@gmail.com>
 *
 * This file is subject to the MIT license that is bundled
 * with this source code in the LICENSE.md file.
 */

namespace Lorhondel\WebSockets\Events;

use Lorhondel\Parts\Channel\Message;
use Lorhondel\WebSockets\Event;
use Lorhondel\Helpers\Deferred;
use Lorhondel\Parts\Channel\Channel;

class MessageCreate extends Event
{
    /**
     * @inheritdoc
     */
    public function handle(Deferred &$deferred, $data): void
    {
        /** @var Message */
        $message = $this->factory->create(Message::class, $data, true);

        // assume it is a private channel
        if ($message->channel === null) {
            $channel = $this->factory->create(Channel::class, [
                'id' => $message->channel_id,
                'type' => Channel::TYPE_DM,
                'last_message_id' => $message->id,
                'recipients' => [$message->author],
            ], true);

            $this->lorhondel->private_channels->push($channel);
        }

        if ($this->lorhondel->options['storeMessages']) {
            if ($channel = $message->channel) {
                $channel->messages->push($message);
            }
        }

        $deferred->resolve($message);
    }
}
