<?php
// An example of using php-webdriver.

namespace Facebook\WebDriver;

use Facebook\WebDriver\Remote\DesiredCapabilities;

use Facebook\WebDriver\Remote\RemoteWebDriver;

require_once('vendor/autoload.php');

set_time_limit(0);
ignore_user_abort(true);
ini_set("memory_limit","-1");

class CrawlerApp {

    public $num = 0;
    public $reNum = 0;
    public $UpNum = 50;
    public $targetUrl = 'http://kaijiang.500.com/shtml/dlt/19052.shtml';

    private $MysqlHost = 'localhost';
    private $MysqlUser = 'root';
    private $MysqlPasswd = '';
    private $MysqlDB = 'test';

    public $dataArr = [];
    public $index = 1;
    /*	private $MysqlHost = 'localhost';
        private $MysqlUser = 'root';
        private $MysqlPasswd = '';
        private $MysqlDB = 'test';*/

    public $MysqlConn;

    public function Crawler( $driver, $page_id ) {

            $targetUrl = 'https://www.aliexpress.com/item/32443138475.html';
            $driver->get( $targetUrl );

            #获取多个元素
            // $ball_red = $driver -> findElements(
            //     WebDriverBy::className('ball_red')
            // );
            // #获取单个元素
            // $ball_blue = $driver -> findElement(
            //     WebDriverBy::className('ball_blue')
            // );

            $smt_info = [];

            #获取标题
            $_title = $driver -> findElements(
                WebDriverBy::className('product-title')
            );
            $smt_info['product_title'] = $_title[0] -> getText();
            unset( $_title );

            $_description = $driver -> findElements(
                WebDriverBy::className('product-description')
            );

            if ( $this ->findElementExsit($_description) ) {
                var_dump($_description);
                var_dump($_description[0]);
                var_dump($_description[0]  -> getText() );
            }

            

    }

    public function findElementExsit( $obj )
    {
        if( $this -> isElementExsit($driver, $obj)){
            return true;
        } else {
            $js="var q=document.documentElement.scrollTop=".(1000);
            $sScriptResult = $driver -> executeScript( $js, array() );

            $this -> findElementExsit($obj);
        }
    }


    public function RunApp() {

        $host = 'http://localhost:9515';

        $this -> MysqlConn = mysqli_connect($this -> MysqlHost, $this -> MysqlUser, $this -> MysqlPasswd);

        if (!$this -> MysqlConn) {
            exit('error('.mysqli_connect_errno().'):'.mysqli_connect_error());
            //die
        }

        if (!mysqli_select_db($this -> MysqlConn, $this -> MysqlDB )) {
            echo 'error('.mysqli_errno($this -> MysqlConn).'):'.mysqli_error($this -> MysqlConn);
            mysqli_close($this -> MysqlConn);
            die;
        }

        mysqli_set_charset($this -> MysqlConn,'utf8');

        # check connection
        if ($this -> MysqlConn -> connect_errno)
        {
            $this -> errorString = $this -> MysqlConn -> connect_error;
            return false;
        }

        #utf8
        $this -> MysqlConn -> set_charset("utf8");

        

        $driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome(), 50000);

        $this -> Crawler( $driver );

      


        // echo "The title is '" . $driver->getTitle() . "'\n";
        // echo "The current URI is '" . $driver->getCurrentURL() . "'\n";



        //关闭浏览器
        $driver->quit();

    }



}



$App = new CrawlerApp;
$App -> RunApp();
return;

