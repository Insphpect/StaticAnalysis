<?php
// Resolves a fully qualified class name for any class name in $source.
//
// new Foo  will instantiate a class based on the current namespace and use keywords
// Note: This doesn't support files with multiple namespace { } blocks.
/*
		Given file.php:

		```
			<?php
			namespace X;
			use Foo\Bar as Baz;
			$foo = new Bar;
		```

		$source = file_get_contents('file.php');
		$resolver = new \Insphpect\StaticAnalysis\NamespaceResolve($source);
		$resolver->resolve('Baz'); // \Foo\Bar
*/

namespace Insphpect\StaticAnalysis;
class NamespaceResolve {
	private $tokens;
	private $namespace = '\\';
	private $use = [];

	public function __construct($code) {
		$token = new Tokens(token_get_all($code));
		$this->namespace = $this->readNamespace($token);
		$this->processUse($token);
	}

	//Look for the `namespace` line to get the namespace of the file
	private function readNamespace($token) {
		while ($token = $token->next()) {
			if ($token->is('T_NAMESPACE')) {
				return '\\' . trim($token->toNext('T_STRING')->string(),'\\') . '\\';
			}
		}
		return '\\';
	}

	//Loop through tokens looking for the `use` keyword to set up aliases
	private function processUse($token) {

		while ($token = $token->next()) {
			if ($token->is('T_USE')) {
				$token = $this->useLine($token->toNext('T_STRING'));
			}
		}
	}

	private function writeUse($useClass, $asClass) {
		if ($asClass) {
			$this->use[$asClass] = $useClass;
		}
		else {
			$parts = explode('\\', $useClass);
			$this->use[end($parts)] = $useClass;
		}
	}

	private function useLine($token) {
		$useClass = '\\';
		$asClass = '';
		$as = false;
		while (!$token->is(';')) {
			if  ($token->is(',')) {
				$this->writeUse($useClass, $asClass);
				$as = false;
				$useClass = '\\';
				$asClass = '';
			}
			else if ($token->is('T_AS')) $as = true;
			else if ($as) $asClass .= trim($token->string());
			else $useClass .= trim($token->string());

			$token = $token->next();
		}


		$this->writeUse($useClass, $asClass);

		return $token;
	}

	//Extracts \Foo\Bar from \Foo\Bar\Baz
	private function extractNs($className) {
		$parts = explode('\\', $className);
		array_pop($parts);
		return implode('\\', $parts);
	}

	//Extracts Baz from \Foo\Bar\Baz
	private function extractClass($className) {
		$parts = explode('\\', $className);
		return array_pop($parts);
	}

	// Resolves the fully qualified $className in the current file
	public function resolve($className) {
		if ($className[0] == '\\') return $className;
		else if (isset($this->use[$className])) return $this->use[$className];
		else if (isset($this->use[$this->extractNs($className)])) return $this->use[$this->extractNs($className)] . '\\' . $this->extractClass($className);
		else return $this->namespace . $className;

	}
}