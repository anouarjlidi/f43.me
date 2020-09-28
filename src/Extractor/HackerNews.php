<?php

namespace App\Extractor;

class HackerNews extends AbstractExtractor
{
    protected $text = null;

    /**
     * {@inheritdoc}
     */
    public function match($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $query = parse_url($url, PHP_URL_QUERY);

        if (null === $host || null === $query) {
            return false;
        }

        if (0 !== strpos($host, 'news.ycombinator.com')) {
            return false;
        }

        // match HN id
        preg_match('/id\=([0-9]+)/i', $query, $matches);

        if (!isset($matches[1])) {
            return false;
        }

        try {
            $response = $this->client->get('https://hacker-news.firebaseio.com/v0/item/' . $matches[1] . '.json');
            $data = $this->jsonDecode($response);
        } catch (\Exception $e) {
            return false;
        }

        if (\in_array($data['type'], ['comment', 'pollopt'], true)
            || !isset($data['text'])
            || '' === trim($data['text'])) {
            return false;
        }

        $this->text = $data['text'];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        if (!$this->text) {
            return '';
        }

        return '<p>' . $this->text . '</p>';
    }
}
