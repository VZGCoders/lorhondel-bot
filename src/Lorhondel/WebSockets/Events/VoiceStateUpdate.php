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

use Lorhondel\Parts\WebSockets\VoiceStateUpdate as VoiceStateUpdatePart;
use Lorhondel\WebSockets\Event;
use Lorhondel\Helpers\Deferred;

class VoiceStateUpdate extends Event
{
    /**
     * @inheritdoc
     */
    public function handle(Deferred &$deferred, $data): void
    {
        $state = $this->factory->create(VoiceStateUpdatePart::class, $data, true);
        $old_state = null;

        if ($state->guild) {
            $guild = $state->guild;

            foreach ($guild->channels as $channel) {
                if (! $channel->allowVoice()) {
                    continue;
                }

                // Remove old member states
                if ($channel->members->has($state->discord_id)) {
                    $old_state = $channel->members->offsetGet($state->discord_id);
                    $channel->members->offsetUnset($state->discord_id);
                }

                // Add member state to new channel
                if ($channel->id == $state->channel_id) {
                    $channel->members->offsetSet($state->discord_id, $state);
                }

                $guild->channels->offsetSet($channel->id, $channel);
            }

            $this->lorhondel->guilds->offsetSet($state->guild->id, $state->guild);
        }

        $deferred->resolve([$state, $old_state]);
    }
}
