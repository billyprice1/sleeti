<?php

/**
 * This file is part of sleeti.
 * Copyright (C) 2016  Eliot Partridge
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Sleeti\Logging;

/**
 * Handles Sleeti logging
 */
class Logger
{
	/**
	 * Log levels constant array
	 * @var array
	 */
	const LOG_LEVELS = [
		'DEBUG'     => \Monolog\Logger::DEBUG,
		'INFO'      => \Monolog\Logger::INFO,
		'NOTICE'    => \Monolog\Logger::NOTICE,
		'WARNING'   => \Monolog\Logger::WARNING,
		'ERROR'     => \Monolog\Logger::ERROR,
		'CRITICAL'  => \Monolog\Logger::CRITICAL,
		'ALERT'     => \Monolog\Logger::ALERT,
		'EMERGENCY' => \Monolog\Logger::EMERGENCY,
	];

	/**
	 * Slim 3 container object
	 */
	protected $container;

	/**
	 * Log file handler
	 * @var \Monolog\Handler\RotatingFileHandler
	 */
	protected $handler;

	/**
	 * Array of cached Monolog logger instances
	 * @var array
	 */
	protected $loggers;

	public function __construct($container) {
		$this->container = $container;

		if (!is_dir($this->container['settings']['logging']['path']) && $this->container['settings']['logging']['enabled']) {
			mkdir($this->container['settings']['logging']['path']);
		}

		$logfile       = $this->container['settings']['logging']['path'] . 'sleeti.log';
		$this->handler = new \Monolog\Handler\RotatingFileHandler($logfile, $container['settings']['logging']['maxFiles'] ?? 0, $this->container['settings']['logging']['level'] ?? \Monolog\Logger::INFO);

		$dateFormat   = 'H:i:s';
		$outputFormat = "[%datetime%] [%level_name%] %channel%: %message% %context%\n";
		$formatter    = new \Monolog\Formatter\LineFormatter($outputFormat, $dateFormat);

		$this->handler->setFormatter($formatter);
	}

	/**
	 * Adds a logger to the loggers cache
	 * @param string $name The name of the log channel this logger uses
	 */
	private function addLogger($name) {
		$logger = new \Monolog\Logger($name);
		$logger->pushHandler($this->handler);
		$this->loggers[$name] = $logger;
	}

	/**
	 * Logs to a specific logger channel
	 * @param  string $name    The logger channel to log to
	 * @param  int    $level   The logging level to output on
	 * @param  mixed  $message The log message to write
	 * @param  array  $context Any extra details to log with the message
	 */
	public function log($name, $level, $message, array $context = []) {
		if (!$this->container['settings']['logging']['enabled']) return;

		if (!isset($loggers[$name])) {
			$this->addLogger($name);
		}

		$this->loggers[$name]->log($level, $message, $context);
	}
}
