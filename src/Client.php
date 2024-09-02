<?php

namespace GooglePlaces;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

class Client
{
    /**
     * The base URL for the Google Places API.
     *
     * This constant defines the base endpoint for all API requests made by this client.
     * It is used as the base URI in the Guzzle client configuration.
     */
    const API_URL = 'https://places.googleapis.com/v1/';

    /**
     * The Guzzle HTTP client instance.
     *
     * This protected property holds the instance of the Guzzle client used to make HTTP requests
     * to the Google Places API. It is initialized in the constructor.
     *
     * @var GuzzleClient
     */
    protected GuzzleClient $client;

    /**
     * The API key for authenticating requests to the Google Places API.
     *
     * This private property stores the API key required to authenticate all requests
     * made to the Google Places API. It is provided during class instantiation and
     * should be kept private to ensure security.
     *
     * @var string
     */
    private string $api_key;

    /**
     * Constructor for the Google Places API client.
     *
     * This constructor initializes the Google Places API client using Guzzle,
     * setting the base URI and default headers for API requests. It also assigns
     * the provided API key to be used in requests.
     *
     * @param string $api_key The API key for authenticating requests to the Google Places API.
     *
     * @throws \InvalidArgumentException If the API key is not provided.
     */
    public function __construct(string $api_key)
    {
        if (!$api_key) {
            throw new \InvalidArgumentException('API key is required');
        }

        $this->api_key = $api_key;

        $this->client = new GuzzleClient([
            'base_uri' => self::API_URL,
            'headers' => [
                'X-Goog-Api-Key' => $this->api_key,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Prepare and format the fields parameter for Google Places API requests.
     *
     * This function takes an array or string of fields and formats them as a comma-separated string.
     * It also provides an option to remove a specific prefix ("places.") from the field names.
     *
     * @param array|string $fields The fields to be included in the API request.
     *                             Can be provided as an array or a comma-separated string.
     *                             If empty, defaults to '*' to select all fields.
     * @param bool $clean_prefix Optional. If true, the function will remove the "places." prefix
     *                           from each field name. Defaults to false.
     *
     * @return string A formatted, comma-separated string of fields to be used in the API request.
     */
    public function prepare_fields(array|string $fields, bool $clean_prefix = false): string
    {
        $fields = !empty($fields) ? $fields : '*';

        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }

        $fields = str_replace(' ', '', $fields);

        if ($clean_prefix) {
            $fields = str_replace('places.', '', $fields);
        }

        return $fields;
    }

    /**
     * Perform a nearby search using the Google Places API.
     *
     * This function sends a request to the Google Places API to search for places
     * near a specified location within a given radius. It allows additional parameters
     * to customize the search and response.
     *
     * @param string $location A comma-separated string representing the latitude and longitude (e.g., "37.7749,-122.4194").
     * @param int $radius The radius (in meters) within which to search for places.
     * @param array $params Optional. Additional parameters for the API request.
     *
     * @return array The decoded JSON response from the Google Places API as an associative array.
     *
     * @throws \Exception|GuzzleException If the location is invalid (does not contain both latitude and longitude).
     */
    public function near_by_search(string $location, int $radius, array $params = []): array
    {
        $url = self::API_URL . 'places:searchNearby';
        $location = explode(',', $location);

        if (empty($location) || count($location) < 2) {
            throw new \InvalidArgumentException('Invalid location');
        }

        $params = array_merge($params, [
            'locationRestriction' => [
                'circle' => [
                    'center' => [
                        'latitude' => $location[0],
                        'longitude' => $location[1],
                    ],
                    'radius' => $radius
                ],
            ]
        ]);

        $fields = $this->prepare_fields($params['fields'], false);

        unset($params['fields']);

        $response = $this->client->post($url, [
            'query' => [
                'fields' => $fields,
            ],
            'json' => $params,
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        if (empty($body)) {
            throw new \Exception('Invalid response from API');
        }

        return $body['places'];
    }

    /**
     * Retrieve details about a specific place using the Google Places API.
     *
     * This function sends a GET request to the Google Places API to retrieve information
     * about a place identified by its place ID. The response can be customized to include
     * specific fields.
     *
     * @param string $place_id The unique identifier of the place to retrieve information about.
     * @param array $fields Optional. An array of fields to be included in the response.
     *                      If no fields are specified, all available fields are returned.
     *
     * @return array The decoded JSON response from the Google Places API as an associative array.
     *
     * @throws GuzzleException|\Exception If the Guzzle HTTP client encounters an error during the request.
     */
    public function get_place(string $place_id, array $fields = []): array
    {
        $url = self::API_URL . 'places/' . $place_id;

        $fields = $this->prepare_fields($fields, true);

        $response = $this->client->get($url, [
            'query' => [
                'fields' => $fields,
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        if (empty($body)) {
            throw new \Exception('Invalid response from API');
        }

        return $body;
    }

    /**
     * Perform a text search using the Google Places API.
     *
     * This function sends a GET request to the Google Places API to search for places
     * based on a text query. The response can be customized to include specific fields.
     *
     * @param string $query The search query text, such as the name of a place, address, or keyword.
     * @param array $fields Optional. An array of fields to be included in the response.
     *                      If no fields are specified, all available fields are returned.
     *
     * @return array The decoded JSON response from the Google Places API as an associative array.
     *
     * @throws GuzzleException|\Exception If the Guzzle HTTP client encounters an error during the request.
     */
    public function search_text(string $query, array $fields = []): array
    {
        $url = self::API_URL . 'places:searchText';

        $fields = $this->prepare_fields($fields);

        $response = $this->client->post($url, [
            'query' => [
                'fields' => $fields,
            ],
            'json' => [
                'textQuery' => $query,
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        if (empty($body)) {
            throw new \Exception('Invalid response from API');
        }

        return $body['places'];
    }

    /**
     * Perform an autocomplete search using the Google Places API.
     *
     * This function sends a POST request to the Google Places API to retrieve place suggestions
     * based on partial input provided by the user. Additional parameters can be passed to customize
     * the search results.
     *
     * @param string $input The partial text input from the user for which autocomplete suggestions are sought.
     * @param array $params Optional. Additional parameters for the API request, such as location, radius, or language.
     *
     * @return array An array of autocomplete suggestions returned by the API.
     *
     * @throws \Exception|GuzzleException If the API response is invalid or empty.
     */
    public function autocomplete(string $input, array $params = []): array
    {
        $url = self::API_URL . 'places:autocomplete';

        $params = array_merge($params, [
            'input' => $input,
        ]);

        $response = $this->client->post($url, [
            'json' => $params,
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        if (empty($body)) {
            throw new \Exception('Invalid response from API');
        }

        return $body['suggestions'];
    }
}