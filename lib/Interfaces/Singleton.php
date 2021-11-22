<?php
namespace Underpin\Interfaces;

interface Singleton {

	public static function instance() : self;

}