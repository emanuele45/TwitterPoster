<?php

if (!defined('ELK'))
	die('No access...');

require EXTDIR . '/TwitterOAuth/vendor/autoload.php';

use TwitterOAuth\Auth\SingleUserAuth;
/**
* Serializer Namespace
*/
use TwitterOAuth\Serializer\ArraySerializer;

function initTwitterOAuth($credentials, $status)
{
	date_default_timezone_set('UTC');

	/**
	* Instantiate SingleUser
	*
	* For different output formats you can set one of available serializers
	* (Array, Json, Object, Text or a custom one)
	*/
	$serializer = new ArraySerializer();
	$auth = new SingleUserAuth($credentials, $serializer);

	/**
	* Now you can post something with the media ids given by Twitter
	*
	* https://dev.twitter.com/rest/reference/post/statuses/update
	*/
	$params = array(
		'status' => $status,
	);
	$response = $auth->post('statuses/update', $params);

	return $response;
}