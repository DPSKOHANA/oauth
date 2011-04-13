<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Kohana_OAuth2_Provider {

	public static function factory($name, array $options = NULL)
	{
		$class = 'OAuth2_Provider_'.$name;

		return new $class($options);
	}

	abstract public function url_authorize();

	abstract public function url_access_token(array $params = NULL);

	public $name;

	public function url_refresh_token()
	{
		// By default its the same as access token URL
		return $this->url_access_token();
	}

	public function request_token(OAuth_Consumer $consumer, array $params = NULL)
	{
		// Create a new GET request for a request token with the required parameters
		$request = OAuth2_Request::factory('authorize', 'GET', $this->url_authorize(), array(
			'response_type' => 'code',
			'client_id'     => $consumer->key(),
			'redirect_uri'  => $consumer->callback(),
		));

		if ($params)
		{
			// Load user parameters
			$request->params($params);
		}

		// Execute request (will redirect to OAuth server)
		$request->execute();
	}

	public function access_token(OAuth_Consumer $consumer, $code, array $params = NULL)
	{
		$params = (array)$params + array(
			'grant_type'    => 'authorization_code',
			'client_id'     => $consumer->key(),
			'code'          => $code,
		);

		if ($secret = $consumer->secret())
		{
			$params['client_secret'] = $secret;
		}

		$request = OAuth2_Request::factory('token', 'POST', $this->url_access_token(), $params);

		$response = $request->execute();
		return OAuth2_Token::factory('access', array(
			'token'    => $response->param('access_token')
		));
	}

}
