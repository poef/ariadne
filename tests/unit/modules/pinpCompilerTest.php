<?php

require_once(AriadneBasePath."/modules/mod_pinp.phtml");

class pinpCompilerTest extends AriadneBaseTest
{
	public function testBaseCompile() {
		$template = <<<'EOD'
<pinp> $test = 'test'; </pinp>
EOD;
		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertEquals("<?php  \$object->test = 'test';  ?>",$res);
		$this->assertTrue((bool)$res);
	}

	/*
	public function testObjectVariables() {
		$template = <<<'EOD'
<pinp>
	$test = ar('store')->rememberShortcuts;
	ar('store')->rememberShortcuts = false;
</pinp>
EOD;
		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}
	 */


	public function testclassOperator() {
		$template = <<<'EOD'
<pinp>
	MyClass::CONST_VALUE;
	parent::myFunc();
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function testclone() {
		$template = <<<'EOD'
<pinp>
  $a = clone($b);
  $b = clone $b;
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function testcomments() {
		$template = <<<'EOD'
<pinp>
// This is a comment
/*
  This is a multiline comment
*/
# This is also a comment
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function testdefine() {
		$template = <<<'EOD'
<pinp>
	define("FOO", "BAR");
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function testderef() {
		$template = <<<'EOD'
<pinp>
	$func = "readfile";
	${$func}();

	$$a;
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function testfunctionCalls() {
		$template = <<<'EOD'
<pinp>
	$a->_frop();
	_frop();
	$a->{"_frop"}();
	$$a();
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function testheredoc() {
		$template = <<<'EOD'
<?php



<pinp>
$MyVar = "frop";
$frop = "Mijn string";
$str = <<<EOT
   Example of string {$frop}
spanning multiple lines
using heredoc syntax.

MyVar: $MyVar;
De inhoud van de $MyVar variabel is: ${$MyVar}

EOT;

echo "$str\n";
</pinp>

EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function testmagicMethods() {
		$template = <<<'EOD'
<pinp>
	$object->_call("phpFunc", array());
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function teststring() {
		$template = <<<'EOD'
<pinp>
$a = "MyVar";
$MyVar = "MyVar Value";
echo "run(\${a}): ${"a"} abc\n";
echo "run({\$a}): {$a} abc\n";
echo "run(\${\$a}): ${$a.substr("", 0, 0)} abc\n";
echo "run(\$a): $a[0]->frop()
 abc xyz\n";
echo "frop: $$a;\n";

</pinp>

EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}


	public function testtypeCasting() {
		$template = <<<'EOD'
<pinp>
	$bool_a		= (bool) 1;
	$bool_b		= (boolean) 1;
	$int_a		= (int) "2";
	$int_b		= (integer) "3";
	$float_a	= (float) "1.1";
	$float_b	= (double) "1.2";
	$float_c	= (real) "1.3";
	$string_a	= (string) 1;
	$array_a 	= (array) null;
	$object_a	= (object) Array("a" => "1", "b" => "2");
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$this->assertTrue((bool)$res);
	}

	public function testObjectArrayAccess() {
		$template = <<<'EOD'
<pinp>
	$res = range(0,10)[5];
	return $res;
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new object(); ?'.'>'.$res);
		$this->assertEquals(5,$ret);
	}

	public function testFluentInterface() {
		$template = <<<'EOD'
<pinp>
	$res = ar('error')->raiseError('test',42)->getMessage();
	return $res;
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new object(); ?'.'>'.$res);
		$this->assertEquals('test',$ret);
	}

	public function testCurlyBrace() {
		$template = <<<'EOD'
<pinp>
	$var = array (0,1,42,3);
	$test = 2;
	return $var[$test];
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new object(); ?'.'>'.$res);
		$this->assertEquals(42,$ret);
	}

	public function testSandboxArrayAccess() {
		$template = <<<'EOD'
<pinp>
	$result['foo'] = 'bar';
	return $result['foo'];
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new object(); ?'.'>'.$res);
		$this->assertEquals('bar',$ret);
	}

	public function testReferences() {
		$template = <<<'EOD'
<pinp>
	$frop = 'frop';
	$foo = &$frop;
	$bar =& $frop;
	$frop = 'frml';	
	return array( $foo, $bar );
</pinp>
EOD;

		$compiler = new pinp("header", "object->", "\$object->_");
		$res = $compiler->compile($template);
		$this->assertNull($compiler->error);
		$ret = eval(' $object = new object(); ?'.'>'.$res);
		$this->assertEquals(['frml','frml'], $ret);
	}

		

}
