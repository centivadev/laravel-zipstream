<?php

namespace STS\ZipStream\Tests;

use STS\ZipStream\ZipStream;
use Zip;
use Orchestra\Testbench\TestCase;
use STS\ZipStream\ZipStreamFacade;
use STS\ZipStream\ZipStreamServiceProvider;

class ZipTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ZipStreamServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Zip' => ZipStreamFacade::class
        ];
    }

    public function testSaveZipOutput()
    {
        $testrun = microtime();
        file_put_contents("/tmp/test1.txt", "this is the first test file for test run $testrun");
        file_put_contents("/tmp/test2.txt", "this is the second test file for test run $testrun");

        /** @var ZipStream $zip */
        $zip = Zip::create("my.zip", ["/tmp/test1.txt", "/tmp/test2.txt"]);
        $sizePrediction = $zip->predictZipSize();
        $zip->saveTo("/tmp");

        $this->assertTrue(file_exists("/tmp/my.zip"));
        $this->assertEquals($sizePrediction, filesize("/tmp/my.zip"));

        $z = zip_open("/tmp/my.zip");
        $this->assertEquals("this is the first test file for test run $testrun", zip_entry_read(zip_read($z)));

        unlink("/tmp/my.zip");
    }

    public function testSaveZip64Output()
    {
        $testrun = microtime();
        file_put_contents("/tmp/test1.txt", "this is the first test file for test run $testrun");
        file_put_contents("/tmp/test2.txt", "this is the second test file for test run $testrun");
        exec('dd if=/dev/zero count=5120 bs=1048576 >/tmp/bigfile.txt');
        exec('dd if=/dev/zero count=1024 bs=1048576 >/tmp/medfile.txt');

        /** @var ZipStream $zip */
        $zip = Zip::create("my.zip", ["/tmp/test1.txt", "/tmp/test2.txt", "/tmp/bigfile.txt", "/tmp/medfile.txt"]);
        $sizePrediction = $zip->predictZipSize();
        $zip->saveTo("/tmp");

        $this->assertTrue(file_exists("/tmp/my.zip"));
        $this->assertEquals($sizePrediction, filesize("/tmp/my.zip"));

        $z = zip_open("/tmp/my.zip");
        $this->assertEquals("this is the first test file for test run $testrun", zip_entry_read(zip_read($z)));

        unlink("/tmp/my.zip");
    }
}