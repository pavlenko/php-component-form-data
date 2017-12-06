# Form data component
This component allow to parse raw form data body for non POST requests
### Installation
```sh
php composer.phar require pe/component-form-data:1.0.0
```
### Usage
```php
// Parse data
$data = new FormData(
    $_SERVER['CONTENT_TYPE'],
    file_get_contents('php://input')
);

// Get $_FILES compatible array
$data->getFILES()

// Get $_POST compatible array
$data->getPOST()
```
