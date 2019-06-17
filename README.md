# Static Analysis Tools

A few tools I developed as part of my PhD project *Insphpect*.

## 1. NamespaceResolve

This can be used to resolve the full classname including namespaces of any class inside any file.

Given `file.php:`

```php
<?php
namespace X;
use Foo\Bar as Baz;
$foo = new Baz;
```

The resolver can be used to work out that `new Baz` will be instantiating `\Foo\Bar`.

```php
$source = file_get_contents('file.php');
$resolver = new \Insphpect\StaticAnalysis\NamespaceResolve($source);
$resolver->resolve('Baz'); // \Foo\Bar
```

See the tests for more examples.


## 2. VariableResolve

This tool attempts to resolve the contents of `$variable` on any `$line` in `$code`. It does not assume a call stack and will give the number of the argument if an argument needs to be resolved.

Where possible a value or code block will be supplied. If the value would come from higher up in the call stack, a value such as `{ARG0}` or `{ARG2}` will be returned, indicating the index of the argument used when the function is called.

Example 1:

```php

$code = '<?php
	class TestClass implements Foo {
	private $a;
	private $b;

	public function __construct($a, $b, $c) {
		$ff = 1;
		$this->a = $a;
		$this->b = new \B(str_replace($ff, trim(\'b\'), \'c\'));
	}
}';


$resolver = new \Insphpect\StaticAnalysis\VariableResolve();

// Resolve the contents of the variable $ff on line 8 in $code
$result = $resolver->resolve($code, '$ff', 8); // "1"
```

Example 2:


```php



$code = '<?php
class TestClass implements Foo {
	private $a;
	private $b;

	public function __construct($a, $b, $c) {
		$this->a = $a;
		$this->b = new \B(str_replace($ff, trim(\'b\'), \'c\'));
		$ff = new \Something();
	}
}';


$resolver = new \Insphpect\StaticAnalysis\VariableResolve();

// Resolve the contents of $ff on line 10
$result = $resolver->resolve($code, '$ff', 10); // "new Something()"



```

Example 3:

```php
		$code = '<?php
		class TestClass implements Foo {
	private $a;
	private $b;

	public function __construct($a, $b, $c) {
		$this->a = $a;
		$this->b = $b;

	}
}';


$resolver = new \Insphpect\StaticAnalysis\VariableResolve();

// Resolve the contents of the variable $b on line 10
$result = $resolver->resolve($code, '$b', 10);  // "{ARG1}"


```



Example 4:

```php
$code = '<?php
class TestClass implements Foo {
	private $a;
	private $b;

	public function __construct($a, $b, $c) {
		$b = $b+1;
		$this->a = $a;
		$this->b = $b;

	}
}';


$resolver = new \Insphpect\StaticAnalysis\VariableResolve();

// Resolve $b on line 11 of $code
$result = $resolver->resolve($code, '$b', 11); // "{ARG1}+1"

```

See test cases for more examples