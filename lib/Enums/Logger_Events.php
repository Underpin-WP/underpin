<?php

namespace Underpin\Enums;


enum Logger_Events {

	case ready;
	case muted;
	case unmuted;
	case volume_changed;

}