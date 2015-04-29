# Rational Meta Box Generator

Generates meta boxes for WordPress using fields passed via arrays.

## Installation

```php
require_once('rational-meta-box.class.php');
$rational_meta_box = new RationalMetaBox();

function rational_meta_box_class() {
	global $rational_meta_box;
	$rational_meta_box->add_box();
}
add_action( 'add_meta_boxes', 'rational_meta_box_class' );
```

## Usage

### Meta Box Parameters

Meta box parameters are set by passing an array as the first parameter in the `add_box` method.

```php
$my_attributes = array(
	'id'			=> 'my-meta-box',
	'title'			=> 'My Meta Box',
);
$rational_meta_box->add_box( $my_attributes );
```
Essentially the same as [WordPress' add_meta_box() parameters](http://codex.wordpress.org/Function_Reference/add_meta_box).

* **id**: HTML id attribute of meta box element. (default: 'rational-meta-box')
* **title**: Title of meta box. (default: 'Rational Meta Box')
* **screen**: Location, or post type, of the meta box. (default: 'post')
* **context**: Region where the meta box is displayed. Boxes placed in the 'side' context will not use a table for layout. (default: 'advanced')
* **priority**: Priority of the meta box within it's region. (default: 'default')

### Meta Box Fields

Fields are added via an array passed as the second parameter in the `add_box` method.

```php
$my_fields = array(
	array(
		'type'	=> 'text',
		'name'	=> 'your-name',
		'label' => 'Your Name',
		'atts' => array(
			'class'	=> 'large-text',
		),
	),
);
$rational_meta_box->add_box( false, $my_fields );
```

Some parameters exist for all input types while some are specific

#### Parameters For All Types

* **type**: HTML input type (text, textarea, select, checkbox, radio).
* **name**: HTML input name attribute.
* **label**: HTML label element.
* **description** Used with checkboxes: Description for the checkbox. Wrapper in a label element as well.
  All other elements: Descriptive text that appears under the elements.

#### Parameters For Text and Textarea

* **value**: HTML input value attribute.
* **placeholder**: HTML5 input placeholder attribute.
* **atts**: Additional attributes in an array. Currently supports 'class' for both and 'rows' for textarea only.
* **atts['char_count']** Adds a character counter to the input or textarea.
* **atts['char_limit']** Adds a character limit to the counter for the input or textarea. This is not a "hard" limit so you can enter more. Default: 155.

#### Parameters for Select and Radio

* **options**: Array of options. Items in a sequential array use the array value as the input and option value as well. Items in an associative array use the array key as the value of the input or option and the array value as the text or description. Example below.

```php
$fields = array(
	array(
		'type'			=> 'select',
		'name'			=> 'select-name',
		'label'			=> 'Select Label',
		'options'		=> array(
			'Option One',
			'option-two' => 'Option Two'
		)
	)
);
$my_meta_box->add_box( false, $fields );
```

The first option would render as `<option>Option One</option>` while the second would render as `<option value="option-two">Option Two</option>`.

## Contributing

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request

## Version

0.2

## History

* 0.2 - Added character counter code, JS and CSS. Added "description" fields for inputs, selects and textareas.
* 0.1 - Initial upload

## Todo's

* Get list of available styles for inputs
* Meta box `callback_args`

## License

The MIT License (MIT)

Copyright (c) 2015 Jeremy Hixon

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
