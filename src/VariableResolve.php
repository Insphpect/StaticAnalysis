<?php
namespace Insphpect\StaticAnalysis;
class VariableResolve {
	// Resolves the value of $variable ARG_{NUM} for any given $var or $array['index'];
	// in $file on $line no

	public function resolve(string $code, string $variable, int $line): string {
		$lines = explode("\n", $code);

		//Firstly ignore anything after $line
		$lines = array_splice($lines, 0, $line);

		//Now tokenize and find the containing function if there is one
		$code = implode("\n", $lines);

		$tokens = new Tokens(token_get_all($code));

		//Get the arguments of containing function, if there are any
		$arguments = $this->getArguments($tokens);

		$tokens = $tokens->end();
		$val = '';

		while ($tokens = $tokens->prev()) {
			if ($tokens->string() == $variable) {
				$assignment = $this->getAssignment($tokens);

				if ($assignment) {
					$val = preg_replace('/\{ARG[0-9]+\}/s', '', $val);
					$val = $this->getAssignmentVal($assignment, $arguments) . $val;

					//Keep going back until the previous expression
					while ($tokens->string() != ';' && $tokens = $tokens->prev());
				}
			}
		}

		if ($val == null) {
			$val = '{ARG' . $this->getArgNum($variable, $arguments) . '}';
		}

		return $val;
	}

	private function getAssignment(Tokens $tokens) {
		$tokens = $tokens->prev()->splice()->toNext(';')->splice(Tokens::REMOVE_AFTER);


		$equals = $tokens->toNext('=');

		if ($equals) {
			return $equals->next();
		}
		else return false;
	}


	private function getAssignmentVal(Tokens $tokens, $arguments) {
		$assignmentVal = '';

		while ($tokens = $tokens->next()) {
			if ($tokens->is('T_VARIABLE')) {
				if ($num = $this->getArgNum($tokens->string(), $arguments)) {
					$assignmentVal .= '{ARG' . $num . '}';
				}
			}
			else $assignmentVal .=  $tokens->string();

		}

		return trim($assignmentVal);
	}

	private function getArgNum($var, $arguments) {
		for ($i = 0; $i < count($arguments); $i++) {
			if ($arguments[$i] == $var) return $i;
		}

		return 0;
	}





	private function getArguments(Tokens $tokens): array {
		//move the cursor to the end
		$tokens = $tokens->end();
		while ($tokens = $tokens->prev()) {
			if ($tokens->is('T_FUNCTION')) {
				$functionHeader = $tokens->splice(Tokens::REMOVE_BEFORE)->toNext(')')->splice(Tokens::REMOVE_AFTER);
				break;
			}
		}

		if (!$functionHeader) return [];

		return $this->getVariables($functionHeader);
	}

	private function getVariables(Tokens $tokens): array {
		$variables = [];

		while ($tokens = $tokens->next()) {
			if ($tokens->is('T_VARIABLE')) {
				$variables[] = $tokens->string();
			}
		}

		return $variables;
	}

}