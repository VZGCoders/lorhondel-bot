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

use Lorhondel\Parts\Guild\Ban;
use Lorhondel\WebSockets\Event;
use Lorhondel\Helpers\Deferred;

class GuildBanAdd extends Event
{
    /**
     * @inheritdoc
     */
    public function handle(Deferred &$deferred, $data): void
    {
        $ban = $this->factory->create(Ban::class, $data, true);

        if ($guild = $ban->guild) {
            $guild->bans->push($ban);
            $this->lorhondel->guilds->push($guild);
        }

        $deferred->resolve($ban);
    }
}
