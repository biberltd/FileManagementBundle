<?php
/**
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com) (C) 2015
 * @license     GPLv3
 *
 * @date        15.01.2016
 */
namespace BiberLtd\Bundle\FileManagementBundle\Exception;

class InvalidFileTypeException extends \Exception{
	public function __construct($type){
		parent::__construct($type.' is not a valid file type. Allowed values are: a, i, v, f, p, d, s');
	}
}