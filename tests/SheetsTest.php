<?php

namespace GoogleSheet\tests;

use Mockery;
use PHPUnit_Framework_TestCase;

use Google_Service_Sheets;

use PulkitJalan\Google\Client;

use GoogleSheets\Sheets;

class SheetsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Sheets
     */
    protected $sheet;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $spreadsheetId;

    /**
     * @var string
     */
    protected $spreadsheetTitle;

    /**
     * @var string
     */
    protected $sheetTitle;

    /**
     * @var integer
     */
    protected $sheetId;

    public function setUp()
    {
        parent::setUp();

        if ($this->checkDevConfig()) {
            $config = __DIR__ . '/data/dev-credentials.json';
            include __DIR__ . '/data/dev-config.php';
        } else {
            $config = __DIR__ . '/data/service-account.json';
        }

        $this->client = new Client([
            'service' => [
                'enable' => true,
                'scopes' => [Google_Service_Sheets::DRIVE, Google_Service_Sheets::SPREADSHEETS],
                'credentials' => $config,
            ]
        ]);

        $this->sheet = new Sheets();

        $this->sheet->setService($this->client->make('Sheets'));
    }

    public function tearDown()
    {
        Mockery::close();
    }

    /**
     * not mock
     *
     * @return bool
     */
    private function checkDevConfig()
    {
        return file_exists(__DIR__ . '/data/dev-config.php');
    }

    public function testSheetsInstance()
    {
        $this->assertInstanceOf('GoogleSheets\Sheets', $this->sheet);
    }

    public function testService()
    {
        $this->assertInstanceOf('Google_Service_Sheets', $this->sheet->getService());
    }

    public function testSpreadsheetProperties()
    {
        if (!$this->checkDevConfig()) {
            return;
        }

        $properties = $this->sheet->spreadsheet($this->spreadsheetId)->spreadsheetProperties();

//        dd($properties);

        $this->assertNotEmpty($properties->title);
    }

    public function testSheetProperties()
    {
        if (!$this->checkDevConfig()) {
            return;
        }

        $properties = $this->sheet->spreadsheet($this->spreadsheetId)->sheet($this->sheetTitle)->sheetProperties();

//        dd($properties);

        $this->assertEquals($this->sheetTitle, $properties->title);
    }


    public function testSheetList()
    {
        if (!$this->checkDevConfig()) {
            return;
        }

        $list = $this->sheet->spreadsheet($this->spreadsheetId)->sheetList();

        $this->assertGreaterThan(1, count($list));
    }

    public function testSpreadSheetList()
    {
        if (!$this->checkDevConfig()) {
            return;
        }

        $this->sheet->setDriveService($this->client->make('drive'));

        $lists = $this->sheet->spreadsheetList();

        $this->assertGreaterThan(0, count($lists));
    }

    public function testSheetById()
    {
        if (!$this->checkDevConfig()) {
            return;
        }

        $list = $this->sheet->spreadsheet($this->spreadsheetId)->sheetList();
        $sheet = array_get($list, $this->sheetId);

        $this->assertNotEmpty($sheet);
    }

    public function testSheetValuesBatchGet()
    {
        if (!$this->checkDevConfig()) {
            return;
        }

        $sheets = $this->sheet->spreadsheets_values->batchGet($this->spreadsheetId, ['ranges' => $this->sheetTitle]);

        $this->assertNotEmpty($sheets);
    }

    public function testSheetValuesGet()
    {
        if (!$this->checkDevConfig()) {
            return;
        }

        $sheets = $this->sheet
            ->spreadsheet($this->spreadsheetId)
            ->sheet($this->sheetTitle)
            ->all();

        $this->assertGreaterThan(1, count($sheets));
    }

    public function testSheetValuesFirst()
    {
        if (!$this->checkDevConfig()) {
            return;
        }

        $sheets = $this->sheet
            ->spreadsheet($this->spreadsheetId)
            ->sheet($this->sheetTitle)
            ->first();

        $this->assertNotEmpty($sheets);
    }

    public function testSheetValuesRange()
    {
        if (!$this->checkDevConfig()) {
            return;
        }

        $sheets = $this->sheet
            ->spreadsheet($this->spreadsheetId)
            ->sheet($this->sheetTitle)
            ->range('A1:E3')
            ->all();

//        dd($sheets);
        $this->assertEquals(3, count($sheets));

    }

    public function testSheetValuesMajorDimension()
    {
        if (!$this->checkDevConfig()) {
            return;
        }

        $sheets = $this->sheet
            ->spreadsheet($this->spreadsheetId)
            ->sheet($this->sheetTitle)
            ->range('A1:E3')
            ->majorDimension('COLUMNS')
            ->all();

//        dd($sheets);
        $this->assertEquals(5, count($sheets));
    }

    public function testSheetUpdate()
    {
        if (!$this->checkDevConfig()) {
            return;
        }

        $response = $this->sheet
            ->spreadsheet($this->spreadsheetId)
            ->sheet('test')
            ->range('A1:B3')
            ->update([['test', 'test2'], ['test3']]);

//        dd($response);
        $this->assertEquals($this->spreadsheetId, $response->getSpreadsheetId());
    }


}
