<?php

/*
  Viscacha - An advanced bulletin board solution to manage your content easily
  Copyright (C) 2004-2017, Lutana
  http://www.viscacha.org

  Authors: Matthias Mohr et al.
  Publisher: The Viscacha Project, http://www.viscacha.org
  Start Date: May 22, 2004

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

use DebugBar\Bridge\MonologCollector;
use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\Renderable;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBar;
use Monolog\ErrorHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Debug {

	private static $debugModeEnabled;
	private static $loggingEnabled;
	private static $debugBar;
	private static $errorHandler;
	private static $defaultLogger;

	public static function init($debugModeEnabled = false, $loggingEnabled = false) {
		if (defined('NON_HTML_RESPONSE') || defined('CONSOLE_REQUEST')) {
			$debugModeEnabled = false;
		}
		
		self::$debugModeEnabled = $debugModeEnabled;
		self::$loggingEnabled = $loggingEnabled;

		// Create the default logger
		self::$defaultLogger = self::createLogger('viscacha');
		// Register the error handler and consume the error handler events with the default logger
		self::$errorHandler = ErrorHandler::register(self::$defaultLogger, array(), false, false);

		if (self::$debugModeEnabled) {
			// Set up the debug bar
			self::$debugBar = new DebugBar();
			self::$debugBar->addCollector(new PhpInfoCollector());
			self::$debugBar->addCollector(new MemoryCollector());
			self::$debugBar->addCollector(new MonologCollector(self::$defaultLogger));
			self::$debugBar->addCollector(new RequestDataCollector());
			$timeDataCollector = new TimeDataCollector();
			self::$debugBar->addCollector($timeDataCollector);
			self::$debugBar->addCollector(new SqlCollector($timeDataCollector));
			self::$debugBar->addCollector(new TemplateCollector($timeDataCollector));

			// Set up the visuals for the debug bar
			$debugbarRenderer = self::$debugBar->getJavascriptRenderer();
			$debugbarRenderer->setBaseUrl('./classes' . $debugbarRenderer->getBaseUrl());
			$debugbarRenderer->renderOnShutdownWithHead(); // TODO: Move to correct place in html code
		}
	}

	public static function error($message) {
		self::$defaultLogger->error($message);
	}

	public static function log($message) {
		self::$defaultLogger->debug($message);
	}

	public static function getDefaultLogger() {
		return self::$defaultLogger;
	}

	public static function createLogger($name) {
		$logger = new Logger($name);
		if (self::$loggingEnabled) {
			$logger->pushHandler(new StreamHandler("data/logs/{$name}.log"));
		}
		return $logger;
	}

	public static function startMeasurement($name, $description = null) {
		if (self::$debugModeEnabled) {
			self::$debugBar['time']->startMeasure($name, $description);
		}
	}

	public static function stopMeasurement($name, $params = array(), $returnTime = false) {
		if (self::$debugModeEnabled) {
			self::$debugBar['time']->stopMeasure($name, $params);
			if ($returnTime) {
				$last = array_slice(self::$debugBar['time']->getMeasures(), -1);
				return isset($last[0]['duration']) ? $last[0]['duration'] : null;
			}
		}
		return null;
	}

}

abstract class UsingTimeDataCollector extends DataCollector {

	private $collector;

	public function __construct(TimeDataCollector $collector) {
		$this->collector = $collector;
	}

	protected function getMeasures() {
		$name = $this->getName();
		return array_filter($this->collector->getMeasures(), function($value) use ($name) {
			return (isset($value['params']['type']) && $value['params']['type'] == $name);
		});
	}

}

class SqlCollector extends UsingTimeDataCollector implements Renderable, AssetProvider {

	public function __construct(TimeDataCollector $collector) {
		parent::__construct($collector);
	}

	public function collect() {
		$queries = array();
		$totalExecTime = 0;
		$measures = $this->getMeasures();
		foreach ($measures as $q) {
			$queries[] = array(
				'sql' => $q['params']['query'],
				'duration' => $q['duration'],
				'duration_str' => $this->formatDuration($q['duration'])
			);
			$totalExecTime += $q['duration'];
		}

		return array(
			'nb_statements' => count($queries),
			'accumulated_duration' => $totalExecTime,
			'accumulated_duration_str' => $this->formatDuration($totalExecTime),
			'statements' => $queries
		);
	}

	public function getName() {
		return "db";
	}

	public function getWidgets() {
		return array(
			"database" => array(
				"icon" => "database",
				"widget" => "PhpDebugBar.Widgets.SQLQueriesWidget",
				"map" => "db",
				"default" => "[]"
			),
			"database:badge" => array(
				"map" => "db.nb_statements",
				"default" => 0
			)
		);
	}

	public function getAssets() {
		return array(
			'css' => 'widgets/sqlqueries/widget.css',
			'js' => 'widgets/sqlqueries/widget.js'
		);
	}

}

class TemplateCollector extends UsingTimeDataCollector implements Renderable, AssetProvider {


	public function __construct(TimeDataCollector $collector) {
		parent::__construct($collector);
	}

	public function collect() {
		$templates = array();
		$accuRenderTime = 0;
		$failed = 0;
		$measures = $this->getMeasures();
		foreach ($measures as $t) {
			$templates[] = array(
				'name' => $t['params']['file'],
				'render_time' => $t['duration'],
				'render_time_str' => $this->formatDuration($t['duration'])
			);
			$accuRenderTime += $t['duration'];
			$failed += $t['params']['error'] ? 1 : 0;
		}

		return array(
			'nb_templates' => count($templates),
			'nb_templates_failed' => $failed,
			'templates' => $templates,
			'accumulated_render_time' => $accuRenderTime,
			'accumulated_render_time_str' => $this->formatDuration($accuRenderTime)
		);
	}

	public function getName() {
		return "tpl";
	}

	public function getWidgets() {
		return array(
			"template" => array(
				"icon" => "leaf",
				"widget" => "PhpDebugBar.Widgets.TemplatesWidget",
				"map" => "tpl",
				"default" => json_encode(array('templates' => array()))
			),
			"template:badge" => array(
				"map" => "tpl.nb_templates",
				"default" => 0
			)
		);
	}

	public function getAssets() {
		return array(
			'css' => 'widgets/templates/widget.css',
			'js' => 'widgets/templates/widget.js'
		);
	}

}
