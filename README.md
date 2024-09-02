## Google Places API (New) API Wrapper (README)

This PHP wrapper provides an easy-to-use interface for interacting with the Google Places API. It simplifies the process of making API requests for place searches, place details, and autocomplete features. The wrapper handles API authentication, request formatting, and response parsing, allowing developers to integrate Google Places functionality into their PHP applications with minimal effort.

## Features

- **Nearby Search**: Search for places near a specified location within a given radius.
- **Place Details**: Retrieve detailed information about a specific place using its place ID.
- **Text Search**: Search for places based on a text query.
- **Autocomplete**: Get place suggestions based on partial user input.

## Installation

To use this wrapper, you'll need to have [Composer](https://getcomposer.org/) installed.

```
composer require mustafakucuk/google-places
```

## Usage

### Initialization

Before you can make any requests, you need to initialize the client with your Google Places API key:

```php
use GooglePlaces\Client;

$client = new Client('your_api_key_here');
```

### Nearby Search

Search for places near a specified location within a given radius:

```php
$location = '37.7749,-122.4194'; // Latitude, Longitude
$radius = 500; // Radius in meters
$params = [
    'includedTypes' => ['restaurant'],
    'fields' => ['places.id', 'places.displayName'],
];

$places = $client->near_by_search($location, $radius, $params);
print_r($places);
```

### Place Details

Retrieve detailed information about a specific place using its place ID:

```php
$place_id = 'ChIJlYL0Wa-BhYARJi6qr49Ncv1';
$fields = ['displayName', 'id', 'googleMapsUri', 'formattedAddress'];

$place_details = $client->get_place($place_id, $fields);
print_r($place_details);
```

### Text Search

Search for places based on a text query:

```php
$query = 'restaurants in Sydney';
$fields = ['places.id', 'places.displayName'];

$places = $client->search_text($query, $fields);
print_r($places);
```

### Autocomplete

Get place suggestions based on partial user input:

```php
$input = 'Pizza';
$params = [
    'includedPrimaryTypes' => ['mexican_restaurant'],
];
$suggestions = $client->autocomplete($input, $params);
print_r($suggestions);
```

## Methods

### `prepare_fields(array|string $fields, bool $clean_prefix = false): string`

Formats and prepares the fields parameter for API requests.

- **$fields**: The fields to be included in the API request. Can be provided as an array or a comma-separated string. Defaults to `'*'` if empty.
- **$clean_prefix**: Optional. If `true`, removes the `places.` prefix from field names. Defaults to `false`.
- **Returns**: A formatted, comma-separated string of fields.

### `near_by_search(string $location, int $radius, array $params = []): array`

Performs a nearby search for places.

- **$location**: A comma-separated string representing the latitude and longitude (e.g., `"37.7749,-122.4194"`).
- **$radius**: The radius (in meters) within which to search for places.
- **$params**: Optional. Additional parameters for the API request.
- **Returns**: An associative array of places.

### `get_place(string $place_id, array $fields = []): array`

Retrieves details about a specific place using its place ID.

- **$place_id**: The unique identifier of the place.
- **$fields**: Optional. An array of fields to include in the response.
- **Returns**: An associative array with place details.

### `search_text(string $query, array $fields = []): array`

Performs a text-based search for places.

- **$query**: The search query text.
- **$fields**: Optional. An array of fields to include in the response.
- **Returns**: An associative array of places.

### `autocomplete(string $input, array $params = []): array`

Provides autocomplete suggestions based on user input.

- **$input**: The partial text input from the user.
- **$params**: Optional. Additional parameters for the API request.
- **Returns**: An array of autocomplete suggestions.

## Error Handling

The wrapper throws exceptions when it encounters errors. Ensure you handle exceptions in your implementation:

```php
try {
    $places = $client->near_by_search($location, $radius, $params);
} catch (\Exception $e) {
    // Handle errors
}
```
