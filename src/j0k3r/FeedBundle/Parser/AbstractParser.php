<?php

namespace j0k3r\FeedBundle\Parser;

abstract class AbstractParser
{
    /**
     * Make a readable content from the given url
     *
     * @param string $url
     *
     * @return string
     */
    abstract public function parse($url);
}
