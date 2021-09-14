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
use Lorhondel\WebSockets\Event;

class ThreadDelete extends Event
{
    public function handle(Deferred &$deferred, $data)
    {
        $thread = null;

        if ($guild = $this->lorhondel->guilds->get('id', $data->guild_id)) {
            if ($parent = $guild->channels->get('id', $data->parent_id)) {
                $thread = $parent->threads->pull($data->id);
            }
        }

        $deferred->resolve($thread);
    }
}
