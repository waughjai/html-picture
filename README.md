HTML Picture
=========================

Class for easy generation o' picture tag HTML code.

## Use

Arguments for constructor:
1. String representing base local filename ( don't include extension ).
2. String representing extension ( don't include "."" ).
3. Hash map with optional arguments:
	1. "loader": file loader to help set URL directory in which 1st argument will be treated local to & the server directory for getting the modified date o' the file to break cache corruption. See https://github.com/waughjai/file-loader for mo' info on how to use this. Can be hash map or FileLoader object.
	2. "img-attributes": HTML attributes that will go in the img tag ( these will affect the image that is shown ).
	3. "picture-attributes": HTML attributes that will go in containing picture tag.
	4. "source-attributes": HTML attributes that will go in every source tag. You probably won't need this. Don't use ID, as it will create multiple IDs, which is invalid.

## Example

	use WaughJ\HTMLPicture\HTMLPicture;

	echo new HTMLPicture
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

Will print:

	<picture id="slider-42"><source class="source-item" srcset="https://mywebsite.com/tests/photo-480x320.jpg?m=1543530332" media="(max-width:480px)"><source class="source-item" srcset="https://mywebsite.com/tests/photo-800x600.jpg?m=1543530717" media="(max-width:800px)"><source class="source-item" srcset="https://mywebsite.com/tests/photo-1200x800.jpg?m=1543530725"><img src="https://mywebsite.com/tests/photo-480x320.jpg?m=1543530332" class="center-img" alt="" /></picture>
