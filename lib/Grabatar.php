<?php
/**
 * Grabatar - PHP Wrapper for the Gravatar API
 * 
 * Copyright (c) 2012  Andrew Lim, Lim Industries. All rights reserved.
 *
 * <pre>
 *   Permission is hereby granted, free of charge, to any person obtaining 
 *   a copy of this software and associated documentation files (the 'Software'), 
 *   to deal in the Software without restriction, including without limitation 
 *   the rights to use, copy, modify, merge, publish, distribute, sublicense, 
 *   and/or sell copies of the Software, and to permit persons to whom the 
 *   Software is furnished to do so, subject to the following conditions:
 *   
 *   The above copyright notice and this permission notice shall be included in 
 *   all copies or substantial portions of the Software.
 *   
 *   THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 *   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN 
 *   THE SOFTWARE.
 * </pre>
 * 
 * With an object of this class you may lookup a Gravatar and cache the images locally. 
 * 
 * Example:
 * <code>
 * $gravatar = Grabatar::getInstance();
 * $avatar = $gravatar->grab("test@test.com");
 * </code>
 * 
 * By default caching is not enabled, to enable it you wil have to set the cache
 * directory.
 * 
 * Example:
 * <code>
 * $gravatar->setCacheDir("lib/cache/");
 * </code>
 * 
 * @todo set expiry date, set SSL, handle 404, 
 * @copyright Andrew Lim 2012
 * @author Andrew Lim <hiya@andrew-lim.net>
 * @version 1.0
 */

 
/**
 * Class Grabatar
 * Provide methods to fetch gravatars and cache them
 */
class Grabatar
{
	const CACHE_PREFIX = "GRAVATAR_CACHE_";		// prefix for cache-files. 
	
	const SERVICE_BASE_URL = "http://www.gravatar.com/avatar/";
	const SERVICE_BASE_URL_SSL = "https://secure.gravatar.com//avatar/";
	
	private $_cache_dir = null;
	private $_use_cache = false;
	
	private $_size = 50;
	private $_rating = "g";
	private $_default = "identicon";
	
	# Holds instance
	private static $_instance;
	
	# Singelton-patterned class. No need to make an instance of this object 
	# outside it self. 
	private function __construct()
	{
		
	}
	
	/**
	 * Get new instance of this object.
	 */
	public static function getInstance()
	{
		if (!isset(self::$_instance))
		{
			$class = __CLASS__;
			self::$_instance = new $class;
		}
		
		return self::$_instance;
	}
	
	/**
	 * Sets the cache directory and sets use_cache to true
	 * 
	 * @param string $dir
	 */
	public function setCacheDir($dir)
	{
		$this->_cache_dir = $dir;
		$this->_use_cache = true;
	}
	
	/**
	 * Sets the default image parameters
	 * 
	 * @param int $size
	 * @param string $rating
	 * @param string $default
	 */
	public function setDefaults($size = NULL, $rating = NULL, $default = NULL)
	{
		if($size)
		{
			$this->_size = $size;
		}
		if($rating)
		{
			$this->_rating = $rating;
		}
		if($default)
		{
			$this->_default = $default;
		}
	}
	
	/**
	 * Returns the path for an image which can be used directly in an <img> tag
	 * 
	 * @param string $email
	 * @param int $size
	 * @param string $rating
	 * @param string $default
	 * @return string
	 * 
	 * @deprecated
	 */
	public function getGravatar($email, $size = NULL, $rating = NULL, $default = NULL)
	{
		# override defaults
		# if empty get default size
		if(!$size)
		{
			$size = $this->_size;
		}
		
		# if empty get default rating
		if(!$rating)
		{
			$rating = $this->_rating;
		}
		
		# if empty get default default
		if(!$default)
		{
			$default = $this->_default;
		}
		
		# get paths
		$strRemotePath = $this->getGravatarPath($email, $size, $rating, $default);
		$strLocalPath = $this->_cache_dir . self::CACHE_PREFIX . md5($strRemotePath) . '.jpg';
		
		# check if expired
		
		# check if the cached file exists
		if (file_exists($strLocalPath) && $this->_use_cache)
		{
			$gravtarPath =  $strLocalPath;
		}
		else
		{
			$this->cacheGravatar($email, $size, $rating, $default);
			
			$gravtarPath = $strRemotePath;
		}
 		
		return $gravtarPath;
	}
	
	/**
	 * Returns the path for an image which can be used directly in an <img> tag
	 * 
	 * @param string $email
	 * @param int $size
	 * @param string $rating
	 * @param string $default
	 * @return string
	 */
	public function grab($email, $size = NULL, $rating = NULL, $default = NULL)
	{
		# override defaults
		# if empty get default size
		if(!$size)
		{
			$size = $this->_size;
		}
		
		# if empty get default rating
		if(!$rating)
		{
			$rating = $this->_rating;
		}
		
		# if empty get default default
		if(!$default)
		{
			$default = $this->_default;
		}
		
		# get paths
		$strRemotePath = $this->getGravatarPath($email, $size, $rating, $default);
		$strLocalPath = $this->_cache_dir . self::CACHE_PREFIX . md5($strRemotePath) . '.jpg';
		
		# check if expired
		
		# check if the cached file exists
		if (file_exists($strLocalPath) && $this->_use_cache)
		{
			$gravtarPath =  $strLocalPath;
		}
		else
		{
			$this->cacheGravatar($email, $size, $rating, $default);
			
			$gravtarPath = $strRemotePath;
		}
 		
		return $gravtarPath;
	}
    
    /**
	 * Saves the image to the cache directory
	 * 
	 * @param string $email
	 * @param int $size
	 * @param string $rating
	 * @param string $default
	 */
	private function cacheGravatar($email, $size = NULL, $rating = NULL, $default = NULL)
	{
		$strPath = self::SERVICE_BASE_URL . $this->generateHash($email);
		
		# set gravatar params
		$params = array(
						'r'		=> $rating,
						's'		=> $size,
						'd'		=> $default
						);

		$strRemotePath = $strPath . '?' . http_build_query($params, '', '&');

		$strLocalPath = $this->_cache_dir . self::CACHE_PREFIX . md5($strRemotePath) . '.jpg';
		
		$contents = @file_get_contents($strRemotePath);
		file_put_contents($strLocalPath, $contents);
	}
    
	/**
	 * Returns gravatar path for an image which can be used directly in an <img> tag
	 * 
	 * @param string $strEmail
	 * @param int $intSize
	 * @return string
	 */
	private function getGravatarPath($email, $size = NULL, $rating = NULL, $default = NULL)
	{
		$strPath = self::SERVICE_BASE_URL . $this->generateHash($email);
		
		# set gravatar params
		$params = array(
						'r'		=> $rating,
						's'		=> $size,
						'd'		=> $default
						);
		
		return $strPath . '?' . http_build_query($params, '', '&');
	}
	
	/**
	 * Return the hash based on the email address
	 * 
	 * @param string $email
	 * @return string
	 */
	private function generateHash($email)
	{
		$strEmail = trim($email);
		$strEmail = strtolower($strEmail);
		$strHash = md5($strEmail);
		return $strHash;
	}
}