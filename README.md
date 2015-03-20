# Rational Meta Box Generator

Generates meta boxes for WordPress using fields passed via arrays.

## Version
0.1


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

## Todo's

* Get list of available styles for inputs

## License

MIT