<?php

namespace Underpin\Interfaces;

interface Can_Convert_To_Model{
	function to_model(): Model;
}