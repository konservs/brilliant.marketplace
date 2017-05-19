<?php
/**
 * Country page (usually sobdomain or different domain).
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

class Model_countries_country extends \Brilliant\MVC\BModel{
	/**
	 * Model - get data
	 */
	public function getData($segments){
		$data=new stdClass;
		return $data;
		}
	}
