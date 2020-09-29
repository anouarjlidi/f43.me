<?php

namespace App\Xml;

use App\Entity\Feed;
use App\Repository\ItemRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class Render
{
    /** @var string */
    protected $generator;
    /** @var ItemRepository */
    protected $itemRepository;
    /** @var RouterInterface */
    protected $router;

    /**
     * @param string $generator Like "Generated by foobar"
     */
    public function __construct(string $generator, ItemRepository $itemRepository, RouterInterface $router)
    {
        $this->generator = $generator;
        $this->itemRepository = $itemRepository;
        $this->router = $router;
    }

    /**
     * Render the feed in specified format.
     *
     * @param Feed $feed Feed to render
     *
     * @throws \InvalidArgumentException if given format formatter does not exists
     */
    public function doRender(Feed $feed): string
    {
        $items = $this->itemRepository->findByFeed(
            $feed->getId(),
            $feed->getSortBy()
        );

        $feedUrl = $this->router->generate(
            'feed_xml',
            ['slug' => $feed->getSlug()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        switch ($feed->getFormatter()) {
            case 'rss':
                $formatter = new Formatter\RssFormatter($feed, $items, $feedUrl, $this->generator);
                break;
            case 'atom':
                $formatter = new Formatter\AtomFormatter($feed, $items, $feedUrl, $this->generator);
                break;
            default:
                throw new \InvalidArgumentException(sprintf("Format '%s' is not available. Please see documentation.", $feed->getFormatter()));
        }

        return $formatter->render();
    }
}
