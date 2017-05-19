<?php
/**
 * Component to work with countries.
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

class Controller_countries extends \Brilliant\MVC\BController{
	/**
	 *
	 */
	public function run($segments){
		$model=$this->LoadModel($segments['view']);
		if(empty($model)){
			return 'Countries: could not load model!';
			}
		$view=$this->LoadView($segments['view']);
		if(empty($view)){
			return 'Countries: could not load view "'.$segments['view'].'"!';
			}
		return($view->generate($model->getData($segments)));
		}
	}
