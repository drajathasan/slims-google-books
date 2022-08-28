<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-08-28 18:05:21
 * @modify date 2022-08-28 18:41:16
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Ramsey\Collection\Collection;

class GoogleBooks
{
    /**
     * Property for store object instance
     *
     */
    private static $instance = null;

    /**
     * Google API endpoint
     *
     * @var string
     */
    private string $endpoint = 'https://www.googleapis.com/books/v1/volumes';

    /**
     * HTTP Client status
     *
     * @var array
     */
    private array $status = [];

    /**
     * Result of google apis 
     * response
     *
     * @var string
     */
    private string $result = '';

    public static function getInstance()
    {
        if (is_null(self::$instance)) self::$instance = new GoogleBooks;
        return self::$instance;
    }

    /**
     * Get guzzle http client instance
     *
     * @return value
     */
    private function getClient()
    {
        return new Client;
    }

    /**
     * Search books by query
     *
     * @param string $query
     * @return void
     */
    public static function search(string $query)
    {
        $client = self::getInstance()->getClient();
        try {
            $request = $client->request('GET', self::getInstance()->endpoint . '?q=' . $query);
            self::getInstance()->result = $request->getBody()->getContents();
        } catch (ClientException $e) {
            self::getInstance()->status = [
                'message' => $e->getMessage(),
                'http_code' => $client->getStatusCode()
            ];
        }

        return self::getInstance();
    }

    public function get()
    {
        $data = Json::parse(self::getInstance()->result)->toArray();
        $collection = new Collection('mixed', $data['items']);
        return $collection;
    }
}