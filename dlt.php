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
    public $_begin = '19106';
    /*	private $MysqlHost = 'localhost';
        private $MysqlUser = 'root';
        private $MysqlPasswd = '';
        private $MysqlDB = 'test';*/

    public $MysqlConn;

    public function Crawler( $driver, $page_id ) {

            if ( strlen($page_id) < 5 ) {
                $page_id = '0'.$page_id;
            }
           
            $targetUrl = 'http://kaijiang.500.com/shtml/dlt/'. $page_id .'.shtml';
            $driver->get( $targetUrl );

            #获取多个元素
            $ball_red = $driver -> findElements(
                WebDriverBy::className('ball_red')
            );
            // #获取单个元素
            // $ball_blue = $driver -> findElement(
            //     WebDriverBy::className('ball_blue')
            // );
             #获取单个元素
            $ball_blue = $driver -> findElements(
                WebDriverBy::className('ball_blue')
            );

            $param = [
                'dlt_times' => $page_id ,
            ];

            #数据抓取
            foreach ($ball_red as $key => $red) {

                if ( isset($param['dlt_red']) ) {
                   $param['dlt_red'] .= ',' . $red -> getText();
                } else {
                    $param['dlt_red'] = $red -> getText();
                }
                
            }

            foreach ($ball_blue as $blue) {

                if ( isset($param['dlt_blue']) ) {
                   $param['dlt_blue'] .= ',' . $blue -> getText();
                } else {
                    $param['dlt_blue'] = $blue -> getText();
                }
                
            }

            // $param['ssq_blue'] = $ball_blue -> getText() ;
            $param['dlt_all'] = $param['dlt_red'] . ',' . $param['dlt_blue'];
           
            #查找是否已经存在
            $sql = 'SELECT * FROM `dlt`
                    WHERE `dlt_times` = \''. $page_id . "'";
            $res = mysqli_query($this -> MysqlConn,$sql);
            $data = $res->fetch_row();



            if ( !empty($data) ) {

                $year = 2000 + (int)($page_id / 1000);//可以像上例一样用mt_rand随机取一个年，也可以随便赋值。
                $time = mktime(20,20,20,4,20,$year);//取得一个日期的 Unix 时间戳;
                if (date("L",$time)==1){ //格式化时间，并且判断是不是闰年，后面的等于一也可以省略；
                    //echo $year."是闰年";
                    $index = 153;
                }else{
                    //echo $year."不是闰年";
                     $index = 154;
                }


                $_new = ( $page_id - 1 ) % 1000 == 0 ? ( $page_id - 1 - 1000 + $index  ) : ($page_id - 1);
                $this -> Crawler( $driver, $_new );
                return true;
            } else {
                $act_sql = "INSERT INTO `test`.`dlt`( `dlt_times`, `dlt_red`, `dlt_blue`, `dlt_all`) 
                            VALUES ('".$page_id."', '".$param['dlt_red']."', '".$param['dlt_blue']."', '".$param['dlt_all']."');";
            }

            $act_res = mysqli_query($this -> MysqlConn,$act_sql);

            if ( $act_res ) {

                $year = 2000 + (int)($page_id / 1000);//可以像上例一样用mt_rand随机取一个年，也可以随便赋值。
                $time = mktime(20,20,20,4,20,$year);//取得一个日期的 Unix 时间戳;
                if (date("L",$time)==1){ //格式化时间，并且判断是不是闰年，后面的等于一也可以省略；
                    //echo $year."是闰年";
                    $index = 153;
                }else{
                    //echo $year."不是闰年";
                     $index = 154;
                }

                $_new = ( $page_id - 1 ) % 1000 == 0 ? ( $page_id - 1 - 1000 + $index  ) : ($page_id - 1);
                $this -> Crawler( $driver, $_new );
                 return true;
            } else {
                
                var_dump($act_sql);
                var_dump( $page_id . '抓取失败' );
                return false;
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

        $this -> Crawler( $driver , $this -> _begin );

      


        echo "The title is '" . $driver->getTitle() . "'\n";

        echo "The current URI is '" . $driver->getCurrentURL() . "'\n";



        //关闭浏览器

        //$driver->quit();

    }



}



$App = new CrawlerApp;
$App -> RunApp();
return;

