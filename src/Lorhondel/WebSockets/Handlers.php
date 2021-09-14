<?php

/*
 * This file is a part of the LorhondelPHP project.
 *
 * Copyright (c) 2015-present David Cole <david.cole1340@gmail.com>
 *
 * This file is subject to the MIT license that is bundled
 * with this source code in the LICENSE.md file.
 */

namespace Lorhondel\WebSockets;

/**
 * This class contains all the handlers for the individual WebSocket events.
 */
class Handlers
{
    /**
     * An array of handlers.
     *
     * @var array Array of handlers.
     */
    protected $handlers = [];

    /**
     * Constructs the list of handlers.
     */
    public function __construct()
    {
        // General
        $this->addHandler(Event::PRESENCE_UPDATE, \Lorhondel\WebSockets\Events\PresenceUpdate::class);
        $this->addHandler(Event::TYPING_START, \Lorhondel\WebSockets\Events\TypingStart::class);
        $this->addHandler(Event::VOICE_STATE_UPDATE, \Lorhondel\WebSockets\Events\VoiceStateUpdate::class);
        $this->addHandler(Event::VOICE_SERVER_UPDATE, \Lorhondel\WebSockets\Events\VoiceServerUpdate::class);
        $this->addHandler(Event::INTERACTION_CREATE, \Lorhondel\WebSockets\Events\InteractionCreate::class);

        // Guild Event handlers
        $this->addHandler(Event::GUILD_CREATE, \Lorhondel\WebSockets\Events\GuildCreate::class);
        $this->addHandler(Event::GUILD_DELETE, \Lorhondel\WebSockets\Events\GuildDelete::class);
        $this->addHandler(Event::GUILD_UPDATE, \Lorhondel\WebSockets\Events\GuildUpdate::class);
        $this->addHandler(Event::GUILD_INTEGRATIONS_UPDATE, \Lorhondel\WebSockets\Events\GuildIntegrationsUpdate::class);

        // Invite handlers
        $this->addHandler(Event::INVITE_CREATE, \Lorhondel\WebSockets\Events\InviteCreate::class);
        $this->addHandler(Event::INVITE_DELETE, \Lorhondel\WebSockets\Events\InviteDelete::class);

        // Channel Event handlers
        $this->addHandler(Event::CHANNEL_CREATE, \Lorhondel\WebSockets\Events\ChannelCreate::class);
        $this->addHandler(Event::CHANNEL_UPDATE, \Lorhondel\WebSockets\Events\ChannelUpdate::class);
        $this->addHandler(Event::CHANNEL_DELETE, \Lorhondel\WebSockets\Events\ChannelDelete::class);
        $this->addHandler(Event::CHANNEL_PINS_UPDATE, \Lorhondel\WebSockets\Events\ChannelPinsUpdate::class);

        // Ban Event handlers
        $this->addHandler(Event::GUILD_BAN_ADD, \Lorhondel\WebSockets\Events\GuildBanAdd::class);
        $this->addHandler(Event::GUILD_BAN_REMOVE, \Lorhondel\WebSockets\Events\GuildBanRemove::class);

        // Message handlers
        $this->addHandler(Event::MESSAGE_CREATE, \Lorhondel\WebSockets\Events\MessageCreate::class, ['message']);
        $this->addHandler(Event::MESSAGE_DELETE, \Lorhondel\WebSockets\Events\MessageDelete::class);
        $this->addHandler(Event::MESSAGE_DELETE_BULK, \Lorhondel\WebSockets\Events\MessageDeleteBulk::class);
        $this->addHandler(Event::MESSAGE_UPDATE, \Lorhondel\WebSockets\Events\MessageUpdate::class);
        $this->addHandler(Event::MESSAGE_REACTION_ADD, \Lorhondel\WebSockets\Events\MessageReactionAdd::class);
        $this->addHandler(Event::MESSAGE_REACTION_REMOVE, \Lorhondel\WebSockets\Events\MessageReactionRemove::class);
        $this->addHandler(Event::MESSAGE_REACTION_REMOVE_ALL, \Lorhondel\WebSockets\Events\MessageReactionRemoveAll::class);
        $this->addHandler(Event::MESSAGE_REACTION_REMOVE_EMOJI, \Lorhondel\WebSockets\Events\MessageReactionRemoveEmoji::class);

        // New Member Event handlers
        $this->addHandler(Event::GUILD_MEMBER_ADD, \Lorhondel\WebSockets\Events\GuildMemberAdd::class);
        $this->addHandler(Event::GUILD_MEMBER_REMOVE, \Lorhondel\WebSockets\Events\GuildMemberRemove::class);
        $this->addHandler(Event::GUILD_MEMBER_UPDATE, \Lorhondel\WebSockets\Events\GuildMemberUpdate::class);

        // New Role Event handlers
        $this->addHandler(Event::GUILD_ROLE_CREATE, \Lorhondel\WebSockets\Events\GuildRoleCreate::class);
        $this->addHandler(Event::GUILD_ROLE_DELETE, \Lorhondel\WebSockets\Events\GuildRoleDelete::class);
        $this->addHandler(Event::GUILD_ROLE_UPDATE, \Lorhondel\WebSockets\Events\GuildRoleUpdate::class);

        // Thread events
        $this->addHandler(Event::THREAD_CREATE, \Lorhondel\WebSockets\Events\ThreadCreate::class);
        $this->addHandler(Event::THREAD_UPDATE, \Lorhondel\WebSockets\Events\ThreadUpdate::class);
        $this->addHandler(Event::THREAD_DELETE, \Lorhondel\WebSockets\Events\ThreadDelete::class);
        $this->addHandler(Event::THREAD_LIST_SYNC, \Lorhondel\WebSockets\Events\ThreadListSync::class);
        $this->addHandler(Event::THREAD_MEMBER_UPDATE, \Lorhondel\WebSockets\Events\ThreadMemberUpdate::class);
        $this->addHandler(Event::THREAD_MEMBERS_UPDATE, \Lorhondel\WebSockets\Events\ThreadMembersUpdate::class);
    }

    /**
     * Adds a handler to the list.
     *
     * @param string $event        The WebSocket event name.
     * @param string $classname    The Event class name.
     * @param array  $alternatives Alternative event names for the handler.
     */
    public function addHandler(string $event, string $classname, array $alternatives = []): void
    {
        $this->handlers[$event] = [
            'class' => $classname,
            'alternatives' => $alternatives,
        ];
    }

    /**
     * Returns a handler.
     *
     * @param string $event The WebSocket event name.
     *
     * @return array|null The Event class name or null;
     */
    public function getHandler(string $event): ?array
    {
        if (isset($this->handlers[$event])) {
            return $this->handlers[$event];
        }

        return null;
    }

    /**
     * Returns the handlers array.
     *
     * @return array Array of handlers.
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * Returns the handlers.
     *
     * @return array Array of handler events.
     */
    public function getHandlerKeys(): array
    {
        return array_keys($this->handlers);
    }

    /**
     * Removes a handler.
     *
     * @param string $event The event handler to remove.
     */
    public function removeHandler(string $event): void
    {
        unset($this->handlers[$event]);
    }
}
