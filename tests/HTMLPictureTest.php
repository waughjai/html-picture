<?php

use PHPUnit\Framework\TestCase;
use WaughJ\FileLoader\FileLoader;
use WaughJ\FileLoader\MissingFileException;
use WaughJ\HTMLPicture\HTMLPicture;
use WaughJ\HTMLPicture\HTMLPictureSource;
use WaughJ\HTMLPicture\HTMLPictureSize;
use WaughJ\HTMLPicture\HTMLPictureSizeList;
use WaughJ\HTMLPicture\MalformedSizeStringException;

class HTMLPictureTest extends TestCase
{
	public function testPictureHTML()
	{
		$picture = HTMLPicture::generate( 'photo', 'jpg', [ [ 'w' => 480, 'h' => '320' ], [ 'w' => 800, 'h' => 600 ], [ 'w' => '1200', 'h' => 800 ] ] );
		$this->assertStringContainsString( '<picture>', $picture->getHTML() );
		$this->assertStringContainsString( '<img src="photo-480x320.jpg" alt="" />', $picture->getHTML() );
		$this->assertStringContainsString( '<source srcset="photo-800x600.jpg" media="(max-width:800px)">', $picture->getHTML() );
	}

	public function testPictureHTMLStringSizes()
	{
		$picture = HTMLPicture::generate( 'photo', 'jpg', '480w 320h, 800w 600h, 1200w 800h' );
		$this->assertStringContainsString( '<picture>', $picture->getHTML() );
		$this->assertStringContainsString( '<img src="photo-480x320.jpg" alt="" />', $picture->getHTML() );
		$this->assertStringContainsString( '<source srcset="photo-800x600.jpg" media="(max-width:800px)">', $picture->getHTML() );
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
				'picture-attributes' => [ 'id' => 'slider-42', 'class' => 'pic' ],
				'source-attributes' => [ 'class' => 'source-item' ]
			]
		);
		$this->assertStringContainsString( ' src="photo-480x320.jpg"', $picture->getHTML() );
		$this->assertStringContainsString( ' class="center-img"', $picture->getHTML() );
		$this->assertStringContainsString( '<source class="source-item" srcset="photo-800x600.jpg" media="(max-width:800px)">', $picture->getHTML() );
		$this->assertStringContainsString( '<source class="source-item" srcset="photo-1200x800.jpg" media="(min-width:801px)">', $picture->getHTML() );
		$this->assertStringContainsString( ' id="slider-42"', $picture->getPictureAttributes()->getAttributesText() );
		$this->assertStringContainsString( ' class="pic"', $picture->getPictureAttributes()->getAttributesText() );
		$this->assertEquals( 'pic', $picture->getPictureAttributes()->getAttribute( 'class' )->getValue() );
		$this->assertEquals( 'slider-42', $picture->getPictureAttributes()->getAttribute( 'id' )->getValue() );
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
		$this->assertStringContainsString( '<picture>', $picture->getHTML() );
		$this->assertStringContainsString( '<img src="https://mywebsite.com/tests/photo-480x320.jpg?m=', $picture->getHTML() );
		$this->assertStringContainsString( '<source srcset="https://mywebsite.com/tests/photo-800x600.jpg?m=', $picture->getHTML() );
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
		$this->assertEquals( $picture_size_list->getSmallestSize(), new HTMLPictureSize( 480, 320, 0 ) );
		$picture_size_list2 = new HTMLPictureSizeList( [ [ 'w' => 480, 'h' => '320' ], [ 'w' => 800, 'h' => 600 ], [ 'w' => '1200', 'h' => 800 ] ] );
		$this->assertEquals( $picture_size_list2->getSmallestSize(), new HTMLPictureSize( 480, 320, 0 ) );
		$picture_size_list3 = new HTMLPictureSizeList( $picture_size_list2 );
		$this->assertEquals( $picture_size_list3->getSmallestSize(), new HTMLPictureSize( 480, 320, 0 ) );
		$this->expectException( \InvalidArgumentException::class );
		$picture_size_list = new HTMLPictureSizeList( 234 );
		$this->expectException( MalformedSizeStringException::class );
		$picture_size_list = new HTMLPictureSizeList( '480w 320h, 200w, 1200w, 800h' );
	}

	public function testPictureSource()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://mywebsite.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests' ]);
		$source2 = HTMLPictureSource::generate( 'photo', 'jpg', 480, 320, null , $loader );
		$this->assertStringContainsString( '<source srcset="https://mywebsite.com/tests/photo-480x320.jpg?m=1554999446" media="(max-width:480px)">', $source2->getHTML() );
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
		$this->assertStringContainsString( '<img src="https://mywebsite.com/tests/photo-480x320.jpg?m=', $picture->getFallbackImage()->getHTML() );
		$this->assertStringNotContainsString( ' class="new-picture"', $picture->getFallbackImage()->getHTML() );
		$this->assertStringNotContainsString( ' id="first-picture"', $picture->getFallbackImage()->getHTML() );
		$picture = $picture->changeFallbackImage( $picture->getFallbackImage()->addToClass( 'new-picture' )->setAttribute( 'id', 'first-picture' ) );
		$this->assertStringContainsString( ' class="new-picture"', $picture->getFallbackImage()->getHTML() );
		$this->assertStringContainsString( ' id="first-picture"', $picture->getFallbackImage()->getHTML() );
	}

	public function testPictureHTMLMissingImage()
	{
		$picture = null;
		try
		{
			$picture = HTMLPicture::generate
			(
				'iainthere',
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
		}
		catch ( MissingFileException $e )
		{
			$picture = $e->getFallbackContent();
		}

		$this->assertStringContainsString( '<picture>', $picture->getHTML() );
		$this->assertStringContainsString( '<img src="https://mywebsite.com/tests/iainthere-480x320.jpg"', $picture->getHTML() );
		$this->assertStringContainsString( '<source srcset="https://mywebsite.com/tests/iainthere-800x600.jpg"', $picture->getHTML() );

		$this->expectException( MissingFileException::class );
		$picture2 = HTMLPicture::generate
		(
			'iainthere',
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
	}

	public function testPictureHTMLPartiallyMissingImage()
	{
		$picture = null;
		try
		{
			$picture = HTMLPicture::generate
			(
				'somegone',
				'png',
				[
					[ 'w' => 480, 'h' => '320' ],
					[ 'w' => 800, 'h' => 600 ],
					[ 'w' => '1200', 'h' => 800 ]
				],
				[
					'loader' => [ 'directory-url' => 'https://mywebsite.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests' ]
				]
			);
		}
		catch ( MissingFileException $e )
		{
			$picture = $e->getFallbackContent();
		}

		$this->assertStringContainsString( '<picture>', $picture->getHTML() );
		$this->assertStringContainsString( '<img src="https://mywebsite.com/tests/somegone-480x320.png?m=', $picture->getHTML() );
		$this->assertStringContainsString( '<source srcset="https://mywebsite.com/tests/somegone-800x600.png"', $picture->getHTML() );

		try
		{
			$picture = HTMLPicture::generate
			(
				'somegone',
				'png',
				[
					[ 'w' => 800, 'h' => 600 ],
					[ 'w' => 480, 'h' => 320 ],
					[ 'w' => '1200', 'h' => 800 ]
				],
				[
					'loader' => [ 'directory-url' => 'https://mywebsite.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests' ]
				]
			);
		}
		catch ( MissingFileException $e )
		{
			$picture = $e->getFallbackContent();
		}

		$this->assertStringContainsString( '<picture>', $picture->getHTML() );
		$this->assertStringContainsString( '<img src="https://mywebsite.com/tests/somegone-800x600.png"', $picture->getHTML() );
		$this->assertStringContainsString( '<source srcset="https://mywebsite.com/tests/somegone-480x320.png?m=', $picture->getHTML() );
	}

	public function testPictureHTMLWithFileLoaderWithoutVersioning()
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
				'loader' => [ 'directory-url' => 'https://mywebsite.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests' ],
				'show-version' => false
			]
		);
		$this->assertStringContainsString( '<picture>', $picture->getHTML() );
		$this->assertStringContainsString( '<img src="https://mywebsite.com/tests/photo-480x320.jpg"', $picture->getHTML() );
		$this->assertStringNotContainsString( '<source srcset="https://mywebsite.com/tests/photo-800x600.jpg?m=', $picture->getHTML() );
		$this->assertStringNotContainsString( ' show-version="', $picture->getHTML() );
	}
}
