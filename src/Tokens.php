<?php
namespace Insphpect\StaticAnalysis;
class Tokens {
	private $tokens;
	private $i;
	const REMOVE_BEFORE = 1;
	const REMOVE_AFTER = 2;

	public function __construct($tokens, $i = 0) {
		$this->tokens = $tokens;
		$this->i = $i;
	}

	public function toNext(string $search): ?self {
		$next = $this->findNext($search);

		if ($next) {
			return new self($this->tokens, $next);
		}
		return null;
	}

	private function findNext(string $search): ?int {
		for ($i = $this->i; $i < count($this->tokens); $i++) {
			if (is_string($this->tokens[$i]) && $this->tokens[$i] == $search) return $i;
			else if (is_array($this->tokens[$i]) && token_name($this->tokens[$i][0]) == $search) return $i;
		}
		return null;
	}

	public function extractNested(): self {
		$close = null;
		$open = $this->string();

		if ($open == '(') $close = ')';
		else if ($open == '{') $close =  '}';
		else if ($open == '[') $close =  ']';

		if ($close == null) throw new \Exception('Cannot nest on non-bracket: ' . $this->string() );

		$tokens = $this->splice();
		$depth = 1;
		//Find the corresponding closing brace and return a subset of tokens
		while ($tokens = $tokens->next()) {
			if ($tokens->is('(')) $depth++;
			else if ($tokens->is(')')) $depth--;

			if ($depth == 0) {
				$tokens = $tokens->splice(\Insphpect\StaticAnalysis\Tokens::REMOVE_AFTER);
				break;
			}
		}

		return $tokens;

	}

	public function splice(int $dir = self::REMOVE_BEFORE): self {
		if ($dir == self::REMOVE_AFTER) {
			$spliced = array_splice($this->tokens, 0, $this->i);
		}
		else if ($dir == self::REMOVE_BEFORE) {
			$spliced = array_splice($this->tokens, $this->i, count($this->tokens));
		}

		return new self($spliced, 0);
	}

	public function debugString() {
		$o = '';
		foreach ($this->tokens as $token) {

			if (is_array($token)) $o .= $token[1];
			else $o .= $token;
		}
		return $o;
	}

	public function next(): ?self {
		return $this->move(1);
	}

	public function prev(): ?self {
		return $this->move(-1);
	}

	public function start(): self {
		return new self($this->tokens, 0);
	}

	public function end(): self {
		return new self($this->tokens, count($this->tokens)-1);
	}

	public function line(): int {
		return $this->tokens[$this->i][2];
	}

	private function move($i): ?self {
		if (isset($this->tokens[$this->i + $i])) {
			return new self($this->tokens, $this->i + $i);
		}
		else return null;
	}

	public function string() {
		$token = $this->tokens[$this->i];
		if (is_array($token)) return $token[1];
		else return $token;
	}

	public function name() {
		$token = $this->tokens[$this->i];
		if (is_array($token)) return token_name($token[0]);
		else return $token;
	}

	public function is($type) {
		$token = $this->tokens[$this->i];

		if (is_array($token)) return token_name($token[0]) == $type;
		else return $token == $type;
	}


}