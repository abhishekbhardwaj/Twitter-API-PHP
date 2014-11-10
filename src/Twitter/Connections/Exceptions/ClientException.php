<?php namespace Twitter\Connections\Exceptions;

use GuzzleHttp\Exception\ClientException as GuzzleClientException;

/**
 * Thrown when Twitter says the user made an error.
 *
 * The private $exception variable is an instance of GuzzleHttp\Exception\ClientException
 * and inspecting it can give you an idea of what was exactly wrong.
 *
 * You can get the instance of GuzzleHttp\Exception\ClientException exception by calling the
 * 'getClientException()' method. Below are a few interesting calls that will help
 * you inspect what went wrong:
 *
 * - getClientException()->getRequest(); //shows what request was sent
 * - getClientException()->getResponse(); //shows the response received from Twitter
 * - getClientException()->getMessage(); //shows an interpreted message
 * - getClientException()->hasResponse(); //tells whether a response was received from Twitter.
 */
class ClientException extends \Exception {

    /**
     * The actual exception.
     *
     * @var GuzzleHttp\Exception\ClientException
     */
    private $exception;

    /**
     * Sets the private $exception variable and calls parent constructor.
     *
     * @param string          $message   Exception Description Message
     * @param GuzzleHttp\Exception\ClientException $exception The actual exception!
     */
    public function __construct($message, GuzzleClientException $exception)
    {
        $this->exception = $exception;

        parent::__construct($message);
    }

    /**
     * Returns the actual GuzzleHttp\Exception\ClientException for further inspection.
     *
     * @return ClientException
     */
    public function getClientException()
    {
        return $this->exception;
    }

}
