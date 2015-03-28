<?php
namespace batten;

interface EventInterface {
	public function getType();
	public function getTarget();
	public function getRelatedTarget();
}
