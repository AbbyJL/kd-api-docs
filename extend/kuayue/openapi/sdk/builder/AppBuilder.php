<?php

interface  AppBuilder
{
	public function sandBox();

	public function uat();

	public function prod();

	public function api($api);

}

