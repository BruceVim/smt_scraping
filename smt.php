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


    private $MysqlHost = 'localhost';
    private $MysqlUser = 'root';
    private $MysqlPasswd = '';
    private $MysqlDB = 'test';

    public $dataArr = [];
    public $index = 1;

    public $attr = [
        'has_img' => 0,
        'has_span' => 0,
        ];
    /*	private $MysqlHost = 'localhost';
        private $MysqlUser = 'root';
        private $MysqlPasswd = '';
        private $MysqlDB = 'test';*/

    public $MysqlConn;

    public $driver;

    public $smt_info;

    public function Crawler() {

            $targetUrl = 'https://www.aliexpress.com/item/4000156718164.html';
            $this -> driver -> get( $targetUrl );

            #获取多个元素
            // $ball_red = $this -> driver -> findElements(
            //     WebDriverBy::className('ball_red')
            // );
            // #获取单个元素
            // $ball_blue = $this -> driver -> findElement(
            //     WebDriverBy::className('ball_blue')
            // );

            #获取标题
            $_title = $this -> driver -> findElements(
                WebDriverBy::className('product-title')
            );
            $_title_str = $_title[0] -> getText();
            if( strlen($_title_str) > 0 )
            {
                $this -> smt_info['product_title'] = $_title[0] -> getText();
            }
            unset( $_title );

            #获取描述
            $localtion = WebDriverBy::className('product-description');
            if ( $this ->findElementExsit( $localtion) ) {
                $_description = $this -> driver -> findElements(
                    $localtion
                );
                $_description_str = $_description[0] -> getAttribute('innerHTML');
                if( strlen($_description_str) > 0 )
                {
                    $this -> smt_info['product_description'] = addslashes($_description[0] -> getAttribute('innerHTML'));
                }

            }
            unset($localtion);
            unset($_description);

            #获取价格区间
            $_price_range = $this -> driver -> findElements(
                WebDriverBy::className('product-price-value')
            );
            $this -> smt_info['product_msrp'] = $_price_range[0] -> getAttribute('innerHTML');
            $price_range_arr = explode(' ', $this -> smt_info['product_msrp']);
            $this -> smt_info['product_msrp_min'] = str_replace('$', '', $price_range_arr[1]);
            $this -> smt_info['product_msrp_max'] = $price_range_arr[3];

            $this -> smt_info['product_cost_price'] = $_price_range[1] -> getAttribute('innerHTML');
            $price_range_arr = explode(' ', $this -> smt_info['product_cost_price']);
            $this -> smt_info['product_cost_min'] = str_replace('$', '', $price_range_arr[1]);
            $this -> smt_info['product_cost_max'] = $price_range_arr[3];
            unset( $_price_range );

            #获取库存
            $_product_stock = $this -> driver -> findElements(
                WebDriverBy::className('product-quantity-tip')
            );
            $_product_stock_str = $_product_stock[0] -> getAttribute('innerHTML');
            if ( strlen($_product_stock_str) > 0 )
            {
                preg_match_all('/\d+/',$_product_stock_str,$arr);
                $this -> smt_info['product_stock'] = $arr[0];
            }
            unset( $_product_stock );
            unset( $_product_stock_str );

            #到货时间
            $_product_shipping_date = $this -> driver -> findElements(
                WebDriverBy::xpath('//span[@class=\'product-shipping-date\']/span[@class="product-shipping-delivery"]/span')
            );
            $_product_shipping_date_str = $_product_shipping_date[0] -> getAttribute('innerHTML');
            $this -> smt_info['product_stock'] = strlen($_product_shipping_date_str) > 0 ? $_product_shipping_date_str : '';
            unset( $_product_shipping_date_str );
            unset( $_product_shipping_date );

            #属性获取
            $_product_attr = $this -> driver -> findElements(
                WebDriverBy::xpath('//div[@class=\'product-sku\']/div[@class="sku-wrap"]/div[@class="sku-property"]')
            );

            if ( !empty($_product_attr) )
            {
                foreach ( $_product_attr as $key =>  $attr )
                {
                    $_attr_name = $attr -> findElements(
                        WebDriverBy::xpath('./div[@class=\'sku-title\']')
                    );
                    $product_attr_1_name = str_replace(': ', '' , $_attr_name[0] -> getAttribute('innerHTML'));
                    $product_attr_1_name = $this -> remove_html_tag($product_attr_1_name);

                    $_attr_img = $attr -> findElements(
                        WebDriverBy::xpath('./ul[@class=\'sku-property-list\']//img')
                    );
                    $_attr_span = $attr -> findElements(
                        WebDriverBy::xpath('./ul[@class=\'sku-property-list\']//div[@class="sku-property-text"]//span')
                    );

                    if ( !empty($_attr_img) )
                    {
                        foreach ( $_attr_img as $_img)
                        {
                            $img_str = $_img -> getAttribute('src');
                            $img_title = $_img -> getAttribute('title');
                            $img_index = strpos($img_str,'jpg');
                            $img_str = substr($img_str, 0, $img_index +3 );

                            $this -> smt_info['product_attr'][ $product_attr_1_name ]['data'][ $img_title ] = $img_str;
                            $this -> smt_info['product_attr'][ $product_attr_1_name ]['type'] = 'img';
                            $this -> attr['has_img'] = 1;
                        }
                    }
                    if ( !empty($_attr_span) )
                    {
                        foreach ( $_attr_span as $_span)
                        {
                            $span_str = $_span -> getAttribute('innerHTML');

                            $this -> smt_info['product_attr'][ $product_attr_1_name ]['data'][] = $span_str;
                            $this -> smt_info['product_attr'][ $product_attr_1_name ]['type'] = 'span';

                            $this -> attr['has_span'] = 1;
                        }
                    }

                    unset($product_attr_1_name);
                }
            }
 
            #关闭弹窗  尝试2次
            $this -> close_pop_div(3);
            
            sleep(1);
    
            #获取属性价格
            $this -> clickElementAndScrap($this -> smt_info['product_attr'], $_product_attr, 0);


            var_dump( $this -> smt_info );
            return;
    }

    public function close_pop_div( $nc = 3 )
    {
         $fuk_div_localtion = WebDriverBy::xpath("//div[@class='next-overlay-wrapper opened']");
            
            if ( $this -> findElementExsit( $fuk_div_localtion ) ) {
            
                $this -> driver -> findElement( 
                    WebDriverBy::xpath("//div[@class='next-overlay-wrapper opened']//a[@class='next-dialog-close']")
                )-> click();

                $nc = 0;
            }

        if ( $nc > 0 ) {
          return   $this -> close_pop_div( $nc-- );
        }

        return;
    }


    public function clickElementAndScrap( $product_attr , $_product_attr_driver_obj, $cur_dept = 0 )
    {
        $dept = count($product_attr);
        $attr_arr = current(array_slice($product_attr,$cur_dept,1));
        $index = 0;
        
        foreach ( $attr_arr['data'] as $k1 => $v1 )
        {

            if ( 'img' == $attr_arr['type'] )
            {

                $img_ele = $_product_attr_driver_obj[$cur_dept] -> findElement(
                    WebDriverBy::xpath("./ul[@class='sku-property-list']//img[@title='". $k1 ."']")
                );

                $this -> driver->executeScript("arguments[0].scrollIntoView({block: \"center\"});",[$img_ele]);
                $img_ele -> click();

            }
            if ( 'span' == $attr_arr['type'] )
            {
                $span_ele = $_product_attr_driver_obj[$cur_dept] -> findElement(
                    WebDriverBy::xpath("./ul[@class='sku-property-list']//span[text()='". $v1 ."']")
                ) -> click();

                $this -> driver->executeScript("arguments[0].scrollIntoView({block: \"center\"});",[$span_ele]);
                
                $span_ele -> click();
            }

            $cur_dept_tmp = $cur_dept + 1 ;
            
            if ( $dept > $cur_dept_tmp )
            {
                $this -> clickElementAndScrap( $product_attr , $_product_attr_driver_obj , $cur_dept_tmp, $index );
            }

            if ( $dept == $cur_dept_tmp ) {

                #获取名字
                unset($_product_attr);
                $_product_attr = $this -> driver -> findElements(
                    WebDriverBy::xpath('//div[@class=\'product-sku\']/div[@class="sku-wrap"]//div[@class="sku-property"]//li[@class="sku-property-item selected"]')
                );

                if ( !empty($_product_attr) ) {
                    foreach ($_product_attr as $key => $value) {
                        $value -> findElement(
                            WebDriverBy::xpath("./ul[@class='sku-property-list']//img")
                    );
                    }
                }

                 #获取库存
                $_product_stock = $this -> driver -> findElements(
                    WebDriverBy::className('product-quantity-tip')
                );
                $_product_stock_str = $_product_stock[0] -> getAttribute('innerHTML');
                $this -> smt_info['sku'][ $index ]['stock'] = $_product_stock_str;

                #获取价格区间
                $product_price = $this -> driver -> findElements(
                    WebDriverBy::className('product-price-value')
                );
                $product_price_str = $product_price[0] -> getAttribute('innerHTML');
                $this -> smt_info['sku'][ $index ]['price'] = $product_price_str;

                unset( $product_price );
                unset( $product_price_str );
                unset( $_product_stock );
                unset( $_product_stock_str );

                $index ++ ;
            }

            

        }
    }

    public function findElementExsit( $obj )
    {
        if( $this -> isElementExsit($obj) ){
            return true;
        } else {

            $js="var q=document.documentElement.scrollTop=".(1000);
            $sScriptResult = $this -> driver -> executeScript( $js, array() );

            return $this -> findElementExsit( $obj );
        }
    }

    /**
     * 判断元素是否存在
     * @param WebDriver $this -> driver
     * @param WebDriverBy $locator
     */
    function isElementExsit($locator){
        try {
            $nextbtn = $this -> driver->findElement($locator);
            return true;
        } catch (\Exception $e) {
            //echo 'element is not found!';
            return false;
        }
    }


    /**
     * #去掉字符串html代码
     * @param $html_string
     * @param bool $is_save_br
     * @return string
     */
    function remove_html_tag( $html_string, $is_save_br = false )
    {
        $description_content = strip_tags( $html_string );
        if( '>' == substr( $description_content, 0, 1 ) )
        {
            $description_content = substr( $description_content, 1 );
        }
        $description_content = preg_replace('|style=\"[\w\W]+\">|', '', $description_content);
        $description_content = str_replace('&nbsp;', '', $description_content);
        $description_content = str_replace('&quot;', '"', $description_content);
        $description_content = str_replace("&apos;","'",$description_content);
        $description_content = str_replace("&amp;","&",$description_content);
        $description_content = str_replace("&#39;","'",$description_content);
        $description_content = preg_replace('/<table[^>]*?>.*?<\/table>/is'," ",$description_content);
        //$description_content = str_replace(PHP_EOL, "\r\n", $description_content);
        $description_content = nl2br( $description_content );

        if ( !$is_save_br )
        {
            $description_content = preg_replace('/\s*(<br\s*\/?\s*>\s*){2,}/im',PHP_EOL,$description_content);
            /*if( '<br />' == substr( $description_content,0, 6 ) )
            {
                $description_content = substr( $description_content, 6 );
            }*/
            $description_content = str_replace("<br />",PHP_EOL,$description_content);
        }


        return trim( $description_content );
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

        

        $this -> driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome(), 50000);

        $this -> smt_info = [];
        $this -> Crawler();

      


        // echo "The title is '" . $this -> driver->getTitle() . "'\n";
        // echo "The current URI is '" . $this -> driver->getCurrentURL() . "'\n";



        //关闭浏览器
        $this -> driver->quit();

    }



}



$App = new CrawlerApp;
$App -> RunApp();
return;

