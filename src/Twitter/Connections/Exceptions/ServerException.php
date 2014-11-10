<?php namespace Twitter\Connections\Exceptions;

use GuzzleHttp\Exception\ServerException as GuzzleServerException;

/**
 * Thrown when Twitter's servers are down or under maintenance or under load or
 * under whatever they say they are under.
 *
 * The private $exception variable is an instance of GuzzleHttp\Exception\ServerException
 * and inspecting it can give you an idea of what was exactly wrong.
 *
 * You can get the instance of GuzzleHttp\Exception\ServerException exception by calling the
 * 'getServerException()' method. Below are a few interesting calls that will help
 * you inspect what went wrong:
 *
 * - getServerException()->getRequest(); //shows what request was sent
 * - getServerException()->getResponse(); //shows the response received from Twitter
 * - getServerException()->getMessage(); //shows an interpreted message
 * - getServerException()->hasResponse(); //tells whether a response was received from Twitter.
 */
class ServerException extends \Exception {

    /**
     * The actual exception.
     *
     * @var GuzzleHttp\Exception\ServerException
     */
    private $exception;

    /**
     * Sets the private $exception variable and calls parent constructor.
     *
     * @param string          $message   Exception Description Message
     * @param GuzzleHttp\Exception\ServerException $exception The actual exception!
     */
    public function __construct($message, GuzzleServerException $exception)
    {
        $this->exception = $exception;

        parent::__construct($message);
    }

    /**
     * Returns the actual GuzzleHttp\Exception\ServerException for further inspection.
     *
     * @return ServerException
     */
    public function getServerException()
    {
        return $this->exception;
    }

}
