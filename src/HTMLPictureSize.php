<?php

declare( strict_types = 1 );
namespace WaughJ\HTMLPicture;

class HTMLPictureSize
{
	public function __construct( int $width, int $height, int $index )
	{
		$this->width = $width;
		$this->height = $height;
		$this->index = $index;
	}

	public function getWidth() : int
	{
		return $this->width;
	}

	public function getHeight() : int
	{
		return $this->height;
	}

	public function getIndex() : int
	{
		return $this->index;
	}

	private $width;
	private $height;
	private $index;
}
