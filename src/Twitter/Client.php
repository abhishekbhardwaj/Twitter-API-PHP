<?php namespace Twitter;

use Twitter\Config\Credentials;
use Twitter\Config\AppCredentials;
use Twitter\Config\UserCredentials;
use Twitter\Config\Exceptions\TwitterCredentialsException;

use Twitter\Connections\AppConnection;
use Twitter\Connections\UserConnection;

class Client {

    /**
     * Twitter Credentials.
     *
     * @var AppCredentials|UserCredentials
     */
    private $credentials;

    /**
     * The current connection in use.
     *
     * @var AppConnection|UserConnection
     */
    private $connection;

    /**
     * Constructs a new Twitter Client object.
     *
     * $credentials should be an instance of AppCredentials if you want to make calls on Application's behalf.
     * $credentials should be an instance of UserCredentials if you want to make calls on users's behalf.
     *
     * @param Credentials $credentials Twitter credentials
     *
     * @throws TwitterCredentialsException If not a subclass of Credentials
     */
    public function __construct(Credentials $credentials)
    {
        //check if $credentials is a subclass of Credentials
        if(is_subclass_of($credentials, Credentials))
        {
            $this->credentials = $credentials;
        }
        else
        {
            //if not a subclass of Credentials, throw exception!
            throw new TwitterCredentialsException('Parameter $credentials should be a subclass of Twitter\Config\Credentials.');
        }
    }

    /**
     * Checks the type of $credentials (if App or User) and returns a new connection instance accordingly.
     *
     * @return AppConnection|UserConnection Type of connection to Twitter.
     */
    public function connect()
    {
        if($this->credentials instanceof AppCredentials)
        {
            //set application specific connection
            $this->connection = new AppConnection($this->credentials);

            return $this->connection;
        }
        else
        {
            //set User specific connection
            $this->connection = new UserConnection($this->credentials);

            return $this->connection;
        }
    }

    /**
     * Gets the Twitter Credentials.
     *
     * @return AppCredentials|UserCredentials
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Sets the Twitter Credentials.
     *
     * @param AppCredentials|UserCredentials $credentials the credentials
     *
     * @return self
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;

        return $this;
    }

    /**
     * Gets the The current connection in use.
     *
     * @return AppConnection|UserConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Sets the The current connection in use.
     *
     * @param AppConnection|UserConnection $connection the connection
     *
     * @return self
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }
}
