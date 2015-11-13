<?php

namespace NSI\Model;


class Circuit {
    private static $MAX_BANDWIDTH_SIZE = 10000; // 10GB

    private $connectionId;
    private $sourceUrn;
    private $sourceVlanRequestRange;
    private $sourceAppliedVlan;
    private $destinationUrn;
    private $destinationVlanRequestRange;
    private $destinationAppliedVlan;
    private $bandwidth;
    private $startTime;
    private $endTime;
    private $paths;

    public function isActive() {
        $now = time();
        $isInTime = ($now >= $this->startTime && $now <= $this->endTime) ? true : false;
        $haveVlans = ($this->sourceAppliedVlan != null && $this->destinationAppliedVlan != null) ? true : false;

        if($isInTime && $haveVlans && $this->connectionId != null) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getConnectionId()
    {
        return $this->connectionId;
    }

    /**
     * @param mixed $connectionId
     */
    public function setConnectionId($connectionId)
    {
        $this->connectionId = $connectionId;
    }

    /**
     * @return mixed
     */
    public function getSourceUrn()
    {
        return $this->sourceUrn;
    }

    /**
     * @param mixed $sourceUrn
     */
    public function setSourceUrn($sourceUrn)
    {
        $this->sourceUrn = $sourceUrn;
    }

    /**
     * @return mixed
     */
    public function getSourceVlanRequestRange()
    {
        return $this->sourceVlanRequestRange;
    }

    /**
     * @param mixed $sourceVlanRequestRange
     */
    public function setSourceVlanRequestRange($sourceVlanRequestRange)
    {
        $this->sourceVlanRequestRange = $sourceVlanRequestRange;
    }

    /**
     * @return mixed
     */
    public function getSourceAppliedVlan()
    {
        return $this->sourceAppliedVlan;
    }

    /**
     * @param mixed $sourceAppliedVlan
     */
    public function setSourceAppliedVlan($sourceAppliedVlan)
    {
        $this->sourceAppliedVlan = $sourceAppliedVlan;
    }

    /**
     * @return mixed
     */
    public function getDestinationUrn()
    {
        return $this->destinationUrn;
    }

    /**
     * @param mixed $destinationUrn
     */
    public function setDestinationUrn($destinationUrn)
    {
        $this->destinationUrn = $destinationUrn;
    }

    /**
     * @return mixed
     */
    public function getDestinationVlanRequestRange()
    {
        return $this->destinationVlanRequestRange;
    }

    /**
     * @param mixed $destinationVlanRequestRange
     */
    public function setDestinationVlanRequestRange($destinationVlanRequestRange)
    {
        $this->destinationVlanRequestRange = $destinationVlanRequestRange;
    }

    /**
     * @return mixed
     */
    public function getDestinationAppliedVlan()
    {
        return $this->destinationAppliedVlan;
    }

    /**
     * @param mixed $destinationAppliedVlan
     */
    public function setDestinationAppliedVlan($destinationAppliedVlan)
    {
        $this->destinationAppliedVlan = $destinationAppliedVlan;
    }

    /**
     * @return mixed
     */
    public function getBandwidth()
    {
        return $this->bandwidth;
    }

    /**
     * @param mixed $bandwidth
     * @throws \Exception
     */
    public function setBandwidth($bandwidth)
    {
        if($bandwidth >= self::$MAX_BANDWIDTH_SIZE) {
            throw new \Exception("The circuit bandwidth exceed the maximum permitted");
        }

        $this->bandwidth = ($bandwidth < 1) ? 1 : $bandwidth;
    }

    /**
     * @return mixed
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @param mixed $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * @return mixed
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param mixed $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    /**
     * @return mixed
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @param mixed $paths
     */
    public function setPaths($paths)
    {
        $this->paths = $paths;
    }
}