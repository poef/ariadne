<?php

class MysqlCompilerTest extends AriadneBaseTest
{
	public static function setUpBeforeClass()
	{
		self::initAriadne();
		parent::setUpBeforeClass();
	}

	protected function setUp()
	{
		global $store_config;
		if( $store_config["dbms"] !== 'mysql' ) {
			$this->marktestskipped(
				'Skipping mysql tests, current config uses: '.$store_config["dbms"]
			);
		}

	}

	function testBaseCompile() {
		global $store;
		$compiler = 'mysql_compiler';
		$compiler = new $compiler($store,'store_');
		$res = $compiler->compile('/',"owner.value = 'admin'");
		$this->assertNull($compilers->error,"Compiler error");
		$prepared["select_query"]=
		     "select distinct(store_nodes.path), store_objects.id, store_nodes.parent, store_nodes.priority, store_objects.type,  store_objects.object, UNIX_TIMESTAMP(store_objects.lastchanged) as lastchanged, store_objects.vtype from (store_prop_owner, store_nodes, store_objects)   where store_nodes.object=store_objects.id  and store_prop_owner.object=store_objects.id  and store_nodes.path like '/%'  and (   store_prop_owner.AR_value  = 'admin'  )   order by store_nodes.parent ASC, store_nodes.priority DESC, store_nodes.path ASC   limit 0, 100  ";
		$prepared["count_query"]=
				  "select count(distinct(store_nodes.path)) as count from store_prop_owner, store_nodes, store_objects  where store_nodes.object=store_objects.id  and store_prop_owner.object=store_objects.id  and store_nodes.path like '/%'  and (   store_prop_owner.AR_value  = 'admin'  ) ";
		$this->assertEquals($prepared, $res);

	}

	function testQuoteCompile() {
		global $store;
		$compiler = 'mysql_compiler';
		$compiler = new $compiler($store,'store_');
		$res = $compiler->compile('/',"owner.value = 'ad\'min'");
		$this->assertNull($compilers->error,"Compiler error");
		$prepared["select_query"] =
					 "select distinct(store_nodes.path), store_objects.id, store_nodes.parent, store_nodes.priority, store_objects.type,  store_objects.object, UNIX_TIMESTAMP(store_objects.lastchanged) as lastchanged, store_objects.vtype from (store_prop_owner, store_nodes, store_objects)   where store_nodes.object=store_objects.id  and store_prop_owner.object=store_objects.id  and store_nodes.path like '/%'  and (   store_prop_owner.AR_value  = 'ad\'min'  )   order by store_nodes.parent ASC, store_nodes.priority DESC, store_nodes.path ASC   limit 0, 100  ";
		$prepared["count_query"] =
						"select count(distinct(store_nodes.path)) as count from store_prop_owner, store_nodes, store_objects  where store_nodes.object=store_objects.id  and store_prop_owner.object=store_objects.id  and store_nodes.path like '/%'  and (   store_prop_owner.AR_value  = 'ad\'min'  ) ";
		$this->assertEquals($prepared, $res);
	}

	function testEmptyCompile() {
		global $store;
		$compiler = 'mysql_compiler';
		$compiler = new $compiler($store,'store_');
		$res = $compiler->compile('/system/',"");
		$this->assertNull($compilers->error,"Compiler error");
		$prepared["select_query"]=
		     "select distinct(store_nodes.path), store_objects.id, store_nodes.parent, store_nodes.priority, store_objects.type,  store_objects.object, UNIX_TIMESTAMP(store_objects.lastchanged) as lastchanged, store_objects.vtype from (store_nodes, store_objects)   where store_nodes.object=store_objects.id  and store_nodes.path like '/system/%'   order by store_nodes.parent ASC, store_nodes.priority DESC, store_nodes.path ASC   limit 0, 100  ";
		$prepared["count_query"]=
				  "select count(distinct(store_nodes.path)) as count from store_nodes, store_objects  where store_nodes.object=store_objects.id  and store_nodes.path like '/system/%' ";
		$this->assertEquals($prepared, $res);

	}

	function testInvalidCompile() {
		global $store;
		$compiler = 'mysql_compiler';
		$compiler = new $compiler($store,'store_');
		$res = $compiler->compile('/system/'," time.ctim'e = 42");
		$this->assertNull($res);
	}

	function testInvalidCompile2() {
		global $store;
		$compiler = 'mysql_compiler';
		$compiler = new $compiler($store,'store_');
		$res = $compiler->compile('/system/'," limit 10,a ");
		$this->assertNull($res);

	}

	function testOrderOnly() {
		global $store;
		$compiler = 'mysql_compiler';
		$compiler = new $compiler($store,'store_');
		$res = $compiler->compile('/system/',"order by none,path");
		$prepared = array (
		    'select_query' => "select distinct(store_nodes.path), store_objects.id, store_nodes.parent, store_nodes.priority, store_objects.type,  store_objects.object, UNIX_TIMESTAMP(store_objects.lastchanged) as lastchanged, store_objects.vtype from (store_nodes, store_objects)   where store_nodes.object=store_objects.id  and store_nodes.path like '/system/%'   order by   store_nodes.path  ASC    limit 0, 100  ",
			 'count_query' => "select count(distinct(store_nodes.path)) as count from store_nodes, store_objects  where store_nodes.object=store_objects.id  and store_nodes.path like '/system/%' "
		 ) ;
		$this->assertEquals($prepared, $res);

	}

	function testOrderLimitOnly() {
		global $store;
		$compiler = 'mysql_compiler';
		$compiler = new $compiler($store,'store_');
		$res = $compiler->compile('/system/',"order by none limit 10,42");
		$this->assertNull($compilers->error,"Compiler error");
		$prepared = array (
		    'select_query' => "select distinct(store_nodes.path), store_objects.id, store_nodes.parent, store_nodes.priority, store_objects.type,  store_objects.object, UNIX_TIMESTAMP(store_objects.lastchanged) as lastchanged, store_objects.vtype from (store_nodes, store_objects)   where store_nodes.object=store_objects.id  and store_nodes.path like '/system/%'    limit 10, 42  ",
			 'count_query' => "select count(distinct(store_nodes.path)) as count from store_nodes, store_objects  where store_nodes.object=store_objects.id  and store_nodes.path like '/system/%' "
		 ) ;
		$this->assertEquals($prepared, $res);

	}
}
?>
