<?php

declare( strict_types = 1 );
namespace WaughJ\HTMLPicture;

class MalformedSizeStringException extends \RuntimeException
{
	public function __construct( string $size_string )
	{
		parent::__construct( "Malformed size string given to HTMLPictureSizeList constructor: {$sizes_string}. All sizes must have \"w\" & \"h\" set." );
	}
}
