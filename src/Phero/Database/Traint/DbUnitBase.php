<?php
namespace Phero\Database\Traint;

use Phero\Database\Enum as enum;
use Phero\Database\Enum\JoinType;
use Phero\Database\Model;
use Phero\Map\NodeReflectionClass;

/**
 * 用来设置数据库实体类的一些携带数据
 * 以及基础功能
 */
trait DbUnitBase {
	private $model;
	/**
	 * 初始化实体类中的数据
	 * 可以是属性名和数据
	 * ['id'=>1,'username'=>'asdf']
	 * 也可以传(设置这些字段未查询字段)
	 * ['id','username']
	 * @param [type]  $values   [
	 *                          array:标示启用的列  【带有费数值key的就会进行赋值】
	 *                          false :禁用原本所有的数据
	 *                          null :不填
	 * ]
	 * @param boolean $IniFalse [反向设置false]
	 */
	public function __construct($values = null, $IniFalse = true) {
		$this->model = new Model();
		if (isset($values)) {
			//判断是否吧除了需要初始化的值之外的数据设置成false[就是不需要查询]
			if ($IniFalse && count($values) > 0 || $values == false) {
				$this->allFalse();
				// if (is_string($values)) {
				// 	$this->field($values);
				// }
			}
			if (is_array($values)) {
				$setFiled = false;
				$keys = array_keys($values);
				//判断是否是数值key的数组
				if (is_numeric($keys[0])) {
					$setFiled = true;
				}
				foreach ($values as $key => $value) {
					if ($setFiled) {
						$this->$value = true;
					} else {
						$this->$key = $value;
					}
				}
			}

		}
	}

	private function allFalse() {
		$NodeReflectionClass = new NodeReflectionClass($this);
		$propertys = $NodeReflectionClass->getPropertieNames();
		//初始化所有的值未false
		foreach ($propertys as $key => $value) {
			$this->$value = false;
		}
	}
	//查询条件列表
	protected $where = [];
	protected $join = [];
	//数据源join方式
	protected $datasourseJoinType;
	//行列模板
	protected $fieldTemp = [];
	//分组
	protected $groupBy;
	//范围
	protected $limit;
	//排序
	protected $order;

	protected $whereGroup = false;
	/**
	 * Constraint自定义
	 * @var array
	 */
	protected $field = [];
	protected $datasourse = [];

	public function getWhere() {return $this->where;}
	public function getJoin() {return $this->join;}
	public function getFieldTemp() {return $this->fieldTemp;}
	public function getField() {return $this->field;}
	public function getDatasourse() {return $this->datasourse;}
	public function getGroup() {return $this->groupBy;}
	public function getLimit() {return $this->limit;}
	public function getOrder() {return $this->order;}
	/**
	 * 获取单独添加数据源时设置的Join类型
	 * @return [type] [description]
	 */
	public function getDatasourseJoinType() {
		return $this->datasourseJoinType;
	}

	/**
	 * 设置条件语句
	 * @param  [type] $where [需要的参数
	 *                       ×数据库字段---index:0
	 *                       ×value数据---index:1
	 *                       -可选
	 *                       		比较符号 可选---index:2(默认未等号)
	 *                       		下个字段连接符---index:3(默认为空字符串)
	 * ]
	 * @param  [type] $from  [来自那个表  如果是多表链接的话]
	 * @return [type]        [description]
	 */
	public function where($where, $from = null, $group = false) {
		if (!isset($where) || count($where) < 2) {
			return;
		}
		if (isset($from)) {
			$where['from'] = $from;
		}
		$group = $this->whereGroup;
		if ($group !== false) {
			$where['group'] = $group;
		}
		$this->where[] = $where;
		return $this;
	}

	/**
	 * 批量设置where
	 * 一次批量是一个分组
	 * @param  Array  $wheres [
	 *                       ×数据库字段---index:0
	 *                       ×value数据---index:1
	 *                       -可选
	 *                       		比较符号 可选---index:2(默认未等号)
	 *                       		下个字段连接符---index:3(默认为空字符串)]
	 *                       		所属的表或者表的别名
	 * @param  [type] $from   [description]
	 * @return [type]         [description]
	 */
	// public function wheres(Array $wheres) {
	// 	$group = count($this->where);
	// 	foreach ($wheres as $key => $value) {
	// 		$from = isset($value[4]) ? $value[4] : null;
	// 		$this->where($value, $from, $group);
	// 	}
	// }
	/**
	 * 表链接
	 * $on 通过这样的替换符号标示
	 * $：标示是被关联的Entiy
	 * #：标示的关联的Entiy
	 *   $Entiy->join(new XX(),"$.uid=#.id");
	 */
	public function join($Entiy, $on, $joinType = JoinType::inner_join) {
		$this->join[] = [$Entiy, $on, $joinType];
		return $this;
	}

