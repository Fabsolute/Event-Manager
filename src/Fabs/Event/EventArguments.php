<?php

/**
 * Created by PhpStorm.
 * User: ahmetturk
 * Date: 31/03/2017
 * Time: 10:39
 */
namespace Fabs\Event;
class EventArguments
{
    protected $channel = '';
    protected $source = null;
    protected $data = null;
    protected $stopped = false;

    /**
     * EventArguments constructor.
     * @param string $channel
     * @param object $source
     * @param mixed $data
     */
    public function __construct($channel, $source, $data = null)
    {
        $this->channel = $channel;
        $this->source = $source;
        $this->data = $data;
    }

    /**
     * @return object
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return $this->stopped;
    }

    /**
     * @return EventArguments
     */
    public function stop()
    {
        $this->stopped = true;
        return $this;
    }
}