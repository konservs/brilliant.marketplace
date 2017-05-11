<?php
namespace Application\Bugtracker;

/**
 * Basic class to control Cities
 *
 * @method \Application\CCL\City itemGet(integer $id)
 * @method \Application\CCL\City[] itemsGet(integer[] $ids)
 * @method \Application\CCL\City[] itemsFilter($params)
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
class Cities extends \Brilliant\Items\BItemsList{
	use \Brilliant\BSingleton;
	protected $tableName='cities';
	protected $tableNameLanguage='cities_lng';
	protected $itemClassName='\Application\CCL\City';
	}
