<?php
namespace Phero\Map\Note;

/**
 *列的信息
 */
class Field {
	/**
	 * 列的别名
	 * @var [type]
	 */
	public $alias;

	/**
	 * 数据库类型
	 * 允许的有
	 * 	string[之后可以扩展成varchar  char text ]
	 *  	int
	 *  	boolean
	 *  	[datetime]
	 * @var [type]
	 */
	public $type = \PDO::PARAM_STR;

	public static function typeTrunPdoType($type) {
		if ($type == "string") {
			return \PDO::PARAM_STR;
		} else if ($type == "int") {
			return \PDO::PARAM_INT;
		}
	}
}