	public function field($field) {
		if (is_array($field)) {
			foreach ($field as $key => $value) {
				$this->field[] = $value;
			}
		} else {
			$this->field[] = $field;
		}
		return $this;
	}
	/**
	 * 添加数据源
	 * @param  [type] $table [数据源  可以是子查询  也可以是一个表名]
	 * @param  [type] $as    [如果是子查询就必须要有别名]
	 * @param  [type] $on    [关联条件]
	 * @param  [type] $join  [join方式]
	 */
	public function datasourse($table, $as, $on, $join = null) {
		$this->datasourse[] = [$table, $as, $on, $join];
		return $this;
	}
	/**
	 * 设置字段的函数  字段用？标示
	 * 如  count(?)
	 * @param  [type] $temp [description]
	 * @return [type]       [description]
	 */
	public function fieldTemp($temp = []) {
		$this->fieldTemp = $temp;
		return $this;
	}
	/**
	 * 设置单独添加数据源时设置的Join类型
	 * @param  [type] $JoinType [description]
	 * @return [type]           [description]
	 */
	public function datasourseJoinType($JoinType) {
		$this->datasourseJoinType = $JoinType;
		return $this;
	}

	/**
	 * 分组列
	 * @param  [type] $field [description]
	 * @return [type]        [description]
	 */
	public function group($field) {
		$this->groupBy = $field;
		return $this;
	}

	/**
	 * 查询范围
	 * @param  [type] $start [description]
	 * @param  [type] $end   [description]
	 * @return [type]        [description]
	 */
	public function limit($start, $end = null) {
		$this->limit = [$start, $end];
		return $this;
	}

	/**
	 * 设置排序
	 * @param  [type] $field      [description]
	 * @param  [type] $order_type [description]
	 * @return [type]             [description]
	 */
	public function order($field, $order_type = null) {
		$this->order = [$field, $order_type];
		return $this;
	}

	private $dumpSql;
	//ORM
	public function select() {
		var_dump($this->model->getPdoDriverType());
		$result = $this->model->select($this);
		$this->dumpSql = $this->model->getSql();
		return $result;
	}
	/**
	 * 通过本实体类更新数据
	 * @param  boolean $transaction_type [更新时是否开启一个事务]
	 * @return [type]                    [description]
	 */
	public function update($transaction_type = false) {
		if ($transaction_type) {
			$this->model->transaction(Model::begin_transaction);
		}
		$result = $this->model->update($this);
		$this->dumpSql = $this->model->getSql();
	}
	/**
	 * [通过本实体类删除数据]
	 * @param  boolean $transaction_type [更新时是否开启一个事务]
	 * @return [type]                    [description]
	 */
	public function delete($transaction_type = false) {
		if ($transaction_type) {
			$this->model->transaction(Model::begin_transaction);
		}
		$result = $this->model->delete($this);
		$this->dumpSql = $this->model->getSql();
		return $result;
	}
	/**
	 * [通过本实体类插入数据]
	 * @param  boolean $transaction_type [更新时是否开启一个事务]
	 * @return [type]                    [description]
	 */
	public function insert($transaction_type = false) {
		if ($transaction_type) {
			$this->model->transaction(Model::begin_transaction);
		}
		$result = $this->model->insert($this);
		$this->dumpSql = $this->model->getSql();
		return $result;
	}

	public function replace($transaction_type = false) {
		if ($this->model->getPdoDriverType() != enum\PdoDriverType::PDO_MYSQL) {
			throw new \Exception("mysql驱动才支持replace", 1);
		}
		if ($transaction_type) {
			$this->model->transaction(Model::begin_transaction);
		}
		$result = $this->model->insert($this, true);
		$this->dumpSql = $this->model->getSql();
		return $result;
	}

	public function rollback() {
		$this->model->transaction(Model::rollback_transaction);
	}
	public function commit() {
		$this->model->transaction(Model::commit_transaction);
	}
}