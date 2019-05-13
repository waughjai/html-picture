<?php

declare( strict_types = 1 );
namespace WaughJ\HTMLPicture
{
	use WaughJ\HTMLAttributeList\HTMLAttributeList;
	use WaughJ\FileLoader\FileLoader;
	use WaughJ\FileLoader\MissingFileException;
	use function WaughJ\TestHashItem\TestHashItemIsTrue;

	class HTMLPictureSource
	{
		public static function generate( string $base, string $ext, int $img_width, int $img_height, string $media = null, FileLoader $loader = null, array $other_attributes = [] ) : HTMLPictureSource
		{
			if ( $media === null )
			{
				$media = "(max-width:{$img_width}px)";
			}
			$local = "{$base}-{$img_width}x{$img_height}.{$ext}";

			$srcset = null;
			try
			{
				$srcset = ( $loader === null )
					? $local
					: (
						( array_key_exists( 'show-version', $other_attributes ) && !$other_attributes[ 'show-version' ] )
						? $loader->getSource( $local )
						: $loader->getSourceWithVersion( $local )
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
