<?php
class NamespaceResolveTest extends \PHPUnit\Framework\TestCase {

	private function createResolver($code) {
		return new \Insphpect\StaticAnalysis\NamespaceResolve($code);
	}

	public function testBasicNoNs() {
		$code  = '<?php

			$foo = new Foo;
		';


		$resolver = $this->createResolver($code);

		$this->assertEquals('\Foo', $resolver->resolve('Foo'));
	}

	public function testBasicNoNsWithSlash() {
		$code  = '<?php

			$foo = new \Foo;
		';

		$resolver = $this->createResolver($code);

		$this->assertEquals('\Foo', $resolver->resolve('\Foo'));
	}


	public function testWithNameSpace() {
		$code  = '<?php
			namespace X;
			$foo = new Y;
		';

		$resolver = $this->createResolver($code);

		$this->assertEquals('\X\Y', $resolver->resolve('Y'));
	}

	public function testGlobalFromNameSpace() {
		$code  = '<?php
			namespace X;
			$foo = new \Y;
		';

		$resolver = $this->createResolver($code);

		$this->assertEquals('\Y', $resolver->resolve('\Y'));
	}

	public function testUse() {
		$code  = '<?php
			namespace X;
			use Foo\Bar;
			$foo = new Bar;
		';

		$resolver = $this->createResolver($code);

		$this->assertEquals('\Foo\Bar', $resolver->resolve('Bar'));
	}

	public function testUseMulti() {
		$code  = '<?php
			namespace X;
			use Foo\Bar;
			use Boo\Far;
			$foo = new Bar;
		';

		$resolver = $this->createResolver($code);

		$this->assertEquals('\Foo\Bar', $resolver->resolve('Bar'));
		$this->assertEquals('\Boo\Far', $resolver->resolve('Far'));
	}

	public function testUseAs() {
		$code  = '<?php
			namespace X;
			use Foo\Bar as Baz;
			$foo = new Bar;
		';

		$resolver = $this->createResolver($code);

		$this->assertEquals('\Foo\Bar', $resolver->resolve('Baz'));
	}

	public function testUseWholeNs() {
		$code  = '<?php
			namespace X;
			use Foo\Bar;
			$foo = new Bar\Baz;
		';

		$resolver = $this->createResolver($code);

		$this->assertEquals('\Foo\Bar\Baz', $resolver->resolve('Bar\Baz'));
	}


	public function testUseCommaSeparated() {
		$code  = '<?php
			namespace X;
			use Foo\Bar, Boo\Far as Faz;
			$foo = new Bar;
		';

		$resolver = $this->createResolver($code);

		$this->assertEquals('\Foo\Bar', $resolver->resolve('Bar'));
	}
}