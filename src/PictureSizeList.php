<?php

declare( strict_types = 1 );
namespace WaughJ\HTMLPicture
{
	class PictureSizeList
	{
		public function __construct( $sizes )
		{
			if ( is_string( $sizes ) )
			{
				$this->sizes = self::getSizesFromString( $sizes );
			}
			else if ( is_array( $sizes ) )
			{
				$this->sizes = self::getSizesFromArray( $sizes );
			}
			else if ( is_a( $sizes, PictureSizeList::class ) )
			{
				$this->sizes = $sizes->getList();
			}
			else
			{
				throw \Exception( "Invalid argument o' type \"" . gettype( $sizes ) . "\" given to PictureSizeList constructor." );
			}
			$this->count = count( $this->sizes );
		}

		public function getCount() : int
		{
			return $this->count;
		}

		public function getLastIndex() : int
		{
			return $this->getCount() - 1;
		}

		public function getSmallestSize() : PictureSize
		{
			return $this->sizes[ 0 ];
		}

		public function getNextSize( PictureSize $size )
		{
			return ( $size->getIndex() >= $this->getLastIndex() ) ? null : $this->sizes[ $size->getIndex() + 1 ];
		}

		public function getList() : array
		{
			return $this->sizes;
		}

		public function forEach( callable $function ) : string
		{
			$text = '';
			foreach ( $this->sizes as $size )
			{
				$text .= $function( $size );
			}
			return $text;
		}

		private static function getSizesFromString( string $sizes_string ) : array
		{
			$final_sizes_list = [];
			if ( $sizes_string )
			{
				$sizes_list = explode( ', ', $sizes_string );
				$i = 0;
				foreach ( $sizes_list as $size )
				{
					$size_items = explode( ' ', $size );
					assert( count( $size_items ) >= 2 );
					$w = str_replace( 'w', '', $size_items[ 0 ] );
					$h = str_replace( 'h', '', $size_items[ 1 ] );
					array_push( $final_sizes_list, new PictureSize( intval( $w ), intval( $h ), $i ) );
					$i++;
				}
			}
			return $final_sizes_list;
		}

		private static function getSizesFromArray( array $sizes ) : array
		{
			$new_list = [];
			$i = 0;
			foreach ( $sizes as $size )
			{
				array_push( $new_list, new PictureSize( intval( $size[ 'w' ] ), intval( $size[ 'h' ] ), $i ) );
				$i++;
			}
			return $new_list;
		}

		private $sizes;
		private $count;
	}
}
