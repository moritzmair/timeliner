<?php
	error_reporting(E_ALL ^ E_NOTICE);

	class SPDOException extends PDOException {

		function __construct(PDOException $e) {
			$this->code = $e->getCode();
			$this->message = $e->getMessage();
			$trace = $e->getTrace();
			$this->file = $trace[1]['file'];
			$this->line = $trace[1]['line'];
		}

	}

	/**
	 * Static class containing a shared PDO object
	 *
	 * @author Michael Lux
	 */
	class SPDO {

		const DB_HOST = 'localhost';
		const DB_USER = 'username';
		const DB_PASSWORD = 'password';
		const DB_NAME = 'timeliner';

		//geteilte Datenbank-Verbindung
		private static $instance = NULL;
		//Tabellen-Prefix
		private static $dbPrefix;

		//Initialisierungsmethode, wird unter der Klassendefinition aufgerufen
		public static function init() {
			if(substr(DB_PREFIX, -1) == '_') {
				SPDO::$dbPrefix = DB_PREFIX;
			} else {
				SPDO::$dbPrefix = DB_PREFIX.'_';
			}
		}

		/**
		 * Gibt das geteilte PDO-Objekt zurück; <br />
		 * Diese Funktion baut eine Datenbank-Verbindung auf, falls noch keine besteht.
		 *
		 * @return PDO PDO-Objekt
		 */
		public static function getInstance() {
			if(SPDO::$instance === NULL) {
				//Verbindung zur Datenbank herstellen
				SPDO::$instance = new PDO('mysql:host=' . SPDO::DB_HOST . ';dbname=' . SPDO::DB_NAME, SPDO::DB_USER, SPDO::DB_PASSWORD, array(
					PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
					PDO::ATTR_PERSISTENT => TRUE
				));
				SPDO::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			return SPDO::$instance;
		}

		/**
		 * PDO::query() auf der gemeinsamen PDO-Instanz mit Prefix-Auflösung
		 *
		 * @param string $sql
		 * @return PDOStatement Query-Result
		 */
		public static function query($sql) {
			try {
				$rsql = str_replace('#_', SPDO::$dbPrefix, $sql);
				return SPDO::getInstance()->query($rsql);
			} catch(PDOException $e) {
				throw new SPDOException($e);
			}
		}

		/**
		 * PDO::exec() auf der gemeinsamen PDO-Instanz mit Prefix-Auflösung
		 *
		 * @param string $sql
		 * @return int Anzahl betroffener Zeilen
		 */
		public static function exec($sql) {
			try {
				$rsql = str_replace('#_', SPDO::$dbPrefix, $sql);
				return SPDO::getInstance()->exec($rsql);
			} catch(PDOException $e) {
				throw new SPDOException($e);
			}
		}

		/**
		 * PDO::exec() auf der gemeinsamen PDO-Instanz mit Prefix-Auflösung
		 *
		 * @param string $sql
		 * @return PDOStatement Prepared Statement
		 */
		public static function prepare($sql, $driver_options = NULL) {
			try {
				$rsql = str_replace('#_', SPDO::$dbPrefix, $sql);
				if($driver_options !== NULL) {
					return new SPDOStatement(SPDO::getInstance()->prepare($rsql, $driver_options));
				} else {
					return new SPDOStatement(SPDO::getInstance()->prepare($rsql));
				}
			} catch(PDOException $e) {
				throw new SPDOException($e);
			}
		}

		/**
		 * "Magische Methode" seit PHP 5.3, NICHT FÜR DEN DIREKTEN AUFRUF <br />
		 * Lenkt statische Methoden-Zugriffe auf diese Klasse auf das Object $instance um
		 *
		 * @param string $name Name der Methode
		 * @param array $arguments Array von Argumenten
		 */
		public static function __callStatic($name, array $arguments) {
			return call_user_func_array(array(SPDO::getInstance(), $name), $arguments);
		}

	}

//statische Member initialisieren
	SPDO::init();
