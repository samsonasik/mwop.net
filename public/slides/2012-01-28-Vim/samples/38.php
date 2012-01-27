<?php
set_include_path(implode(PATH_SEPARATOR, array(
    '.',
    '/usr/local/zend/share/pear',
    get_include_path(),
)));

require_once 'PHPUnit/Framework/TestCase.php';

class TestSomething extends PHPUnit_Framework_TestCase
{
    public function testEquality()
    {
        $this->assertEquals(4, 2+3);
    }
}
