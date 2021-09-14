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

use Lorhondel\Helpers\Deferred;
use Lorhondel\Parts\Thread\Member;
use Lorhondel\WebSockets\Event;

class ThreadMemberUpdate extends Event
{
    public function handle(Deferred &$deferred, $data)
    {
        $member = $this->factory->create(Member::class, $data, true);
        $guild = $this->lorhondel->guilds->get('id', $data->guild_id);

        foreach ($guild->channels as $channel) {
            if ($thread = $channel->threads->get('id', $data->id)) {
                $thread->members->push($member);
                break;
            }
        }

        $deferred->resolve($member);
    }
}
