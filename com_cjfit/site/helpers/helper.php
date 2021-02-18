<?php
use League\OAuth2\Client\Token\AccessToken;

/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class CjFitHelper
{
	public static $_cypher = 'AES-128-CBC';
	
	public static function getEncryptedToken(AccessToken $accessToken, $key)
	{
		$token 						= array();
		$token['access_token'] 		= $accessToken->getToken();
		$token['refresh_token'] 	= $accessToken->getRefreshToken();
		$token['resource_owner_id'] = $accessToken->getResourceOwnerId();
		$token['expires'] 			= $accessToken->getExpires();
		$token 						= array_merge($token, $accessToken->getValues());
		$jsonToken 					= json_encode($token);
		$ivlen 						= openssl_cipher_iv_length(self::$_cypher);
		$iv 						= openssl_random_pseudo_bytes($ivlen);
		$ciphertext_raw 			= openssl_encrypt($jsonToken, self::$_cypher, $key, $options=OPENSSL_RAW_DATA, $iv);
		$hmac 						= hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
		$ciphertext 				= base64_encode( $iv.$hmac.$ciphertext_raw );
		
		return $ciphertext;
	}
	
	public static function getDecryptedToken($ciphertext, $key)
	{
		$c 					= base64_decode($ciphertext);
		$ivlen 				= openssl_cipher_iv_length(self::$_cypher);
		$iv 				= substr($c, 0, $ivlen);
		$hmac 				= substr($c, $ivlen, $sha2len=32);
		$ciphertext_raw 	= substr($c, $ivlen+$sha2len);
		$decodedJson		= openssl_decrypt($ciphertext_raw, self::$_cypher, $key, $options=OPENSSL_RAW_DATA, $iv);
		$calcmac 			= hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
		
		if (hash_equals($hmac, $calcmac))//PHP 5.6+ timing attack safe comparison
		{
			$jsonToken 		= json_decode($decodedJson, true);
			if(!empty($jsonToken))
			{
				return new AccessToken($jsonToken);
			}
		}
		
		return false;
	}
	
	public static function getActivityDate($timestamp)
	{
		$date = date('M d, Y', strtotime($timestamp));
		
		if($date == date('M d, Y')) 
		{
			$date = JText::_('COM_CJFIT_TODAY');
		}
		else if($date == date('M d, Y', time() - (24 * 60 * 60))) 
		{
			$date = JText::_('COM_CJFIT_YESTERDAY');
		}
		else if($date == date('M d, Y', time() - (48 * 60 * 60)))
		{
			$date = date('l', strtotime($timestamp));
		}
		else if($date == date('M d, Y', time() - (72 * 60 * 60)))
		{
			$date = date('l', strtotime($timestamp));
		}
		
		return $date;
	}
}