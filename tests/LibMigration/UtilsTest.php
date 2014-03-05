<?php
namespace Test\LibMigration;

use LibMigration\Utils;

class UtilsTest extends \PHPUnit_Framework_TestCase
{
  public function testArrayKeyLargestLength()
  {
    $tArray = array(
      'aaa' => "",
      'aaabbb' => "",
      'aaacceee' => "",
      'aaeea' => "",
    );
    $this->assertEquals(8, Utils::arrayKeyLargestLength($tArray));

    $tArray = array(
        'gewaof' => "",
        'geweeeeeeaofgewaof' => "",
        'efwaaq3dececa' => "",
        '1' => "",
    );
    $this->assertEquals(18, Utils::arrayKeyLargestLength($tArray));
  }

  public function testCamelize()
  {
    $this->assertEquals('AaaEeeBbb', Utils::camelize("aaa_eee_bbb"));
  }

  public function testUnderscore()
  {
    $this->assertEquals('aaa_eee_bbb', Utils::underscore("AaaEeeBbb"));
  }


}