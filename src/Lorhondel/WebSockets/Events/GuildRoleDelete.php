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

class GuildRoleDelete extends Event
{
    /**
     * @inheritdoc
     */
    public function handle(Deferred &$deferred, $data): void
    {
        if ($guild = $this->lorhondel->guilds->get('id', $data->guild_id)) {
            $role = $guild->roles->pull($data->role_id);
            $this->lorhondel->guilds->push($guild);

            $deferred->resolve($role);
        } else {
            $deferred->resolve($data);
        }
    }
}
