<?php

declare( strict_types = 1 );
namespace WaughJ\HTMLPicture;

class HTMLPictureSizeList
{
	//
	//  PUBLIC
	//
	/////////////////////////////////////////////////////////

		public function __construct( $sizes )
		{
			$this->sizes = self::configureSizesByType( $sizes );
			if ( $this->sizes === null )
			{
				throw new \InvalidArgumentException( "Invalid argument o' type \"" . gettype( $sizes ) . "\" given to HTMLPictureSizeList constructor." );
			}
			$this->count = count( $this->sizes );
		}

		public function getLastIndex() : int
		{
			return $this->count - 1;
		}

		public function getSmallestSize() : HTMLPictureSize
		{
			return $this->sizes[ 0 ];
		}

		public function getPreviousSize( HTMLPictureSize $size )
		{
			return ( $size->getIndex() <= 0 ) ? null : $this->sizes[ $size->getIndex() - 1 ];
		}

		public function getList() : array
		{
			return $this->sizes;
		}



	//
	//  PRIVATE
	//
	/////////////////////////////////////////////////////////

		private static function getSizesFromString( string $sizes_string ) : array
		{
			$final_sizes_list = [];
			if ( !empty( $sizes_string ) )
			{
				$sizes_list = explode( ', ', $sizes_string );
				$i = 0;
				foreach ( $sizes_list as $size )
				{
					$size_items = explode( ' ', $size );
					if ( count( $size_items ) < 2 )
					{
						throw new \MalformedSizeStringException( $size_string );
					}
					$w = str_replace( 'w', '', $size_items[ 0 ] );
					$h = str_replace( 'h', '', $size_items[ 1 ] );
					$final_sizes_list[] = new HTMLPictureSize( intval( $w ), intval( $h ), $i );
					$i++;
				}
			}
			return $final_sizes_list;
		}

		private static function getSizesFromArray( array $sizes ) : array
		{
			$list = [];
			$i = 0;
			foreach ( $sizes as $size )
			{
				$list[] = new HTMLPictureSize( intval( $size[ 'w' ] ), intval( $size[ 'h' ] ), $i );
				$i++;
			}
			return $list;
		}

		// TODO: If WordPress e'er allows PHP 7.1 for plugins, add : ?array as mandatory return.
		private static function configureSizesByType( $sizes )
		{
			return
			   ( is_string( $sizes ) )                      ? self::getSizesFromString( $sizes )
			: (( is_array( $sizes ) )                       ? self::getSizesFromArray( $sizes )
			:  ( is_a( $sizes, HTMLPictureSizeList::class ) ? $sizes->getList()
			:                                                 null));
		}

		private $sizes;
		private $count;
}
