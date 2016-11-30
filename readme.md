# Readme

CodeYellow/Validation is an extension for Illuminate/Validation - the validation library of Laravel 5. It eliminates the necessity to create validators using
the factory methods, and instead lets you define the validator as a stand alone class. This allows for easy extension of the validation class, makes code reuse simpler,
and seperates the responsibility of validation logic from the model and service. Furthermore CodeYellow/Validation gives adds a lot of usefull features to make validation
even simpler.

## Defining a validator
Defining a validator is very easy. Create a class that extends the ```\CodeYellow\Validation\Validator``` class, and create a variable $rules with the same validation rules
as you can use in Illuminate/Validation. For example:


```php
<?php
class SimpleValidator extends \CodeYellow\Validation\Validator
{
    protected $rules = [
        'age' => 'required|numeric|min:5',
        'size' => 'numeric|min:10'
    ];
}
```

Your rules do not have to be static. If the rules depend on some kind of variable, a method getRules can be defined to return a dynamic definition of rules. For example;

```php
<?php
class MoreAdvancedValidator extends \CodeYellow\Validation\Validator
{
    protected $minAge = 5;

    public function setMinAge($age) {
        $this->minAge = $age;
    }


    public function getRules() {
        return [
                   'age' => 'required|numeric|min:' . $minAge,
                   'size' => 'numeric|min:10'
               ];
    }
}
```


## Using a validator

Validators can be injected through normal dependency injection, and has got two methods. The verify method returns a boolean that indicates if
the data satisfies the validator. The parse method throws a \Illuminate\Validation\ValidationException if the data does not validate. If the data does validate
the data is parsed in the correct format. See the following example:

```php
<?php
$goodData = [
    'age' => '7',
    'size' => '12'
];

$badData = [
    'age' => '3',
    'size' => '11'
];

$simpleValidator = app()->make('SimpleValidator'); // Create the simplevalidator as defined above

$simpleValidator->validate($goodData); // True
$goodData; //  ['age' => '7', 'size' => '12' ];
$simpleValidator->validate($badData); // false


$simpleValidator->parse($goodData); // ['age' => 7, 'size' => 12 ];
$goodData; // ['age' => 7, 'size' => 12 ];
$simpleValidator->parse($badData); // ValidationException

```

## Extending a validator

You can add methods to the validator by naming them "validateStuff" that returns a boolean, where stuff is the name of your validation. For example, see the following validator which checks if
a number is even. See the Laravel documentation for how you can define custom error messages for your validation

```php
<?php
class IsEvenValidator extends \CodeYellow\Validation\Validator
{
    protected $rules = [
        'age' => 'required|even',
    ];

    public function validateEven($number) {
        if (!is_numeric($number)) {
            return false;
        }

        return $number % 2 == 0;
    }
}
``

Now you can extend a validator just as you would do in a normal class, like this:

```php
<?php
class IsAlsoEvenValidator extends IsEvenValidator
{
    protected $rules = [
        'age' => 'required|even',   // this uses the validateEven method from the IsEvenValidator
    ];
}
```

## NestedValidators
Sometimes the data that you need to validate contains something that is validated already in another validator. For example if you create a client, with a user, it might be that you have defined a validator for the user already.
This means that you have to duplicate the validation data for the user between the client and the user validator. NestedValidation contain a solution for this.

First of all, other validators that are used need to be registered in the validator. This is done as follows:

```php
<?php
class FooValidator extends \CodeYellow\Validation\Validator
{
    protected $validators = [
        'bar' => 'Foo\\BarValidator'
    ];
```
Now we can reference the 'bar' validator when specifying rules

__nested validator__

The nested validator can be applied by adding 'nested:validator\_name' in the rules. So for example:

```php
<?php
    protected $rules = [
        'nested' => 'array|nested:bar'
    ];
```

The bar validator will now be called on the value of the nested key


__nested collection validator__

The nested collection is almost the same as the nested validator. The only difference is that in the rules 'nested\_collection:validator\_name' needs to be used.

E.g.
```php
<?php
    protected $rules = [
        'nested_collection' => 'array|nested_collection:bar'
    ];
```

Now assume that we have a validator for bar as follows:

```php
class BarValidator extends IsEvenValidator
{
    protected $rules = [
        'age' => 'required|even',   // this uses the validateEven method from the IsEvenValidator
    ];
}
```

Now we can use the fooValidator as follows:
```php
$fooValidator = app()->make('FooValidator');

$bar = ['age' => 6];
$otherBar = ['age' => 8];
$badBar = ['age' => 7];

$fooValidator->validate([
    'nested' => $bar
]); // Returns true, since $bar passes the BarValidator

$fooValidator->validate([
    'nested' => $badBar
]); // Returns false, since $badBar does not pass the BarValidator


$fooValidator->validate([
    'nested_collection' => [$bar, $otherBar]
]); // Returns true, since $bar and $otherBar passes the BarValidator

$fooValidator->validate([
    'nested' => [$bar, $badBar]
]); // Returns false, since $badBar does not pass the BarValidator
```
