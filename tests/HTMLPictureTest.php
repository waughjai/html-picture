<?php

use PHPUnit\Framework\TestCase;
use WaughJ\HTMLPicture\HTMLPicture;
use WaughJ\HTMLPicture\HTMLPictureSource;
use WaughJ\HTMLPicture\HTMLPictureSize;
use WaughJ\HTMLPicture\HTMLPictureSizeList;
use WaughJ\FileLoader\FileLoader;

class HTMLPictureTest extends TestCase
{
	public function testPictureHTML()
	{
		$picture = HTMLPicture::generate( 'photo', 'jpg', [ [ 'w' => 480, 'h' => '320' ], [ 'w' => 800, 'h' => 600 ], [ 'w' => '1200', 'h' => 800 ] ] );
		$this->assertContains( '<picture>', $picture->getHTML() );
		$this->assertContains( '<img src="photo-480x320.jpg" alt="" />', $picture->getHTML() );
		$this->assertContains( '<source srcset="photo-800x600.jpg" media="(max-width:800px)">', $picture->getHTML() );
	}

	public function testPictureHTMLStringSizes()
	{
		$picture = HTMLPicture::generate( 'photo', 'jpg', '480w 320h, 800w 600h, 1200w 800h' );
		$this->assertContains( '<picture>', $picture->getHTML() );
		$this->assertContains( '<img src="photo-480x320.jpg" alt="" />', $picture->getHTML() );
		$this->assertContains( '<source srcset="photo-800x600.jpg" media="(max-width:800px)">', $picture->getHTML() );
	}

	public function testPictureHTMLWithAttributes()
	{
		$picture = HTMLPicture::generate
		(
			'photo',
			'jpg',
			[
				[ 'w' => 480, 'h' => '320' ],
				[ 'w' => 800, 'h' => 600 ],
				[ 'w' => '1200', 'h' => 800 ]
			],
			[
				'img-attributes' => [ 'class' => 'center-img' ],
				'picture-attributes' => [ 'id' => 'slider-42' ],
				'source-attributes' => [ 'class' => 'source-item' ]
			]
		);
		$this->assertContains( '<picture id="slider-42">', $picture->getHTML() );
		$this->assertContains( ' src="photo-480x320.jpg"', $picture->getHTML() );
		$this->assertContains( ' class="center-img"', $picture->getHTML() );
		$this->assertContains( '<source class="source-item" srcset="photo-800x600.jpg" media="(max-width:800px)">', $picture->getHTML() );
		$this->assertContains( 'id="slider-42"', $picture->getPictureAttributes()->getAttributesText() );
	}

	public function testPictureHTMLWithFileLoader()
	{
		$picture = HTMLPicture::generate
		(
			'photo',
			'jpg',
			[
				[ 'w' => 480, 'h' => '320' ],
				[ 'w' => 800, 'h' => 600 ],
				[ 'w' => '1200', 'h' => 800 ]
			],
			[
				'loader' => [ 'directory-url' => 'https://mywebsite.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests' ]
			]
		);
		$this->assertContains( '<picture>', $picture->getHTML() );
		$this->assertContains( '<img src="https://mywebsite.com/tests/photo-480x320.jpg?m=', $picture->getHTML() );
		$this->assertContains( '<source srcset="https://mywebsite.com/tests/photo-800x600.jpg?m=', $picture->getHTML() );
	}

	public function testHTMLPictureSize()
	{
		$picture_size = new HTMLPictureSize( 200, 450, 7 );
		$this->assertEquals( $picture_size->getIndex(), 7 );
		$this->assertEquals( $picture_size->getWidth(), 200 );
		$this->assertEquals( $picture_size->getHeight(), 450 );
	}

	public function testHTMLPictureSizeList()
	{
		$string = '480w 320h, 800w 600h, 1200w 800h';
		$picture_size_list = new HTMLPictureSizeList( $string );
		$this->assertEquals( $picture_size_list->getCount(), 3 );
		$this->assertEquals( $picture_size_list->getSmallestSize(), new HTMLPictureSize( 480, 320, 0 ) );
		$this->assertEquals( $picture_size_list->getNextSize( $picture_size_list->getSmallestSize() ), new HTMLPictureSize( 800, 600, 1 ) );
		$picture_size_list2 = new HTMLPictureSizeList( [ [ 'w' => 480, 'h' => '320' ], [ 'w' => 800, 'h' => 600 ], [ 'w' => '1200', 'h' => 800 ] ] );
		$this->assertEquals( $picture_size_list2->getCount(), 3 );
		$this->assertEquals( $picture_size_list2->getSmallestSize(), new HTMLPictureSize( 480, 320, 0 ) );
		$this->assertEquals( $picture_size_list2->getNextSize( $picture_size_list2->getSmallestSize() ), new HTMLPictureSize( 800, 600, 1 ) );
		$picture_size_list3 = new HTMLPictureSizeList( $picture_size_list2 );
		$this->assertEquals( $picture_size_list3->getCount(), 3 );
		$this->assertEquals( $picture_size_list3->getSmallestSize(), new HTMLPictureSize( 480, 320, 0 ) );
		$this->assertEquals( $picture_size_list3->getNextSize( $picture_size_list3->getSmallestSize() ), new HTMLPictureSize( 800, 600, 1 ) );
	}

	public function testPictureSource()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://mywebsite.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests' ]);
		$source2 = HTMLPictureSource::generate( 'photo', 'jpg', 480, 320, 480 , $loader );
		$this->assertContains( '<source srcset="https://mywebsite.com/tests/photo-480x320.jpg?m=1543530332" media="(max-width:480px)">', $source2->getHTML() );
	}

	public function testFallbackImage()
	{
		$picture = HTMLPicture::generate
		(
			'photo',
			'jpg',
			[
				[ 'w' => 480, 'h' => '320' ],
				[ 'w' => 800, 'h' => 600 ],
				[ 'w' => '1200', 'h' => 800 ]
			],
			[
				'loader' => [ 'directory-url' => 'https://mywebsite.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests' ]
			]
		);
		$this->assertContains( '<img src="https://mywebsite.com/tests/photo-480x320.jpg?m=', $picture->getFallbackImage()->getHTML() );
		$this->assertNotContains( ' class="new-picture"', $picture->getFallbackImage()->getHTML() );
		$this->assertNotContains( ' id="first-picture"', $picture->getFallbackImage()->getHTML() );
		$picture = $picture->changeFallbackImage( $picture->getFallbackImage()->addToClass( 'new-picture' )->setAttribute( 'id', 'first-picture' ) );
		$this->assertContains( ' class="new-picture"', $picture->getFallbackImage()->getHTML() );
		$this->assertContains( ' id="first-picture"', $picture->getFallbackImage()->getHTML() );
	}
}
