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

			public function __construct( string $source_root, string $extension, $sizes, array $other_attributes = [] )
			{
				$this->source_root = $source_root;
				$this->extension = $extension;
				$this->sizes = new PictureSizeList( $sizes );
				$this->loader = self::setupLoader( $other_attributes, $extension );

				$other_attributes = new VerifiedArgumentsSameType( $other_attributes, self::DEFAULT_ATTRIBUTES );
				$this->img_attributes = $other_attributes->get( 'img-attributes' );
				$this->picture_attributes = new HTMLAttributeList( $other_attributes->get( 'picture-attributes' ) );
				$this->source_attributes = new HTMLAttributeList( $other_attributes->get( 'source-attributes' ) );
			}

			public function __toString()
			{
				return $this->getHTML();
			}

			public function getHTML() : string
			{
				return '<picture' . $this->picture_attributes->getAttributesText() . '>' .
					$this->getSources() .
					$this->getFallbackImage() .
					'</picture>';
			}

			public function print() : void
			{
				echo $this;
			}

			public function getSingleSource( PictureSize $size ) : string
			{
				return '<source' . $this->source_attributes->getAttributesText() . ' srcset="' . $this->loader->getSourceWithVersion( $this->getSourceFromSize( $size ) ) . '"' . $this->getSizeMedia( $size ) . '>';
			}



		//
		//  PRIVATE
		//
		/////////////////////////////////////////////////////////

			private function getSources() : string
			{
				return $this->sizes->forEach([ $this, 'getSingleSource' ]);
			}

			private function getFallbackImage() : string
			{
				$image = new HTMLImage( $this->getSmallestSource(), $this->loader, $this->img_attributes );
				return $image->getHTML();
			}

			private function getSizeMedia( PictureSize $size ) : string
			{
				return ( $size->getIndex() < $this->sizes->getLastIndex() )
					? ' media="(max-width:' . $size->getWidth() . 'px)"'
					: '';
			}

			private function getSmallestSource() : string
			{
				return $this->getSourceFromSize( $this->sizes->getSmallestSize() );
			}

			private function getSourceFromSize( PictureSize $size ) : string
			{
				return $this->source_root . '-' . $size->getWidth() . 'x' . $size->getHeight();
			}

			private static function setupLoader( array $other_attributes, string $extension ) : FileLoader
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
				return $loader->changeExtension( $extension );
			}

			private $source_root;
			private $extension;
			private $sizes;
			private $img_attributes;
			private $picture_attributes;
			private $source_attributes;
			private $loader;

			const DEFAULT_ATTRIBUTES =
			[
				'img-attributes' => [],
				'picture-attributes' => [],
				'source-attributes' => []
			];
	}
}
