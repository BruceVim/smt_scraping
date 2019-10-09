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
    public $targetUrl = 'http://finance.sina.com.cn/zt_d/2018zgzf1000/';

    private $MysqlHost = 'localhost';
    private $MysqlUser = 'root';
    private $MysqlPasswd = '';
    private $MysqlDB = '';
    /*	private $MysqlHost = 'localhost';
        private $MysqlUser = 'root';
        private $MysqlPasswd = '';
        private $MysqlDB = 'test';*/

    public $MysqlConn;
    public $hotel_name = '深圳大梅沙湾游艇度假酒店';

    public function Crawler( $driver, $index ) {

       /* $js="var q=document.documentElement.scrollTop=".($index * 10000 + ( 100000 ));
        $sScriptResult = $driver -> executeScript( $js, array() );

        $js="var q=document.documentElement.scrollTop=".($index * 10000 - ( 10000 ));
        $sScriptResult = $driver -> executeScript( $js, array() );

        $driver -> wait(10);*/

        /*		$target = $driver -> findElements(
                     WebDriverBy::className('hotelCommentGroupInfo')
                );
                $sScriptResult = $driver -> executeScript( "arguments[0].scrollIntoView();", $target );*/

        #获取多个元素
        /*$elements = $driver -> findElements(
            WebDriverBy::className('tree-ellips-line6')
        );

        $elements_id = $driver -> findElements(
            WebDriverBy::className('js_btn_useful')
        );
        $index = $index * 10000 + ( 100000 ) + 100;
        #页面跳动

        $js="var q=document.documentElement.scrollTop=".($index + 10000 );
        $sScriptResult = $driver -> executeScript( $js, array() );

        $js="var q=document.documentElement.scrollTop=50";
        $sScriptResult = $driver -> executeScript( $js, array() );*/

       /* #目标选择
        $target_index = (($this -> num) - 2) > 0 ? (($this -> num) - 2) : 0;

        $xpath = "//div[position()>".$target_index ." and @class='dn hotel-t-b-border']//p[@class='tree-ellips-line6']";
        $xpath_id = "//div[position()>".$target_index ." and @class='dn hotel-t-b-border']//div[@class='fr js_btn_useful']";

        $elements = $driver -> findElements(
            WebDriverBy::xpath($xpath)
        );

        $elements_id = $driver -> findElements(
            WebDriverBy::xpath($xpath_id)
        );

        $old_num = $this -> num;
        $Len = count($elements);
        $this -> num += $Len;

        echo '<span style="color:red">index：'. $target_index .'，本次获取数量：'. $Len .'，总登记数量：'. $this -> num .'</span>';
        echo "<hr>";

        for ($i=0; $i < $Len ; $i++) {

            $comment_id = $elements_id[$i] -> getAttribute('data-commentid');
            $comment_detail = $elements[$i]  -> getText();

            echo $comment_id .' - '. $comment_detail ;
            echo "<hr>";

            $elements[$i]  -> getText() ;

            if ($elements_id[$i] -> getAttribute('data-commentid') > 0 ) {

                $sql = 'SELECT count(`comment_id`) FROM `THotelComment`
                        WHERE `comment_id` = '. $comment_id;
                $res = mysqli_query($this -> MysqlConn,$sql);
                $data = $res->fetch_row();

                if ( $data[0] > 0) {
                    continue;
                } else {

                    $sql2 = "INSERT INTO `THotelComment` (`hotel_name`, `comment_id`, `comment_detail`, `create_time`) VALUES ('". $this -> hotel_name ."', '". $comment_id ."', '" . $comment_detail . "', '". date("Y-m-d H:i:s") ."') ";

                    $result = mysqli_query($this -> MysqlConn,$sql2);

                    unset($elements[$i]);
                    unset($comment_id);
                    unset($comment_detail);

                }
                

                $sql2 = "INSERT INTO `THotelComment` (`hotel_name`, `comment_id`, `comment_detail`, `create_time`) VALUES ('". $this -> hotel_name ."', '". $comment_id ."', '" . $comment_detail . "', '". date("Y-m-d H:i:s") ."') ";

                $result = mysqli_query($this -> MysqlConn,$sql2);

                unset($elements[$i]);
                unset($comment_id);
                unset($comment_detail);


            } else {
                continue;
            }*/

             $elements_id = $driver -> findElements(
             WebDriverBy::className('js_btn_useful');

             var_dump( $elements_id );die;

        }

        if ( $Len == $this -> num ) {
            /*echo '<span style="color:red">已登记数量：'. $this -> num .'</span>';
            echo "<hr>";*/
            $this -> Crawler( $driver, $this -> num );

        }

        if ( $old_num == $this -> num ) {
            $this -> reNum ++ ;
            if ( $this -> reNum >= $this -> UpNum ) {
                $driver->quit();
                return;
            }
        }

        $this -> Crawler( $driver, $this -> num );



    }



    public function RunApp() {

       /* $this -> MysqlConn = mysqli_connect($this -> MysqlHost, $this -> MysqlUser, $this -> MysqlPasswd);

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

        */



        $host = 'http://localhost:9515';

        $driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome(), 50000);

        $driver->get( $this -> targetUrl);

    

        $this -> Crawler( $driver, 0 );


        echo "The title is '" . $driver->getTitle() . "'\n";

        echo "The current URI is '" . $driver->getCurrentURL() . "'\n";



        //关闭浏览器

        $driver->quit();

    }



}



$App = new CrawlerApp;
$App -> RunApp();
return;



if (response.getStatus() == Response.Status.OK.getStatusCode() && response.hasEntity()) {
  InputStream inputStream = (InputStream)response.getEntity();
  try {
    String header = response.getHeaderString("Content-Disposition");
    if(header != null &amp;&amp; !("").equals(header)) {
      if(header.contains("filename")){
        //header value will be something like:
        //attachment; filename=10000000354_2016-01-15T23:09:54.438+0000.zip
        int length = header.length();
        String fileName = header.substring(header.indexOf("filename="),length);
        System.out.println("filenameText " + fileName);
        String [] str = fileName.split("=");
        System.out.println("fileName: " + str[1]);
        //replace "/Users/anauti1/Documents/" below with your values
        File reportFile = new File("/Users/anauti1/Documents/" + str[1].toString());
        OutputStream outStream = new FileOutputStream(reportFile);
        byte[] buffer = new byte[8 * 1024];
        int bytesRead;
        while ((bytesRead = inputStream.read(buffer)) != -1) {
          outStream.write(buffer, 0, bytesRead);
        }
        IOUtils.closeQuietly(inputStream);
        IOUtils.closeQuietly(outStream);
      }
    }
  }
  catch (Exception ex){
    System.out.print("Exception: " + ex.getMessage());
  }
}