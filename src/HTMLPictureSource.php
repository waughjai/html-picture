<?php

declare( strict_types = 1 );
namespace WaughJ\HTMLPicture
{
	use WaughJ\HTMLAttributeList\HTMLAttributeList;
	use WaughJ\FileLoader\FileLoader;

	class HTMLPictureSource
	{
		public static function generate( string $base, string $ext, int $img_width, int $img_height, int $max_width = -1, FileLoader $loader = null, array $other_attributes = [] ) : HTMLPictureSource
		{
			if ( $max_width < 0 )
			{
				$max_width = $img_width;
			}
			$local = "{$base}-{$img_width}x{$img_height}.{$ext}";
			$srcset = ( $loader === null ) ? $local : $loader->getSourceWithVersion( $local );
			$media = "(max-width:{$max_width}px)";
			return new HTMLPictureSource( $srcset, $media, $other_attributes );
		}

		public function __construct( string $srcset, string $media, array $other_attributes = [] )
		{
			$other_attributes[ 'srcset' ] = $srcset;
			$other_attributes[ 'media' ] = $media;
			$this->attributes = new HTMLAttributeList( $other_attributes );
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
}
