<?php

	class SPDOStatement {

		/**
		 * @var PDOStatement
		 */
		private $statement;

		public function __construct($statement) {
			$this->statement = $statement;
		}

		/**
		 * modified execute() which returns the underlying PDOStatement object on success, thus making the execute command "chainable"
		 *
		 * @param type $array
		 * @return PDOStatement
		 * @throws SPDOException
		 */
		public function execute() {
			try {
				$numArgs = func_num_args();
				if($numArgs === 0) {
					$res = $this->statement->execute();
				} elseif($numArgs === 1 && is_array(func_get_arg(0))) {
					$res = $this->statement->execute(func_get_arg(0));
				} else {
					$res = $this->statement->execute(func_get_args());
				}
				if($res) {
					return $this->statement;
				} else {
					return false;
				}
			} catch(PDOException $e) {
				throw new SPDOException($e);
			}
		}

		public function fetchMap() {
			return $this->statement->fetch(PDO::FETCH_ASSOC);
		}

		public function fetchArray() {
			return $this->statement->fetch(PDO::FETCH_NUM);
		}

		/**
		 * Handler for bindParam(), which needs pass-by-reference
		 *
		 * @param mixed $parameter Parameter identifier. For a prepared statement using named placeholders,
		 * this will be a parameter name of the form :name. For a prepared statement using question mark placeholders,
		 * this will be the 1-indexed position of the parameter.
		 * @param mixed $variable Name of the PHP variable to bind to the SQL statement parameter.
		 * @param int $data_type Explicit data type for the parameter using the PDO::PARAM_* constants.
		 * To return an INOUT parameter from a stored procedure, use the bitwise OR operator to set
		 * the PDO::PARAM_INPUT_OUTPUT bits for the data_type parameter.
		 * @param int $length Length of the data type. To indicate that a parameter is an OUT parameter from a stored procedure,
		 * you must explicitly set the length.
		 * @param mixed $driver_options
		 */
		public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = NULL, $driver_options = NULL) {
			$this->statement->bindParam($parameter, $variable, $data_type, $length, $driver_options);
		}

		/**
		 * magic __call method which routes every call to the "real" PDOStatement
		 *
		 * @param string $name
		 * @param array $arguments
		 */
		function __call($name, array $arguments) {
			return call_user_func_array(array($this->statement, $name), $arguments);
		}
	}
