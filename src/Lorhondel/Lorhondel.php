<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel;

class Lorhondel
{
	public $loop;
	public $browser;
	
	public $discord;
	
	protected $verbose = true;
	
	public function __construct(array $options = [])
    {
		$options = $this->resolveOptions($options);
		
		$this->loop = $options['loop'];
		$this->browser = $options['browser'];
		
		if ($options['discord'] || $options['discord_options']) {
			if($options['discord']) $this->discord = $options['discord'];
			elseif($options['discord_options']) $this->discord = new \Discord\Discord($options['discord_options']);
		}
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