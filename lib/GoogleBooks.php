<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-08-28 18:05:21
 * @modify date 2022-08-28 21:24:53
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

    /**
     * Get Google Books instance
     *
     * @return GoogleBooks
     */
    public static function getInstance(): GoogleBooks
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
    public static function search(string $query): GoogleBooks
    {
        $client = self::getInstance()->getClient();
        
        try {
            $request = $client->request('GET', self::getInstance()->endpoint . '?q=' . $query);
            self::getInstance()->result = $request->getBody()->getContents();
            self::getInstance()->status = [
                'status' => true,
                'message' => 'ok'
            ];
        } catch (ClientException $e) {
            self::getInstance()->status = [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }

        return self::getInstance();
    }

    /**
     * Get result as collection
     *
     * @return Collection
     */
    public function get(): Collection
    {
        $data = Json::parse(self::getInstance()->result);
        $collection = new Collection('mixed', $data->items);
        return $collection;
    }

    /**
     * call property as function
     *
     * @param string $method
     * @param array $params
     */
    public function __call($method, $params)
    {
        $propertyName = lcfirst(str_replace('get', '', $method));
        $paramProcessor = function($property, $params) {
            if (is_array($property) && count($params) > 0) return $property[$params[0]]??null;
            if (is_object($property) && count($params) > 0 && property_exists($property, $params[0])) return $property->{$params[0]};
            return $property;
        };

        if (property_exists($this, $propertyName)) return $paramProcessor($this->{$propertyName}, $params);
    }
}