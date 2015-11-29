<?php
namespace Solarfield\Batten;

interface EventInterface {
	public function getType();
	public function getTarget();
	public function getRelatedTarget();
}
