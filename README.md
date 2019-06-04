HTML Picture
=========================

Class for easy generation o' picture tag HTML code.

## Use

Arguments for constructor:
1. Fallback image as HTMLImage object.
2. Sources as array o' HTMLPictureSource objects.
3. Array o' picture element attributes.

A simpler way to generate these is to use the "generate" static method, which takes the following arguments:
1. String representing base local filename ( don't include extension ).
2. String representing extension ( don't include "."" ).
3. Hash map with optional arguments:
	1. "loader": file loader to help set URL directory in which 1st argument will be treated local to & the server directory for getting the modified date o' the file to break cache corruption. See https://github.com/waughjai/file-loader for mo' info on how to use this. Can be hash map or FileLoader object.
	2. "img-attributes": HTML attributes that will go in the img tag ( these will affect the image that is shown ).
	3. "picture-attributes": HTML attributes that will go in containing picture tag.
	4. "source-attributes": HTML attributes that will go in every source tag. You probably won't need this. Don't use ID, as it will create multiple IDs, which is invalid.

## Example

	use WaughJ\HTMLPicture\HTMLPicture;

	echo HTMLPicture::generate
	(
		'photo',
		'jpg',
		'480w 320h, 800w 600h, 1200w 800h',
		[
			'loader' =>
			[
				'directory-url' => 'https://mywebsite.com',
				'directory-server' => getcwd(),
				'shared-directory' => 'tests'
			],
			'img-attributes' => [ 'class' => 'center-img' ],
			'picture-attributes' => [ 'id' => 'slider-42' ],
			'source-attributes' => [ 'class' => 'source-item' ]
		]
	);

Will print ( #s after "?m=" will vary ):

	<picture id="slider-42"><source class="source-item" srcset="https://mywebsite.com/tests/photo-480x320.jpg?m=1543530332" media="(max-width:480px)"><source class="source-item" srcset="https://mywebsite.com/tests/photo-800x600.jpg?m=1543530717" media="(max-width:800px)"><source class="source-item" srcset="https://mywebsite.com/tests/photo-1200x800.jpg?m=1543530725"><img src="https://mywebsite.com/tests/photo-480x320.jpg?m=1543530332" class="center-img" alt="" /></picture>

### Error Handling

PictureHTML's default o' adding a version to each URL may throw a WaughJ\FileLoader\MissingFileException exception when using the static generate method ( but not the regular constructor, which doesn't set any URLs ). This exception contains a fallback version o' the PictureHTML that is equivalent to a version with "show_version" turned off.

Example:

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

## Changelog

### 0.7.0
* Add Cached HTML to Make Use o' Same Object Multiple Times Faster

### 0.6.0
* Revamp Error Handling for Missing Files

### 0.5.0
* Add Ability to Easily Cancel Showing Versioning

### 0.4.0
* Add getSources & getPictureAttributes Methods

### 0.3.0
* Change Constructor to Take in HTMLImage & Array o' HTMLPictureSource

### 0.2.0
* Add getFallbackImage & changeFallbackImage Methods

### 0.1.0
* Initial Version
