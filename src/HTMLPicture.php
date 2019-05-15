<?php

declare( strict_types = 1 );
namespace WaughJ\HTMLPicture;

use WaughJ\FileLoader\FileLoader;
use WaughJ\FileLoader\MissingFileException;
use WaughJ\HTMLAttributeList\HTMLAttributeList;
use WaughJ\HTMLImage\HTMLImage;
use WaughJ\VerifiedArgumentsSameType\VerifiedArgumentsSameType;
use function WaughJ\TestHashItem\TestHashItemArray;
use function WaughJ\TestHashItem\TestHashItemExists;
use function WaughJ\TestHashItem\TestHashItemIsTrue;

class HTMLPicture
{
	//
	//  PUBLIC
	//
	/////////////////////////////////////////////////////////

		public function __construct( HTMLImage $fallback_image, array $sources, array $picture_attributes = [] )
		{
			$this->fallback_image = $fallback_image;
			$this->sources = $sources;
			$this->picture_attributes = new HTMLAttributeList( $picture_attributes );
		}

		public static function generate( string $source_root, string $extension, $sizes, array $other_attributes = [] )
		{
			$loader = self::setupLoader( $other_attributes );
			$src_attributes = self::configureSrcAttributes( $other_attributes );
			$other_attributes = new VerifiedArgumentsSameType( $other_attributes, self::DEFAULT_ATTRIBUTES );

			// If generateSources finds exception, handle & recreate exception so we keep full fallback code
			// & can still throw an exception, in case outside code needs to know 'bout it.
			$exception = null;
			try
			{
				$sources = self::generateSources( $sizes, $source_root, $extension, $loader, $src_attributes );
			}
			catch ( MissingFileException $e )
			{
				$exception = $e;
				$sources = $e->getFallbackContent();
			}
			finally
			{
				$fallback_image = new HTMLImage( $sources[ 0 ]->getSrcSet(), null, $other_attributes->get( 'img-attributes' ) );
				$picture_attributes = $other_attributes->get( 'picture-attributes' );
				$content = new HTMLPicture( $fallback_image, $sources, $picture_attributes );

				if ( $exception !== null )
				{
					throw new MissingFileException( $exception->getFilename(), $content );
				}

				return $content;
			}
		}

		public function __toString()
		{
			return $this->getHTML();
		}

		public function getHTML() : string
		{
			return '<picture' . $this->picture_attributes->getAttributesText() . '>' .
					$this->getSourcesHTML() .
					$this->fallback_image->getHTML() .
				'</picture>';
		}

		public function getFallbackImage() : HTMLImage
		{
			return $this->fallback_image;
		}

		public function getSources() : array
		{
			return $this->sources;
		}

		public function getPictureAttributes() : HTMLAttributeList
		{
			return $this->picture_attributes;
		}

		public function print() : void
		{
			echo $this;
		}

		public function changeFallbackImage( HTMLImage $image ) : HTMLPicture
		{
			$new_picture = clone $this;
			$new_picture->fallback_image = $image;
			return $new_picture;
		}



	//
	//  PRIVATE
	//
	/////////////////////////////////////////////////////////

		private function getSourcesHTML() : string
		{
			$html = '';
			foreach ( $this->sources as $source )
			{
				$html .= $source->getHTML();
			}
			return $html;
		}

		private static function generateSources( $sizes, string $base, string $ext, FileLoader $loader, array $attributes  ) : array
		{
			$sources = [];
			$missing_files = []; // For logging missing file exceptions.
			$sizes = new HTMLPictureSizeList( $sizes );

			foreach ( $sizes->getList() as $size )
			{
				$is_last_source = $size->getIndex() === $sizes->getLastIndex();
				if ( $is_last_source )
				{
					$min_width = $sizes->getPreviousSize( $size )->getWidth() + 1;
					$media = "(min-width:{$min_width}px)";
				}
				else
				{
					$media = "(max-width:{$size->getWidth()}px)";
				}

				try
				{
					$sources[] = HTMLPictureSource::generate( $base, $ext, $size->getWidth(), $size->getHeight(), $media, $loader, $attributes );
				}
				catch ( MissingFileException $e )
				{
					$sources[] = $e->getFallbackContent();
					$missing_files[] = $e->getFilename();
				}
			}

			if ( !empty( $missing_files ) )
			{
				throw new MissingFileException( $missing_files, $sources );
			}
			return $sources;
		}

		private static function setupLoader( array $other_attributes ) : FileLoader
		{
			$loader = TestHashItemExists( $other_attributes, 'loader', null );
			return ( is_array( $loader ) )
				? new FileLoader( $loader )
				: ( ( !is_a( $loader, FileLoader::class ) )
					? new FileLoader()
					: $loader );
		}

		private static function configureSrcAttributes( array $attributes ) : array
		{
			$src_attributes = TestHashItemArray( $attributes, 'source-attributes', [] );
			if ( array_key_exists( 'show-version', $attributes ) && !$attributes[ 'show-version' ] )
			{
				$src_attributes[ 'show-version' ] = false;
			}
			return $src_attributes;
		}

		private $picture_attributes;
		private $sources;
		private $fallback_img;

		const DEFAULT_ATTRIBUTES =
		[
			'img-attributes' => [],
			'picture-attributes' => [],
			'source-attributes' => []
		];
}
