<?php
/**
 * @category	Library
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

require_once (COMMUNITY_COM_PATH.DS.'models'.DS.'videos.php');

/**
 * Class to manipulate data from vimeo video
 * 	 	
 * @access	public  	 
 */
class CTableVideoVimeo extends CVideoProvider
{
	var $xmlContent = null;
	var $url 		= '';
	var $videoId	= '';
	/**
	 * Return feedUrl of the video
	 */
	public function getFeedUrl()
	{
		return 'http://vimeo.com/api/v2/video/' .$this->videoId.'.xml';
	}
	
                
	
	/*
	 * Return true if successfully connect to remote video provider
	 * and the video is valid
	 */	
	public function isValid()
	{
		if ( !parent::isValid())
		{
			return false;
		}

		$parser = JFactory::getXMLParser('Simple');
		$parser->loadString($this->xmlContent);
		
		$videoElement = $parser->document;
			
		if( empty($videoElement) )
		{
			$this->setError( JText::_('COM_COMMUNITY_VIDEOS_FETCHING_VIDEO_ERROR') );
			return false;
		}	
		
		//get vimeo error
		$elementError	= $videoElement->getElementByPath('error/message');
		if($elementError){
			$vimeoError = $elementError->data();
			$vimeoError	= JText::_('COM_COMMUNITY_VIDEOS_FETCHING_VIDEO_ERROR') . ' ' . $vimeoError;
			$this->setError( $vimeoError );
			return false;
		}

		//get Video title
		$element = $videoElement->getElementByPath('video/title');
		$this->title = $element->data();
		
		//Get Video duration
		$element = $videoElement->getElementByPath('video/duration');
		$this->duration = $element->data();
		
		//Get Video thumbnail
		$element = $videoElement->getElementByPath('video/thumbnail_large');
		$this->thumbnail = $element->data();

		//Get Video description
		$element = $videoElement->getElementByPath('video/description');
		$this->description = $element->data();
		
		return true;
	}

	
	/**
	 * Extract Vimeo video id from the video url submitted by the user
	 * 	 	
	 * @access	public
	 * @param	video url
	 * @returns videoid	 
	 */
	public function getId()
	{
	    $pattern = '/vimeo.com\/(hd#)?(channels\/[a-zA-Z0-9]*#)?(\d*)/';
	    preg_match($pattern, $this->url, $match);

            if(!empty($match[3]))
            {
                return $match[3];
            }
            else
            {
               return !empty( $match[2] ) ? $match[2] : null;
            }

	}
	
	/**
	 * Return the video provider's name
	 * 
	 */
	public function getType()
	{
		return 'vimeo';
	}
	
	public function getTitle()
	{
		$title	= '';
		$title	= $this->title;
		
		return $title;
	}
	
	/**
	 * 
	 * @param $videoId
	 * @return unknown_type
	 */
	public function getDescription()
	{
		$description	= '';
		$description = $this->description;
		return $description;
	}
	
	public function getDuration()
	{
		$duration	= '';
		$duration	= $this->duration;
		
		return $duration;
	}
	
	/**
	 * 
	 * @param $videoId
	 * @return unknown_type
	 */
	public function getThumbnail()
	{ 
		$thumbnail	= '';
		$thumbnail	= $this->thumbnail;
		
		return $thumbnail;
	}
	
	/**
	 * 
	 * 
	 * @return $embedCode specific embeded code to play the video
	 */
	public function getViewHTML($videoId, $videoWidth, $videoHeight)
	{
		if (!$videoId)
		{
			$videoId	= $this->videoId;
		}
		$embedCode ="<object width=\"".$videoWidth."\" height=\"".$videoHeight."\"><param name=\"wmode\" value=\"transparent\" /><param name=\"allowfullscreen\" value=\"true\" /><param name=\"allowscriptaccess\" value=\"always\" /><param name=\"movie\" value=\"http://vimeo.com/moogaloop.swf?clip_id=".$videoId."&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=0&amp;show_portrait=0&amp;color=&amp;fullscreen=1\" /><embed src=\"http://vimeo.com/moogaloop.swf?clip_id=" .$videoId. "&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=0&amp;show_portrait=0&amp;color=&amp;fullscreen=1\" type=\"application/x-shockwave-flash\" allowfullscreen=\"true\" allowscriptaccess=\"always\" width=\"".$videoWidth."\" height=\"".$videoHeight."\" wmode=\"transparent\" ></embed></object>";

                return $embedCode;
	}
}
