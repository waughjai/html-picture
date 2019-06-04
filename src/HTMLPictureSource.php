<?php

declare( strict_types = 1 );
namespace WaughJ\HTMLPicture;

use WaughJ\FileLoader\FileLoader;
use WaughJ\FileLoader\MissingFileException;
use WaughJ\HTMLAttributeList\HTMLAttributeList;

class HTMLPictureSource
{
	public static function generate( string $base, string $ext, int $img_width, int $img_height, string $media = null, FileLoader $loader = null, array $other_attributes = [] ) : HTMLPictureSource
	{
		if ( $media === null )
		{
			$media = "(max-width:{$img_width}px)";
		}
		$local = "{$base}-{$img_width}x{$img_height}.{$ext}";
		$show_version = ( bool )( $other_attributes[ 'show-version' ] ?? true ); // Default true ’less set.
		unset( $other_attributes[ 'show-version' ] ); // Make sure we don't keep this when we convert to HTML attributes.
		try
		{
			$srcset = ( $loader === null )
				? $local
				: (
					$show_version
					? $loader->getSourceWithVersion( $local )
					: $loader->getSource( $local )
				);
			return new HTMLPictureSource( $srcset, $media, $other_attributes );
		}
		catch ( MissingFileException $e )
		{
			$srcset = $loader->getSource( $local );
			throw new MissingFileException( $e->getFilename(), new HTMLPictureSource( $srcset, $media, $other_attributes ) );
		}
	}

	public function __construct( string $srcset, string $media, array $other_attributes = [] )
	{
		$other_attributes[ 'srcset' ] = $srcset;
		$other_attributes[ 'media' ] = $media;
		$this->attributes = new HTMLAttributeList( $other_attributes );
	}

	public function __toString()
	{
		return $this->getHTML();
	}

	public function getHTML() : string
	{
		return "<source{$this->attributes->getAttributesText()}>";
	}

	public function getSrcSet() : string
	{
		return $this->attributes->getAttributeValue( 'srcset' );
	}

	private $attributes;
}
