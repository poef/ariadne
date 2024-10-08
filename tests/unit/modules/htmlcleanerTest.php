<?php

require_once(AriadneBasePath."/modules/mod_htmlcleaner.php");

class htmlcleanerTest extends AriadneBaseTest
{
	public function testSimple() {
		$html = '<html><head><title>test</title></head><body>testbody</body></html>';
		$clean = htmlcleaner::cleanup($html,array());
		$this->assertEquals($html, $clean);
	}

	public function testFormSelectOptionSelected() {
		$html = '<body>testbody<form><select><option selected></option></select></form></body>';
		$clean = htmlcleaner::cleanup($html,array());
		$this->assertEquals($html, $clean);
	}

	public function testFormSelectOptionEmptyAttr() {
		$html = '<body>testbody<form><a class=""></form></body>';
		$clean = htmlcleaner::cleanup($html,array());
		$this->assertEquals($html, $clean);
	}

	public function testDataAtributesDot() {
		$html = '<body>testbody<span data.dot="dot">frml</span></body>';
		$clean = htmlcleaner::cleanup($html,array());
		$this->assertEquals($html, $clean);
	}

	public function testDataAtributesUnderscore() {
		$html = '<body>testbody<span data_underscore="underscore">frml</span></body>';
		$clean = htmlcleaner::cleanup($html,array());
		$this->assertEquals($html, $clean);
	}

	public function testDataAtributesDash() {
		$html = '<body>testbody<span data-dash="dash">frml</span></body>';
		$clean = htmlcleaner::cleanup($html,array());
		$this->assertEquals($html, $clean);
	}
	public function testCleaningAtributesDash() {
		$html = '<body>testbody<span data-dash="dash"	ar:path:=\'/some"/thing/\'     	               >frml</span' . PHP_EOL . '></body>';
		$prep = '<body>testbody<span data-dash="dash" ar:path:="/some&quot;/thing/">frml</span></body>';
		$clean = htmlcleaner::cleanup($html,array());
		$this->assertEquals($prep, $clean);
	}

	public function testDataNextingImpropperHtmlEncoding() {
		$html = '<body>testbody<span data-dash="dash><">frml</span></body>';
		$clean = htmlcleaner::cleanup($html,array());
		$this->assertEquals($html, $clean);
	}

	public function testTagDash() {
		$html = '<body><hello-world>hello world</hello-world></body>';
		$clean = htmlcleaner::cleanup($html,array());
		$this->assertEquals($html, $clean);
	}

}
?>
