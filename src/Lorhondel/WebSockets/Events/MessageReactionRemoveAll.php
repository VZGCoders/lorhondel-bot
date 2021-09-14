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

use Lorhondel\Parts\WebSockets\MessageReaction;
use Lorhondel\WebSockets\Event;
use Lorhondel\Helpers\Deferred;

class MessageReactionRemoveAll extends Event
{
    /**
     * @inheritdoc
     */
    public function handle(Deferred &$deferred, $data): void
    {
        $reaction = new MessageReaction($this->lorhondel, (array) $data, true);

        if ($channel = $reaction->channel) {
            if ($message = $channel->messages->offsetGet($reaction->message_id)) {
                $message->reactions->clear();
            }
        }

        $deferred->resolve($reaction);
    }
}
