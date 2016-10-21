<?php
namespace Solarfield\Batten;

use Exception;

class UnresolvedRouteException extends \Exception {
	private $routeInfo;

	public function getRouteInfo() {
		return $this->routeInfo;
	}

	public function __construct($message = null, $code = 0, Exception $previous = null, $aRouteInfo = null) {
		parent::__construct($message, $code, $previous);
		$this->routeInfo = $aRouteInfo;
	}
}
