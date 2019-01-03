<?php

declare( strict_types = 1 );
namespace WaughJ\HTMLPicture
{
	use WaughJ\FileLoader\FileLoader;
	use WaughJ\HTMLAttributeList\HTMLAttributeList;
	use WaughJ\HTMLImage\HTMLImage;
	use WaughJ\VerifiedArgumentsSameType\VerifiedArgumentsSameType;
	use function WaughJ\TestHashItem\TestHashItemExists;

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
				$sizes = new HTMLPictureSizeList( $sizes );
				$loader = self::setupLoader( $other_attributes );
				$other_attributes = new VerifiedArgumentsSameType( $other_attributes, self::DEFAULT_ATTRIBUTES );
				$sources = self::generateSources( $sizes, $source_root, $extension, $loader, $other_attributes->get( 'source-attributes' ) );
				$fallback_image = new HTMLImage( $sources[ 0 ]->getSrcSet(), null, $other_attributes->get( 'img-attributes' ) );
				$picture_attributes = $other_attributes->get( 'picture-attributes' );
				return new HTMLPicture( $fallback_image, $sources, $picture_attributes );
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

			private static function generateSources( HTMLPictureSizeList $sizes, string $base, string $ext, FileLoader $loader, array $attributes  ) : array
			{
				return $sizes->forEach
				(
					function( HTMLPictureSize $size ) use ( $base, $ext, $loader, $attributes, $sizes )
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
						return HTMLPictureSource::generate( $base, $ext, $size->getWidth(), $size->getHeight(), $media, $loader, $attributes );
					}
				);
			}

			private static function setupLoader( array $other_attributes ) : FileLoader
			{
				$loader = TestHashItemExists( $other_attributes, 'loader', null );
				if ( is_array( $loader ) )
				{
					$loader = new FileLoader( $loader );
				}
				else if ( !is_a( $loader, FileLoader::class ) )
				{
					$loader = new FileLoader();
				}
				return $loader;
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
}
