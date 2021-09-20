<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Factory;

use Lorhondel\Lorhondel;
use Lorhondel\Http;
use Lorhondel\Parts\Part;
use Lorhondel\Repository\AbstractRepository;

/**
 * Exposes an interface to build part objects without the other requirements.
 */
class Factory
{
    /**
     * The Lorhondel client.
     *
     * @var lorhondel Client.
     */
    public $lorhondel;
	
    /**
     * The HTTP client.
     *
     * @var Http Client.
     */
    protected $http;

    /**
     * Constructs a factory.
     *
     * @param Lorhondel $lorhondel The Lorhondel client.
	 * @param Http    $http    The HTTP client.
     */
    public function __construct(Lorhondel $lorhondel, Http $http)
    {
        $this->lorhondel = $lorhondel;
		$this->http = $http;
    }

    /**
     * Creates an object.
     *
     * @param string $class   The class to build.
     * @param mixed  $data    Data to create the object.
     * @param bool   $created Whether the object is created (if part).
     *
     * @return Part|AbstractRepository The object.
     * @throws \Exception
     */
    public function create(string $class, $data = [], bool $created = false)
    {
        if (! is_array($data)) {
            $data = (array) $data;
        }

        if (strpos($class, 'Lorhondel\\Parts') !== false) {
            $object = $this->part($class, $data, $created);
        } elseif (strpos($class, 'Lorhondel\\Repository') !== false) {
            $object = $this->repository($class, $data/*, $this->lorhondel->browser*/);
        } else {
            throw new \Exception('The class '.$class.' is not a Part or a Repository.');
        }

        return $object;
    }

    /**
     * Creates a part.
     *
     * @param string $class   The class to build.
     * @param array  $data    Data to create the object.
     * @param bool   $created Whether the object is created (if part).
     *
     * @return Part The part.
     */
    public function part(string $class, array $data = [], bool $created = false): Part
    {
        return new $class($this->lorhondel, $data, $created);
    }

    /**
     * Creates a repository.
     *
     * @param string $class The class to build.
     * @param array  $data  Data to create the object.
     *
     * @return AbstractRepository The repository.
     */
    public function repository(string $class, array $data = [], $browser = null): AbstractRepository
    {
		if ($class == 'Player')
			return new $class($this->http, $this, $data, $browser);
        return new $class($this->http, $this, $data);
    }
}
