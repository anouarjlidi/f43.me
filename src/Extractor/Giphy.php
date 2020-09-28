<?php

namespace App\Extractor;

class Giphy extends AbstractExtractor
{
    protected $giphyUrl = null;

    /**
     * {@inheritdoc}
     */
    public function match($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH);

        if (null === $host || null === $path) {
            return false;
        }

        if (!\in_array($host, ['www.giphy.com', 'giphy.com'], true)) {
            return false;
        }

        preg_match('/\/gifs\//', $path, $matches);

        if (!isset($matches[0])) {
            return false;
        }

        $this->giphyUrl = $url;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        if (!$this->giphyUrl) {
            return '';
        }

        try {
            $response = $this->client->get('http://giphy.com/services/oembed/?url=' . $this->giphyUrl);
            $data = $this->jsonDecode($response);
        } catch (\Exception $e) {
            $this->logger->warning('Giphy extract failed for: ' . $this->giphyUrl, [
                'exception' => $e,
            ]);

            return '';
        }

        if (!\is_array($data) || empty($data)) {
            return '';
        }

        return '<div><h2>' . $data['title'] . '</h2><p><img src="' . $data['url'] . '"></p></div>';
    }
}
