<?php

interface ApiBuilder
{

	public function body($body);

	public function header($key, $value);

	public function request();

}

