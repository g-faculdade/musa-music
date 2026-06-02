<?php

class DeezerClient
{
    public function getSearch(string $search): array
    {
        $query = urlencode($search);

        $url = "https://api.deezer.com/search?q={$query}";
        $response = file_get_contents($url);

        $data = json_decode($response, true);

        return $data['data'] ?? [];
    }

    public function getPopular(): array
    {
        $url = "https://api.deezer.com/chart";
        $response = file_get_contents($url);

        $data = json_decode($response, true);

        return $data['tracks']['data'] ?? [];
    }
}
