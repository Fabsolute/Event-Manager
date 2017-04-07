<?php

/**
 * Created by PhpStorm.
 * User: ahmetturk
 * Date: 31/03/2017
 * Time: 10:10
 */

namespace Fabs\Event;

use Closure;
use Exception;
use SplPriorityQueue;

class Messenger
{
    /** @var SplPriorityQueue[] */
    protected $events = [];

    /**
     * @param $channel string
     * @param $handler callable|object
     * @param int $priority
     * @return Messenger
     * @throws Exception
     */
    public function addListener($channel, $handler, $priority = 100)
    {
        if (is_object($handler) || is_callable($handler)) {
            if (is_string($channel)) {
                if (!array_key_exists($channel, $this->events)) {
                    $this->events[$channel] = new SplPriorityQueue();
                }
                $queue = $this->events[$channel];
                $queue->insert($handler, $priority);
            } else {
                throw new Exception('channel must be string');
            }
        } else {
            throw new Exception('handler must be callable or object');
        }
        return $this;
    }

    /**
     * @param $channel string
     * @param $handler callable
     * @return Messenger
     */
    public function removeListener($channel, $handler)
    {
        if (array_key_exists($channel, $this->events)) {
            $queue = $this->events[$channel];
            $queue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
            $queue->top();

            $new_queue = new SplPriorityQueue();

            while ($queue->valid()) {
                $queue_element = $queue->current();
                $queue->next();

                $data = $queue_element['data'];
                $priority = $queue_element['priority'];
                if ($data != $handler) {
                    $new_queue->insert($data, $priority);
                }
            }

            $this->events[$channel] = $new_queue;
        }

        return $this;
    }

    /**
     * @param $channel string
     * @return Messenger
     */
    public function removeAllListeners($channel)
    {
        unset($this->events[$channel]);
        return $this;
    }

    /**
     * @param string $channel
     * @param object $source
     * @param mixed $data
     */
    public function raise($channel, $source = null, $data = null)
    {
        if (array_key_exists($channel, $this->events)) {
            $queue = $this->events[$channel];
            $event_arguments = new EventArguments($channel, $source, $data);
            $this->raiseWithQueue($queue, $event_arguments);
        }
    }

    /**
     * @param $queue SplPriorityQueue
     * @param $event_arguments EventArguments
     */
    public function raiseWithQueue($queue, $event_arguments)
    {
        $queue = clone $queue;
        $queue->top();

        while ($queue->valid()) {
            $handler = $queue->current();
            $queue->next();

            if ($handler instanceof Closure) {
                call_user_func($handler, $event_arguments);
            } else {
                $method_name = 'on_' . $event_arguments->getChannel();
                $method_name = str_replace(' ', '', ucwords(str_replace('-', ' ', str_replace('_', ' ', $method_name))));
                $method_name[0] = strtolower($method_name[0]);

                if (method_exists($handler, $method_name)) {
                    call_user_func([$handler, $method_name], $event_arguments);
                }
            }

            if ($event_arguments->isStopped()) {
                break;
            }
        }
    }
}