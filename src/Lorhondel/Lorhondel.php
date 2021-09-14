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
use Lorhondel\WebSockets\Event;
use Lorhondel\WebSockets\Handlers;
use Lorhondel\WebSockets\Intents;
use Lorhondel\WebSockets\Op;
use Lorhondel\Helpers\Deferred;
use Lorhondel\Drivers\React;
use Lorhondel\Endpoint;

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
	
	
	public $browser;
	
	/**
     * The part/repository factory.
     *
     * @var Factory Part factory.
     */
    protected $factory;
	
	public $discord;
	
	protected $verbose = true;
	
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
			if($options['discord']) $this->discord = $options['discord'];
			elseif($options['discord_options']) $this->discord = new \Discord\Discord($options['discord_options']);
		}
		
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

        $this->factory = new Factory($this, $this->http);
		//$this->client = $this->factory->create(Client::class, [], true);

        //$this->connectWs();
	}
	
	/*
	* Attempt to catch errors with the user-provided $options early
	*/
	protected function resolveOptions(array $options = []): array
	{
		if ($this->verbose) $this->emit('[LORHONDEL] [RESOLVE OPTIONS]');
		$options['loop'] = $options['loop'] ?? Factory::create();
		$options['browser'] = $options['browser'] ?? new \React\Http\Browser($options['loop']);
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
				'discord'
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
                'socket_options' => [],
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
            ->setAllowedTypes('socket_options', 'array');

        $options = $resolver->resolve($options);
		
		return $options;
	}
	
	public function emit(string $string): void
	{
		echo "[EMIT] $string" . PHP_EOL;
	}
	
	public function run(): void
	{
		if ($this->verbose) $this->emit('[LORHONDEL] [RUN]');
		if(!(isset($this->discord))) $this->emit('[WARNING] Discord not set!');
		else $this->discord->run();
	}
}