<?php class Twitter\Config;

use Twitter\Config\Exceptions\InvalidConfigItemException;

class Config {

    /**
     * Get an item from the 'Config.json' file.
     *
     * @param  string $item the config item to get
     * @return string       the config URL
     *
     * @throws InvalidConfigItemException If the requested config item doesn't exist.
     */
    public static function get($item)
    {
        //convert the item requested to upper, just in case.
        $item = strtoupper($item);

        //get all config items as an associative array from the JSON file
        $config = json_decode(file_get_contents('Config.json'), true);

        //if the requested config item doesn't exist, throw Twitter\Config\Exceptions\InvalidConfigItemException
        if( isset($config[$item]) )
        {
            throw new InvalidConfigItemException("Invalid Endpoint Requested!");
        }

        //return the requested item
        return $config[$item];
    }

}
