<?php

namespace NSI\Model;


class Reservation {
    private $name;
    private $description;
    private $circuit;
    private $version;

    public function __construct($name, $description, Circuit $circuit, $version)
    {
        $this->name = $name;
        $this->description = $description;
        $this->circuit = $circuit;
        $this->version = $version;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getCircuit()
    {
        return $this->circuit;
    }

    /**
     * @param mixed $circuit
     */
    public function setCircuit(Circuit $circuit)
    {
        $this->circuit = $circuit;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }
}