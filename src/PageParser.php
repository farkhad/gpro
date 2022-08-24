<?php
/**
 *
 * GPRO's Page Parser
 */

namespace Gpro;

abstract class PageParser
{
    public function __construct(public string $subject)
    {
        $this->parse();
    }

    abstract public function parse();
    abstract public function toArray();

    public function toJSON()
    {
        return json_encode($this->toArray());
    }
}
