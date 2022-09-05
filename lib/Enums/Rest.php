<?php

namespace Underpin\Enums;

enum Rest: string {

	case Get = 'GET';
	case Post = 'POST';
	case Put = 'PUT';
	case Delete = 'DELETE';

}