<?php
namespace Batten;

interface EventInterface {
	public function getType();
	public function getTarget();
	public function getRelatedTarget();
}
