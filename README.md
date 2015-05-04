# Rational Meta Box Class

## Description

PHP Class for building WordPress meta boxes using multidimensional, associative arrays.

- Uses [WordPress' native functions](https://codex.wordpress.org/Function_Reference/add_meta_box)
- Supports all, basic HTML and HTML5 inputs and attributes
- Reduces a lot of redundant callback programming

## Installation

```php
function rational_meta_boxes() {
	require_once( 'inc/class.rational-meta-box.php' );
	$rational_meta_box = new RationalMetaBoxes();
	$rational_meta_box->generate_boxes();
}
add_action( 'admin_init', 'rational_meta_boxes' );
```
1. Create a function to hook into WordPress' `admin_init` hook.
2. `require_once` the Rational Meta Box class.
3. Instantiate the class.
4. Trigger the generation of the boxes with `generate_boxes()` method.

## Customizing
```php
$my_boxes = array(
	'my-box-one'	=> array(
		'title'			=> 'My Box One',
		'screen'		=> array( 'post', 'page' ),
		'description'	=> 'My custom meta box description',
		'fields'		=> array(
			'my-text-field'	=> array(
				'type'			=> 'text',
				'label'			=> 'My Text Field',
				'description'	=> 'Instructions or an explanation of the field',
			),
		),
	),
);
$rational_meta_box = new RationalMetaBoxes( $my_boxes );
```

1. Create an array with the top level being the attributes of your meta box and fields within that.
2. Pass the array to the function that initializes the Rational Meta Box class.
3. Check out your meta box on the given post type(s)' edit page.

![Screenshot of rendered 'my_boxes' array](http://i.imgur.com/IevTxdS.jpg)

## Parameters

### Meta Box Options

See [WordPress' `add_meta_box` function](https://codex.wordpress.org/Function_Reference/add_meta_box#Parameters) for more information.

- **$id**: (required, string, preferably hyphenated) Provided as the 'key' for the array of attributes.
- **$title**: (required, string) Defines the "title" of the meta box.
- **$screen**: (optional, string or array) Where the meta box will appear. Can be string or an array of strings.  
(`'post', 'page','dashboard','link','attachment','custom_post_type'`)  
Default: `'post'`
- **$description**: (optional, string) A description for the meta box.
- **$context**: (optional, string) Position on the editor window.  
(`'normal', 'advanced', or 'side'`)  
Default: `'advanced'`
- **$priority**: (optional, string) The priority to give the meta box.  
(`'high', 'core', 'default' or 'low'`)  
Default: `'default'`
- **$fields**: (optional, array) An array of multidimensional, associative arrays containing field data. See [Field Options](#field-options) below.

Example:
```php
$my_boxes = array(
	'meta-box-id'   => array(
		'title'         => 'Meta Box One',
		'screen'        => array( 'post', 'page' ),
		'description'   => 'Description of meta box',
		'context'       => 'side',
		'priority'      => 'core',
		'fields'        => array(
			'text-field-id' => array(
				'type'          => 'text',
				'label'         => 'Field Label',
				'description'   => 'Instructions or an explanation of the field',
			),
		),
	),
);
```
<a name="field-options" id="field-options"></a>
### Field Options
- **$id**: (required, string, preferrably hyphenated) Provided as the 'key' for the array of field attributes.
- **$type**: (optional, string) The type of input to generate.  
(`'file', 'checkbox', 'color', 'date', 'month', 'week', 'datetime', 'datetime-local', 'email', 'number', 'password', 'radio', 'range', 'search', 'select', 'tel', 'text', 'time', 'textarea', 'url'`)  
Default: `'text'`
- **$label**: (required, string) Identifying text for the left column. Serves as a label for some input types.
- **$description**: (optional, string) Serves as 'help text' for most elements. Also serves as a label for checkboxes.