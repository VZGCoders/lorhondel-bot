<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel;

use Lorhondel\Exceptions\IntentException;
use Lorhondel\Factory\Factory;
use Lorhondel\Http;
use Lorhondel\HttpServer;
use Lorhondel\WebSockets\Event;
use Lorhondel\WebSockets\Handlers;
use Lorhondel\WebSockets\Intents;
use Lorhondel\WebSockets\Op;
use Lorhondel\Helpers\Deferred;
use Lorhondel\Drivers\React;
use Lorhondel\Endpoint;
use Lorhondel\Parts\Player\Client;

use Monolog\Handler\StreamHandler;
use Monolog\Logger as Monolog;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\Message;
use React\EventLoop\Factory as LoopFactory;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use Evenement\EventEmitterTrait;
use Psr\Log\LoggerInterface;
use React\Promise\ExtendedPromiseInterface;
use React\Promise\PromiseInterface;
use React\Socket\Connector as SocketConnector;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Lorhondel
{
	use EventEmitterTrait;

    /**
     * The gateway version the client uses.
     *
     * @var int Gateway version.
     */
    public const GATEWAY_VERSION = 1;

    /**
     * The client version.
     *
     * @var string Version.
     */
    public const VERSION = 'v1.0.0';
	
	/**
     * The logger.
     *
     * @var LoggerInterface Logger.
     */
    protected $logger;
	
	/**
     * An array of options passed to the client.
     *
     * @var array Options.
     */
    protected $options;
	
	/**
     * The authentication token.
     *
     * @var string Token.
     */
    protected $token;
	
	/**
     * The ReactPHP event loop.
     *
     * @var LoopInterface Event loop.
     */
    public $loop;
	
	/**
     * The WebSocket client factory.
     *
     * @var Connector Factory.
     */
    protected $wsFactory;

    /**
     * The WebSocket instance.
     *
     * @var WebSocket Instance.
     */
    protected $ws;

    /**
     * The event handlers.
     *
     * @var Handlers Handlers.
     */
    protected $handlers;

    /**
     * The packet sequence that the client is up to.
     *
     * @var int Sequence.
     */
    protected $seq;

    /**
     * Whether the client is currently reconnecting.
     *
     * @var bool Reconnecting.
     */
    protected $reconnecting = false;

    /**
     * Whether the client is connected to the gateway.
     *
     * @var bool Connected.
     */
    protected $connected = false;

    /**
     * Whether the client is closing.
     *
     * @var bool Closing.
     */
    protected $closing = false;

    /**
     * The session ID of the current session.
     *
     * @var string Session ID.
     */
    protected $sessionId;
	
	/**
     * An array of large guilds that need to be requested for
     * members.
     *
     * @var array Large guilds.
     */
    protected $largeGuilds = [];

    /**
     * An array of large guilds that have been requested for members.
     *
     * @var array Large guilds.
     */
    protected $largeSent = [];

    /**
     * An array of unparsed packets.
     *
     * @var array Unparsed packets.
     */
    protected $unparsedPackets = [];

    /**
     * How many times the client has reconnected.
     *
     * @var int Reconnect count.
     */
    protected $reconnectCount = 0;

    /**
     * The heartbeat interval.
     *
     * @var int Heartbeat interval.
     */
    protected $heartbeatInterval;

    /**
     * The timer that sends the heartbeat packet.
     *
     * @var TimerInterface Timer.
     */
    protected $heartbeatTimer;

    /**
     * The timer that resends the heartbeat packet if
     * a HEARTBEAT_ACK packet is not received in 5 seconds.
     *
     * @var TimerInterface Timer.
     */
    protected $heartbeatAckTimer;

    /**
     * The time that the last heartbeat packet was sent.
     *
     * @var int Epoch time.
     */
    protected $heartbeatTime;

    /**
     * Whether `ready` has been emitted.
     *
     * @var bool Emitted.
     */
    protected $emittedReady = false;

    /**
     * The gateway URL that the WebSocket client will connect to.
     *
     * @var string Gateway URL.
     */
    protected $gateway;

    /**
     * What encoding the client will use, either `json` or `etf`.
     *
     * @var string Encoding.
     */
    protected $encoding = 'json';

    /**
     * Tracks the number of payloads the client
     * has sent in the past 60 seconds.
     *
     * @var int
     */
    protected $payloadCount = 0;

    /**
     * Payload count reset timer.
     *
     * @var TimerInterface
     */
    protected $payloadTimer;

    /**
     * The HTTP client.
     *
     * @var Http Client.
     */
    protected $http;
	
	/**
     * The HTTP server.
     *
     * @var Http Server.
     */
	protected $httpserver;
	
	/**
     * The part/repository factory.
     *
     * @var Factory Part factory.
     */
    protected $factory;
	
    /**
     * The Client class.
     *
     * @var Client Lorhondel client.
     */
    protected $client;
	
	public $discord;
	public $browser;
	
	public $server;
	
	public $command_symbol;
	
	protected $verbose = true;
	
    /**
     * Creates a Lorhondel client instance.
     *
     * @param  array           $options Array of options.
     * @throws IntentException
     */
	public function __construct(array $options = [])
    {
		if (php_sapi_name() !== 'cli') {
            trigger_error('Lorhondel will not run on a webserver. Please use PHP CLI to run a Lorhondel bot.', E_USER_ERROR);
        }
		
		$options = $this->resolveOptions($options);
		
		$this->options = $options;
		$this->token = $options['token'];
		$this->loop = $options['loop'];
		$this->browser = $options['browser'];
		if ($options['discord'] || $options['discord_options']) {
			if ($options['discord']) $this->discord = $options['discord'];
			elseif ($options['discord_options']) $this->discord = new \Discord\Discord($options['discord_options']);
		} else echo '[LORHONDEL-WARNING] No discord set!' . PHP_EOL;
		
		$connector = new SocketConnector($this->loop, $options['socket_options']);
        $this->wsFactory = new Connector($this->loop, $connector);
		$this->handlers = new Handlers();
		
		 foreach ($options['disabledEvents'] as $event) {
            $this->handlers->removeHandler($event);
        }
		
		$function = function () use (&$function) {
            $this->emittedReady = true;
            $this->removeListener('ready', $function);
        };

        $this->on('ready', $function);

        $this->http = new Http(
            'Bot '.$this->token,
            $this->loop,
            $this->options['logger'],
            new React($this->loop, $options['socket_options'])
        );
		
		if ($options['server']) {
			if ($options['socket']) $this->httpServer = new HttpServer($this, $options['socket']);
			else {
				$socket = new \React\Socket\Server(sprintf('%s:%s', '0.0.0.0', '27759'), $loop);
				if ($options['socket']) $this->httpServer = new HttpServer($this, $socket);
			}
		}
		
		$this->server = $options['server'];
		$this->command_symbol = $options['command_symbol'];

        $this->factory = new Factory($this, $this->http);
		$this->client = $this->factory->create(Client::class, [], true);

        $this->connectWs();
	}
	
	/**
     * Handles `RESUME` packets.
     *
     * @param object $data Packet data.
     */
    protected function handleResume(object $data): void
    {
        $this->logger->info('websocket reconnected to lorhondel');
        $this->emit('reconnected', [$this]);
    }
	
    /**
     * Handles `READY` packets.
     *
     * @param object $data Packet data.
     *
     * @return false|void
     * @throws \Exception
     */
    protected function handleReady(object $data)
    {
        $this->logger->debug('ready packet received');

        // If this is a reconnect we don't want to
        // reparse the READY packet as it would remove
        // all the data cached.
        if ($this->reconnecting) {
            $this->reconnecting = false;
            $this->logger->debug('websocket reconnected to lorhondel through identify');
            $this->emit('reconnected', [$this]);

            return;
        }

        $content = $data->d;
        $this->emit('trace', $data->d->_trace);
        $this->logger->debug('lorhondel trace received', ['trace' => $content->_trace]);

        // Setup the user account
        $this->client->fill((array) $content->user);
        $this->sessionId = $content->session_id;

        $this->logger->debug('client created and session id stored', ['session_id' => $content->session_id, 'user' => $this->client->user->getPublicAttributes()]);

        // Private Channels
        if ($this->options['pmChannels']) {
            foreach ($content->private_channels as $channel) {
                $channelPart = $this->factory->create(Channel::class, $channel, true);
                $this->private_channels->push($channelPart);
            }

            $this->logger->info('stored private channels', ['count' => $this->private_channels->count()]);
        } else {
            $this->logger->info('did not parse private channels');
        }

        // Guilds
        $event = new GuildCreate(
            $this->http,
            $this->factory,
            $this
        );

        $unavailable = [];

        foreach ($content->guilds as $guild) {
            $deferred = new Deferred();

            $deferred->promise()->done(null, function ($d) use (&$unavailable) {
                list($status, $data) = $d;

                if ($status == 'unavailable') {
                    $unavailable[$data] = $data;
                }
            });

            $event->handle($deferred, $guild);
        }

        $this->logger->info('stored guilds', ['count' => $this->guilds->count(), 'unavailable' => count($unavailable)]);

        if (count($unavailable) < 1) {
            return $this->ready();
        }

        // Emit ready after 60 seconds
        $this->loop->addTimer(60, function () {
            $this->ready();
        });

        $function = function ($guild) use (&$function, &$unavailable) {
            $this->logger->debug('guild available', ['guild' => $guild->id, 'unavailable' => count($unavailable)]);
            if (array_key_exists($guild->id, $unavailable)) {
                unset($unavailable[$guild->id]);
            }

            // todo setup timer to continue after x amount of time
            if (count($unavailable) < 1) {
                $this->logger->info('all guilds are now available', ['count' => $this->guilds->count()]);
                $this->removeListener(Event::GUILD_CREATE, $function);

                $this->setupChunking();
            }
        };

        $this->on(Event::GUILD_CREATE, $function);
    }
	
    /**
     * Handles `GUILD_MEMBERS_CHUNK` packets.
     *
     * @param  object     $data Packet data.
     * @throws \Exception
     */
    protected function handleGuildMembersChunk(object $data): void
    {
        $guild = $this->guilds->offsetGet($data->d->guild_id);
        $members = $data->d->members;

        $this->logger->debug('received guild member chunk', ['guild_id' => $guild->id, 'guild_name' => $guild->name, 'chunk_count' => count($members), 'member_collection' => $guild->members->count(), 'member_count' => $guild->member_count]);

        $count = 0;
        $skipped = 0;
        foreach ($members as $member) {
            if ($guild->members->has($member->user->id)) {
                ++$skipped;
                continue;
            }

            $member = (array) $member;
            $member['guild_id'] = $guild->id;
            $member['status'] = 'offline';

            if (! $this->users->has($member['user']->id)) {
                $userPart = $this->factory->create(User::class, $member['user'], true);
                $this->users->offsetSet($userPart->id, $userPart);
            }

            $memberPart = $this->factory->create(Member::class, $member, true);
            $guild->members->offsetSet($memberPart->id, $memberPart);

            ++$count;
        }

        $this->logger->debug('parsed '.$count.' members (skipped '.$skipped.')', ['repository_count' => $guild->members->count(), 'actual_count' => $guild->member_count]);

        if ($guild->members->count() >= $guild->member_count) {
            $this->largeSent = array_diff($this->largeSent, [$guild->id]);

            $this->logger->debug('all users have been loaded', ['guild' => $guild->id, 'member_collection' => $guild->members->count(), 'member_count' => $guild->member_count]);
            $this->guilds->offsetSet($guild->id, $guild);
        }

        if (count($this->largeSent) < 1) {
            $this->ready();
        }
    }
	
	    /**
     * Handles WebSocket connections received by the client.
     *
     * @param WebSocket $ws WebSocket client.
     */
    public function handleWsConnection(WebSocket $ws): void
    {
        $this->ws = $ws;
        $this->connected = true;

        $this->logger->info('websocket connection has been created');

        $this->payloadCount = 0;
        $this->payloadTimer = $this->loop->addPeriodicTimer(60, function () {
            $this->logger->debug('resetting payload count', ['count' => $this->payloadCount]);
            $this->payloadCount = 0;
            $this->emit('payload_count_reset');
        });

        $ws->on('message', [$this, 'handleWsMessage']);
        $ws->on('close', [$this, 'handleWsClose']);
        $ws->on('error', [$this, 'handleWsError']);
    }
	
    /**
     * Handles WebSocket messages received by the client.
     *
     * @param Message $message Message object.
     */
    public function handleWsMessage(Message $message): void
    {
        if ($message->isBinary()) {
            $data = zlib_decode($message->getPayload());
        } else {
            $data = $message->getPayload();
        }

        $data = json_decode($data);
        $this->emit('raw', [$data, $this]);

        if (isset($data->s)) {
            $this->seq = $data->s;
        }

        $op = [
            Op::OP_DISPATCH => 'handleDispatch',
            Op::OP_HEARTBEAT => 'handleHeartbeat',
            Op::OP_RECONNECT => 'handleReconnect',
            Op::OP_INVALID_SESSION => 'handleInvalidSession',
            Op::OP_HELLO => 'handleHello',
            Op::OP_HEARTBEAT_ACK => 'handleHeartbeatAck',
        ];

        if (isset($op[$data->op])) {
            $this->{$op[$data->op]}($data);
        }
    }
	
	    /**
     * Handles WebSocket closes received by the client.
     *
     * @param int    $op     The close code.
     * @param string $reason The reason the WebSocket closed.
     */
    public function handleWsClose(int $op, string $reason): void
    {
        $this->connected = false;

        if (! is_null($this->heartbeatTimer)) {
            $this->loop->cancelTimer($this->heartbeatTimer);
            $this->heartbeatTimer = null;
        }

        if (! is_null($this->heartbeatAckTimer)) {
            $this->loop->cancelTimer($this->heartbeatAckTimer);
            $this->heartbeatAckTimer = null;
        }

        if (! is_null($this->payloadTimer)) {
            $this->loop->cancelTimer($this->payloadTimer);
            $this->payloadTimer = null;
        }

        if ($this->closing) {
            return;
        }

        $this->logger->warning('websocket closed', ['op' => $op, 'reason' => $reason]);

        if (in_array($op, Op::getCriticalCloseCodes())) {
            $this->logger->error('not reconnecting - critical op code', ['op' => $op, 'reason' => $reason]);
        } else {
            $this->logger->warning('reconnecting in 2 seconds');

            $this->loop->addTimer(2, function () {
                ++$this->reconnectCount;
                $this->reconnecting = true;
                $this->logger->info('starting reconnect', ['reconnect_count' => $this->reconnectCount]);
                $this->connectWs();
            });
        }
    }
	
	    /**
     * Handles WebSocket errors received by the client.
     *
     * @param \Exception $e The error.
     */
    public function handleWsError(\Exception $e): void
    {
        // Pawl pls
        if (strpos($e->getMessage(), 'Tried to write to closed stream') !== false) {
            return;
        }

        $this->logger->error('websocket error', ['e' => $e->getMessage()]);
        $this->emit('error', [$e, $this]);
        $this->ws->close(Op::CLOSE_ABNORMAL, $e->getMessage());
    }
	
	
    /**
     * Handles cases when the WebSocket cannot be connected to.
     *
     * @param \Throwable $e
     */
    public function handleWsConnectionFailed(\Throwable $e)
    {
        $this->logger->error('failed to connect to websocket, retry in 5 seconds', ['e' => $e->getMessage()]);

        $this->loop->addTimer(5, function () {
            $this->connectWs();
        });
    }

    /**
     * Handles dispatch events received by the WebSocket.
     *
     * @param object $data Packet data.
     */
    protected function handleDispatch(object $data): void
    {
        $handlers = [
            Event::VOICE_SERVER_UPDATE => 'handleVoiceServerUpdate',
            Event::RESUMED => 'handleResume',
            Event::READY => 'handleReady',
            Event::GUILD_MEMBERS_CHUNK => 'handleGuildMembersChunk',
            Event::VOICE_STATE_UPDATE => 'handleVoiceStateUpdate',
        ];

        if (! is_null($hData = $this->handlers->getHandler($data->t))) {
            $handler = new $hData['class'](
                $this->http,
                $this->factory,
                $this
            );

            $deferred = new Deferred();
            $deferred->promise()->done(function ($d) use ($data, $hData) {
                if (is_array($d) && count($d) == 2) {
                    list($new, $old) = $d;
                } else {
                    $new = $d;
                    $old = null;
                }

                $this->emit($data->t, [$new, $this, $old]);

                foreach ($hData['alternatives'] as $alternative) {
                    $this->emit($alternative, [$d, $this]);
                }

                if ($data->t == Event::MESSAGE_CREATE && mentioned($this->client->user, $new)) {
                    $this->emit('mention', [$new, $this, $old]);
                }
            }, function ($e) use ($data) {
                $this->logger->warning('error while trying to handle dispatch packet', ['packet' => $data->t, 'error' => $e]);
            }, function ($d) use ($data) {
                $this->logger->warning('notified from event', ['data' => $d, 'packet' => $data->t]);
            });

            $parse = [
                Event::GUILD_CREATE,
            ];

            if (! $this->emittedReady && (array_search($data->t, $parse) === false)) {
                $this->unparsedPackets[] = function () use (&$handler, &$deferred, &$data) {
                    $handler->handle($deferred, $data->d);
                };
            } else {
                $handler->handle($deferred, $data->d);
            }
        } elseif (isset($handlers[$data->t])) {
            $this->{$handlers[$data->t]}($data);
        }
    }

    /**
     * Handles heartbeat packets received by the client.
     *
     * @param object $data Packet data.
     */
    protected function handleHeartbeat(object $data): void
    {
        $this->logger->debug('received heartbeat', ['seq' => $data->d]);

        $payload = [
            'op' => Op::OP_HEARTBEAT,
            'd' => $data->d,
        ];

        $this->send($payload);
    }

    /**
     * Handles heartbeat ACK packets received by the client.
     *
     * @param object $data Packet data.
     */
    protected function handleHeartbeatAck(object $data): void
    {
        $received = microtime(true);
        $diff = $received - $this->heartbeatTime;
        $time = $diff * 1000;

        if (! is_null($this->heartbeatAckTimer)) {
            $this->loop->cancelTimer($this->heartbeatAckTimer);
            $this->heartbeatAckTimer = null;
        }

        $this->emit('heartbeat-ack', [$time, $this]);
        $this->logger->debug('received heartbeat ack', ['response_time' => $time]);
    }

    /**
     * Handles reconnect packets received by the client.
     *
     * @param object $data Packet data.
     */
    protected function handleReconnect(object $data): void
    {
        $this->logger->warning('received opcode 7 for reconnect');

        $this->ws->close(
            Op::CLOSE_UNKNOWN_ERROR,
            'gateway redirecting - opcode 7'
        );
    }

    /**
     * Handles invalid session packets received by the client.
     *
     * @param object $data Packet data.
     */
    protected function handleInvalidSession(object $data): void
    {
        $this->logger->warning('invalid session, re-identifying', ['resumable' => $data->d]);

        $this->loop->addTimer(2, function () use ($data) {
            $this->identify($data->d);
        });
    }

    /**
     * Handles HELLO packets received by the websocket.
     *
     * @param object $data Packet data.
     */
    protected function handleHello(object $data): void
    {
        $this->logger->info('received hello');
        $this->setupHeartbeat($data->d->heartbeat_interval);
        $this->identify();
    }

    /**
     * Identifies with the Lorhondel gateway with `IDENTIFY` or `RESUME` packets.
     *
     * @param  bool $resume Whether resume should be enabled.
     * @return bool
     */
    protected function identify(bool $resume = true): bool
    {
        if ($resume && $this->reconnecting && ! is_null($this->sessionId)) {
            $payload = [
                'op' => Op::OP_RESUME,
                'd' => [
                    'session_id' => $this->sessionId,
                    'seq' => $this->seq,
                    'token' => $this->token,
                ],
            ];

            $reason = 'resuming connection';
        } else {
            $payload = [
                'op' => Op::OP_IDENTIFY,
                'd' => [
                    'token' => $this->token,
                    'properties' => [
                        '$os' => PHP_OS,
                        '$browser' => $this->http->getUserAgent(),
                        '$device' => $this->http->getUserAgent(),
                        '$referrer' => 'https://github.com/VZGCoders/lorhondel-bot',
                        '$referring_domain' => 'https://github.com/VZGCoders/lorhondel-bot',
                    ],
                    'compress' => true,
                    'intents' => $this->options['intents'],
                ],
            ];

            if (
                array_key_exists('shardId', $this->options) &&
                array_key_exists('shardCount', $this->options)
            ) {
                $payload['d']['shard'] = [
                    (int) $this->options['shardId'],
                    (int) $this->options['shardCount'],
                ];
            }

            $reason = 'identifying';
        }

        $safePayload = $payload;
        $safePayload['d']['token'] = 'xxxxxx';

        $this->logger->info($reason, ['payload' => $safePayload]);

        $this->send($payload);

        return $payload['op'] == Op::OP_RESUME;
    }

    /**
     * Sends a heartbeat packet to the Lorhondel gateway.
     */
    public function heartbeat(): void
    {
        $this->logger->debug('sending heartbeat', ['seq' => $this->seq]);

        $payload = [
            'op' => Op::OP_HEARTBEAT,
            'd' => $this->seq,
        ];

        $this->send($payload, true);
        $this->heartbeatTime = microtime(true);
        $this->emit('heartbeat', [$this->seq, $this]);

        $this->heartbeatAckTimer = $this->loop->addTimer($this->heartbeatInterval / 1000, function () {
            if (! $this->connected) {
                return;
            }

            $this->logger->warning('did not receive heartbeat ACK within heartbeat interval, closing connection');
            $this->ws->close(1001, 'did not receive heartbeat ack');
        });
    }

    /**
     * Sets guild member chunking up.
     *
     * @return false|void
     */
    protected function setupChunking()
    {
        if ($this->options['loadAllMembers'] === false) {
            $this->logger->info('loadAllMembers option is disabled, not setting chunking up');

            return $this->ready();
        }

        $checkForChunks = function () {
            if ((count($this->largeGuilds) < 1) && (count($this->largeSent) < 1)) {
                $this->ready();

                return;
            }

            if (count($this->largeGuilds) < 1) {
                $this->logger->debug('unprocessed chunks', $this->largeSent);

                return;
            }

            if (is_array($this->options['loadAllMembers'])) {
                foreach ($this->largeGuilds as $key => $guild) {
                    if (array_search($guild, $this->options['loadAllMembers']) === false) {
                        $this->logger->debug('not fetching members for guild ID '.$guild);
                        unset($this->largeGuilds[$key]);
                    }
                }
            }

            $chunks = array_chunk($this->largeGuilds, 50);
            $this->logger->debug('sending '.count($chunks).' chunks with '.count($this->largeGuilds).' large guilds overall');
            $this->largeSent = array_merge($this->largeGuilds, $this->largeSent);
            $this->largeGuilds = [];

            $sendChunks = function () use (&$sendChunks, &$chunks) {
                $chunk = array_pop($chunks);

                if (is_null($chunk)) {
                    return;
                }

                $this->logger->debug('sending chunk with '.count($chunk).' large guilds');

                foreach ($chunk as $guild_id) {
                    $payload = [
                        'op' => Op::OP_GUILD_MEMBER_CHUNK,
                        'd' => [
                            'guild_id' => $guild_id,
                            'query' => '',
                            'limit' => 0,
                        ],
                    ];

                    $this->send($payload);
                }
                $this->loop->addTimer(1, $sendChunks);
            };

            $sendChunks();
        };

        $this->loop->addPeriodicTimer(5, $checkForChunks);
        $this->logger->info('set up chunking, checking for chunks every 5 seconds');
        $checkForChunks();
    }

    /**
     * Sets the heartbeat timer up.
     *
     * @param int $interval The heartbeat interval in milliseconds.
     */
    protected function setupHeartbeat(int $interval): void
    {
        $this->heartbeatInterval = $interval;
        if (isset($this->heartbeatTimer)) {
            $this->loop->cancelTimer($this->heartbeatTimer);
        }

        $interval = $interval / 1000;
        $this->heartbeatTimer = $this->loop->addPeriodicTimer($interval, [$this, 'heartbeat']);
        $this->heartbeat();

        $this->logger->info('heartbeat timer initilized', ['interval' => $interval * 1000]);
    }

    /**
     * Initializes the connection with the Lorhondel gateway.
     */
    protected function connectWs(): void
    {
        $this->setGateway()->done(function ($gateway) {
            if (isset($gateway['session']) && $session = $gateway['session']) {
                if ($session['remaining'] < 2) {
                    $this->logger->error('exceeded number of reconnects allowed, waiting before attempting reconnect', $session);
                    $this->loop->addTimer($session['reset_after'] / 1000, function () {
                        $this->connectWs();
                    });

                    return;
                }
            }

            $this->logger->info('starting connection to websocket', ['gateway' => $this->gateway]);

            /** @var ExtendedPromiseInterface */
            $promise = ($this->wsFactory)($this->gateway);
            $promise->done(
                [$this, 'handleWsConnection'],
                [$this, 'handleWsConnectionFailed']
            );
        });
    }

    /**
     * Sends a packet to the Lorhondel gateway.
     *
     * @param array $data Packet data.
     */
    protected function send(array $data, bool $force = false): void
    {
        // Wait until payload count has been reset
        // Keep 5 payloads for heartbeats as required
        if ($this->payloadCount >= 115 && ! $force) {
            $this->logger->debug('payload not sent, waiting', ['payload' => $data]);
            $this->once('payload_count_reset', function () use ($data) {
                $this->send($data);
            });
        } else {
            ++$this->payloadCount;
            $data = json_encode($data);
            $this->ws->send($data);
        }
    }

    /**
     * Emits ready if it has not been emitted already.
     * @return false|void
     */
    protected function ready()
    {
        if ($this->emittedReady) {
            return false;
        }

        $this->logger->info('client is ready');
        $this->emit('ready', [$this]);

        foreach ($this->unparsedPackets as $parser) {
            $parser();
        }
    }

    /**
     * Updates the clients presence.
     *
     * @param  Activity|null $activity The current client activity, or null.
     *                                 Note: The activity type _cannot_ be custom, and the only valid fields are `name`, `type` and `url`.
     * @param  bool          $idle     Whether the client is idle.
     * @param  string        $status   The current status of the client.
     *                                 Must be one of the following:
     *                                 online, dnd, idle, invisible, offline
     * @param  bool          $afk      Whether the client is AFK.
     * @throws \Exception
     */
    public function updatePresence(Activity $activity = null, bool $idle = false, string $status = 'online', bool $afk = false): void
    {
        $idle = $idle ? time() * 1000 : null;

        if (! is_null($activity)) {
            $activity = $activity->getRawAttributes();

            if (! in_array($activity['type'], [Activity::TYPE_PLAYING, Activity::TYPE_STREAMING, Activity::TYPE_LISTENING, Activity::TYPE_WATCHING, Activity::TYPE_COMPETING])) {
                throw new \Exception("The given activity type ({$activity['type']}) is invalid.");

                return;
            }
        }

        if (! array_search($status, ['online', 'dnd', 'idle', 'invisible', 'offline'])) {
            $status = 'online';
        }

        $payload = [
            'op' => Op::OP_PRESENCE_UPDATE,
            'd' => [
                'since' => $idle,
                'activities' => [$activity],
                'status' => $status,
                'afk' => $afk,
            ],
        ];

        $this->send($payload);
    }
	
	    /**
     * Retrieves and sets the gateway URL for the client.
     *
     * @param string|null $gateway Gateway URL to set.
     *
     * @return ExtendedPromiseInterface
     */
    protected function setGateway(?string $gateway = null): ExtendedPromiseInterface
    {
        $deferred = new Deferred();
        $defaultSession = [
            'total' => 1000,
            'remaining' => 1000,
            'reset_after' => 0,
            'max_concurrency' => 1,
        ];

        $buildParams = function ($gateway, $session = null) use ($deferred, $defaultSession) {
            $session = $session ?? $defaultSession;
            $params = [
                'v' => self::GATEWAY_VERSION,
                'encoding' => $this->encoding,
            ];

            $query = http_build_query($params);
            $this->gateway = trim($gateway, '/').'/?'.$query;

            $deferred->resolve(['gateway' => $this->gateway, 'session' => (array) $session]);
        };

        if (is_null($gateway)) {
            $this->http->get(Endpoint::GATEWAY_BOT)->done(function ($response) use ($buildParams) {
                $buildParams($response->url, $response->session_start_limit);
            }, function ($e) use ($buildParams) {
                // Can't access the API server so we will use the default gateway.
                $this->logger->warning('could not retrieve gateway, using default');
                $buildParams('wss://lorhondel.valzargaming.com');
            });
        } else {
            $buildParams($gateway);
        }

        $deferred->promise()->then(function ($gateway) {
            $this->logger->info('gateway retrieved and set', $gateway);
        }, function ($e) {
            $this->logger->error('error obtaining gateway', ['e' => $e->getMessage()]);
        });

        return $deferred->promise();
    }

	/*
	* Attempt to catch errors with the user-provided $options early
	*/
	protected function resolveOptions(array $options = []): array
	{
		if ($this->verbose) $this->emit('[LORHONDEL] [RESOLVE OPTIONS]');
		$options['loop'] = $options['loop'] ?? Factory::create();
		$options['browser'] = $options['browser'] ?? new \React\Http\Browser($options['loop']);
		$options['server'] = $options['server'] ?? false;
		$options['command_symbol'] = $options['command_symbol'] ?? ';';
		//Discord must be Discord or null
		//Twitch must be Twitch or null
		
		$resolver = new OptionsResolver();
		$resolver
            ->setRequired('token')
            ->setAllowedTypes('token', 'string')
            ->setDefined([
                'token',
                'shardId',
                'shardCount',
                'loop',
                'logger',
                'loadAllMembers',
                'disabledEvents',
                'pmChannels',
                'storeMessages',
                'retrieveBans',
                'intents',
                'socket_options',
				'browser',
				'discord',
				'server',
				'socket',
				'command_symbol',
            ])
            ->setDefaults([
                'loop' => LoopFactory::create(),
                'logger' => null,
                'loadAllMembers' => false,
                'disabledEvents' => [],
                'pmChannels' => false,
                'storeMessages' => false,
                'retrieveBans' => false,
                'intents' => Intents::getDefaultIntents(),
				'server' => false,
                'socket_options' => [],
				'command_symbol' => ';',
            ])
            ->setAllowedTypes('token', 'string')
            ->setAllowedTypes('logger', ['null', LoggerInterface::class])
            ->setAllowedTypes('loop', LoopInterface::class)
            ->setAllowedTypes('loadAllMembers', ['bool', 'array'])
            ->setAllowedTypes('disabledEvents', 'array')
            ->setAllowedTypes('pmChannels', 'bool')
            ->setAllowedTypes('storeMessages', 'bool')
            ->setAllowedTypes('retrieveBans', 'bool')
            ->setAllowedTypes('intents', ['array', 'int'])
			->setAllowedTypes('server', 'bool')
            ->setAllowedTypes('socket_options', 'array')
			->setAllowedTypes('command_symbol', 'string');

        $options = $resolver->resolve($options);
		
		return $options;
	}
	
	/**
     * Adds a large guild to the large guild array.
     *
     * @param Guild $guild The guild.
     */
    public function addLargeGuild(Part $guild): void
    {
        $this->largeGuilds[] = $guild->id;
    }
	
	public function run(): void
	{
		if ($this->verbose) $this->emit('[LORHONDEL] [RUN]');
		if (!(isset($this->discord))) $this->emit('[WARNING] Discord not set!');
		else $this->discord->run();
	}
	
	/**
     * Closes the Lorhondel client.
     *
     * @param bool $closeLoop Whether to close the loop as well. Default true.
     */
    public function close(bool $closeLoop = true): void
    {
        $this->closing = true;
        $this->ws->close(Op::CLOSE_UNKNOWN_ERROR, 'lorhondel closing...');
        $this->emit('closed', [$this]);
        $this->logger->info('lorhondel closed');

        if ($closeLoop) {
            $this->loop->stop();
        }
    }

    /**
     * Allows access to the part/repository factory.
     *
     * @param string $class   The class to build.
     * @param mixed  $data    Data to create the object.
     * @param bool   $created Whether the object is created (if part).
     *
     * @return Part|AbstractRepository
     *
     * @see Factory::create()
     */
    public function factory(string $class, $data = [], bool $created = false)
    {
        return $this->factory->create($class, $data, $created);
    }

    /**
     * Gets the factory.
     *
     * @return Factory
     */
    public function getFactory(): Factory
    {
        return $this->factory;
    }

    /**
     * Gets the HTTP client.
     *
     * @return Http
     */
    public function getHttpClient(): Http
    {
        return $this->http;
    }

    /**
     * Gets the loop being used by the client.
     *
     * @return LoopInterface
     */
    public function getLoop(): LoopInterface
    {
        return $this->loop;
    }

    /**
     * Gets the logger being used.
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Gets the HTTP client.
     *
     * @return Http
     *
     * @deprecated Use Lorhondel::getHttpClient()
     */
    public function getHttp(): Http
    {
        return $this->http;
    }

    /**
     * Handles dynamic get calls to the client.
     *
     * @param string $name Variable name.
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        $allowed = ['loop', 'options', 'logger', 'http'];

        if (array_search($name, $allowed) !== false) {
            return $this->{$name};
        }

        if (is_null($this->client)) {
            return;
        }

        return $this->client->{$name};
    }

    /**
     * Handles dynamic set calls to the client.
     *
     * @param string $name  Variable name.
     * @param mixed  $value Value to set.
     */
    public function __set(string $name, $value)
    {
        if (is_null($this->client)) {
            return;
        }

        $this->client->{$name} = $value;
    }

    /**
     * Gets a channel.
     *
     * @param string|int $channel_id Id of the channel.
     *
     * @return Channel|null
     */
    public function getChannel($channel_id): ?Channel
    {
        foreach ($this->guilds as $guild) {
            if ($channel = $guild->channels->get('id', $channel_id)) {
                return $channel;
            }
        }

        if ($channel = $this->private_channels->get('id', $channel_id)) {
            return $channel;
        }

        return null;
    }

    /**
     * Handles dynamic calls to the client.
     *
     * @param string $name   Function name.
     * @param array  $params Function paramaters.
     *
     * @return mixed
     */
    public function __call(string $name, array $params)
    {
        if (is_null($this->client)) {
            return;
        }

        return call_user_func_array([$this->client, $name], $params);
    }

    /**
     * Returns an array that can be used to describe the internal state of this
     * object.
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        $secrets = [
            'token' => '*****',
        ];
        $replace = array_intersect_key($secrets, $this->options);
        $config = $replace + $this->options;

        unset($config['loop'], $config['logger']);

        return $config;
    }
}