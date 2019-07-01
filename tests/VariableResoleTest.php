<?php
class VariableResolveTest extends \PHPUnit\Framework\TestCase {

	public function testBasic() {

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

		$result = $resolver->resolve($code, '$ff', 8);

		$this->assertEquals(1, $result);

	}

	public function testBasicString() {

		$code = '<?php
		class TestClass implements Foo {
	private $a;
	private $b;

	public function __construct($a, $b, $c) {
		$this->a = $a;
		$this->b = new \B(str_replace($ff, trim(\'b\'), \'c\'));
		$ff = "this" . "is" . "a" . "concatenated" . "string";
	}
}';


		$resolver = new \Insphpect\StaticAnalysis\VariableResolve();

		$result = $resolver->resolve($code, '$ff', 10);

		$this->assertEquals('"this" . "is" . "a" . "concatenated" . "string"', $result);

	}


	public function testBasicObj() {

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

		$result = $resolver->resolve($code, '$ff', 10);

		$this->assertEquals('new \Something()', $result);

	}

	public function testArgument() {

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

		$result = $resolver->resolve($code, '$b', 10);

		$this->assertEquals('{ARG1}', $result);

	}


	public function testArgumentModified() {

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

		$result = $resolver->resolve($code, '$b', 11);

		$this->assertEquals('{ARG1}+1', $result);

	}


	public function testArgumentModifiedTwice() {

		$code = '<?php
		class TestClass implements Foo {
	private $a;
	private $b;

	public function __construct($a, $b, $c) {
		$b = $b+1;
		$b = $b+2;
		$this->a = $a;
		$this->b = $b;

	}
}';


		$resolver = new \Insphpect\StaticAnalysis\VariableResolve();

		$result = $resolver->resolve($code, '$b', 11);

		$this->assertEquals('{ARG1}+1+2', $result);

	}

	public function testObjWithArg() {

		$code = '<?php
		class TestClass implements Foo {
	private $a;
	private $b;

	public function __construct($a, $b, $c) {
		$this->a = $a;
		$this->b = new \B(str_replace($ff, trim(\'b\'), \'c\'));
		$ff = new \Something("Foo", $b);
	}
}';


		$resolver = new \Insphpect\StaticAnalysis\VariableResolve();

		$result = $resolver->resolve($code, '$ff', 10);

		$this->assertEquals('new \Something("Foo", {ARG1})', $result);

	}

	public function testArg2() {

		$code = '<?php
class TestClass implements Foo {
	private $a;
	private $b;

	public function __construct($a) {
		$ff = 1;
		$this->b = new RequiresObject($a);
	}
}';



		$resolver = new \Insphpect\StaticAnalysis\VariableResolve();

		$result = $resolver->resolve($code, '$a', 10);

		$this->assertEquals('{ARG0}', $result);

	}


		public function testArgDefaultValue() {

		$code = '<?php
class TestClass implements Foo {
	private $a;
	private $b;

	public function __construct($a = 1) {
		$ff = 1;
		$this->b = new RequiresObject($a);
	}
}';



		$resolver = new \Insphpect\StaticAnalysis\VariableResolve();

		$result = $resolver->resolve($code, '$a', 10);

		$this->assertEquals('{ARG0?1}', $result);

	}


	public function testArgProperty() {

		$code = '<?php
class TestClass implements Foo {
	private $a = 4;
	private $b;

	public function __construct() {
		$ff = 1;
		$this->b = new RequiresObject($this->a);
	}
}';



		$resolver = new \Insphpect\StaticAnalysis\VariableResolve();

		$result = $resolver->resolve($code, '$this->a', 10);

		$this->assertEquals('4', $result);

	}

}