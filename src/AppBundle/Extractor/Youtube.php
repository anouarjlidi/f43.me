<?php

namespace AppBundle\Extractor;

class Youtube extends AbstractExtractor
{
    protected $youtubeUrl = null;

    /**
     * {@inheritdoc}
     */
    public function match($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH);

        if (false === $host || false === $path) {
            return false;
        }

        if (!\in_array($host, ['www.youtube.com', 'youtube.com', 'youtu.be'], true)) {
            return false;
        }

        $this->youtubeUrl = $url;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        if (!$this->youtubeUrl) {
            return '';
        }

        try {
            $response = $this->client->get('http://www.youtube.com/oembed?format=json&url=' . $this->youtubeUrl);
            $data = $this->jsonDecode($response);
        } catch (\Exception $e) {
            $this->logger->warning('Youtube extract failed for: ' . $this->youtubeUrl, [
                'exception' => $e,
            ]);

            return '';
        }

        if (!\is_array($data) || empty($data)) {
            return '';
        }

        return '<div><h2>' . $data['title'] . '</h2><p><img src="' . $data['thumbnail_url'] . '"></p>' . $data['html'] . '</div>';
    }
}
