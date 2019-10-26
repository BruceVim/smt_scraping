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

    public $targetUrl;

    public function Crawler() {

            $targetUrl = $this -> targetUrl;
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
            $this -> smt_info['product_title'] = addslashes($this -> smt_info['product_title']);
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

                    $this -> smt_info['product_description'] = $this -> remove_html_tag($_description[0] -> getAttribute('innerHTML'),false);
                    $this -> smt_info['product_description'] = addslashes($this -> smt_info['product_description']);
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
            $this -> smt_info['product_msrp_max'] = (isset($price_range_arr[3]) && (float)$price_range_arr[3] > 0) ? $price_range_arr[3] : $this -> smt_info['product_msrp_min'];

            $this -> smt_info['product_cost_price'] = $_price_range[1] -> getAttribute('innerHTML');
            $price_range_arr = explode(' ', $this -> smt_info['product_cost_price']);
            $this -> smt_info['product_cost_min'] = str_replace('$', '', $price_range_arr[1]);
            $this -> smt_info['product_cost_max'] = (isset($price_range_arr[3]) && (float)$price_range_arr[3] > 0) ? $price_range_arr[3] : $this -> smt_info['product_cost_min'];

            unset( $_price_range );

            #获取库存
            $_product_stock = $this -> driver -> findElements(
                WebDriverBy::className('product-quantity-tip')
            );
            $_product_stock_str = $_product_stock[0] -> getAttribute('innerHTML');
            if ( strlen($_product_stock_str) > 0 )
            {
                preg_match_all('/\d+/',$_product_stock_str,$arr);
                $this -> smt_info['product_stock'] = current($arr[0]);
            }
            unset( $_product_stock );
            unset( $_product_stock_str );

            #到货时间
            $_product_shipping_date = $this -> driver -> findElements(
                WebDriverBy::xpath('//span[@class=\'product-shipping-date\']/span[@class="product-shipping-delivery"]/span')
            );
            $_product_shipping_date_str = $_product_shipping_date[0] -> getAttribute('innerHTML');
            $this -> smt_info['product_shipping_date'] = strlen($_product_shipping_date_str) > 0 ? $_product_shipping_date_str : '';
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
 
            #关闭弹窗  尝试3次
            sleep(1);
            $this -> close_pop_div(3);
            sleep(1);

            #获取属性价格
            $this -> clickElementAndScrap($this -> smt_info['product_attr'], $_product_attr, 0);

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


    public function clickElementAndScrap( $product_attr , $_product_attr_driver_obj, $cur_dept = 0, $sku_name=[] , $img_url='')
    {
        $dept = count($product_attr);
        $attr_arr = current(array_slice($product_attr,$cur_dept,1));
        foreach ( $attr_arr['data'] as $k1 => $v1 )
        {

            if ( 'img' == $attr_arr['type'] )
            {

                $img_ele = $_product_attr_driver_obj[$cur_dept] -> findElement(
                    WebDriverBy::xpath("./ul[@class='sku-property-list']//img[@title='". $k1 ."']")
                );

                $this -> driver->executeScript("arguments[0].scrollIntoView({block: \"center\"});",[$img_ele]);
                $img_ele -> click();

                $_sku_name = $k1;

                $img_str = $img_ele -> getAttribute('src');
                $img_index = strpos($img_str,'jpg');
                $img_str = substr($img_str, 0, $img_index +3 );
                 $img_url = '';
                $img_url = $img_str;
        
            }
            if ( 'span' == $attr_arr['type'] )
            {
                $span_ele = $_product_attr_driver_obj[$cur_dept] -> findElement(
                    WebDriverBy::xpath("./ul[@class='sku-property-list']//span[text()='". $v1 ."']")
                );

                $this -> driver->executeScript("arguments[0].scrollIntoView({block: \"center\"});",[$span_ele]);
                
                $span_ele -> click();

                $_sku_name = $v1;
            }


            $sku_name[ $cur_dept ] = $_sku_name;

           //数组深度
            if ( $dept > ($cur_dept + 1) ) 
            {
                $this -> clickElementAndScrap( $product_attr , $_product_attr_driver_obj , ($cur_dept + 1), $sku_name, $img_url);
            } 
            elseif( $dept == ($cur_dept + 1) ) 
            {
                $sku_attr_tmp = [];
                #获取名字
                $sku_attr_tmp['sku_name'] = implode(',', $sku_name);
                 #获取库存
                $_product_stock = $this -> driver -> findElements(
                    WebDriverBy::className('product-quantity-tip')
                );
                $_product_stock_str = $_product_stock[0] -> getAttribute('innerHTML');

                if ( strlen($_product_stock_str) > 0 )
                {
                    preg_match_all('/\d+/',$_product_stock_str,$arr);
                    $sku_attr_tmp['stock'] = current($arr[0]);
                }
                
                #获取价格区间
                $product_price = $this -> driver -> findElements(
                    WebDriverBy::className('product-price-value')
                );
                $product_price_str = $product_price[0] -> getAttribute('innerHTML');
                if ( strlen($product_price_str) > 0 )
                {
                    $price_range_arr = explode(' ', $product_price_str);
                    $sku_attr_tmp['price'] = str_replace('$', '', $price_range_arr[1]);
                }
                #添加图片
                $sku_attr_tmp['img_url'] =  $img_url;

                $this -> smt_info['sku'][] = $sku_attr_tmp;
                unset( $sku_attr_tmp );
                unset( $product_price );
                unset( $product_price_str );
                unset( $_product_stock );
                unset( $_product_stock_str );
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

    /*
        * 加密，可逆
        * 可接受任何字符
        * 安全度非常高
        */
    function reversible_encrypt($txt, $key = 'BruceVim')
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
        $ikey ="-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
        $nh1 = rand(0,64);
        $nh2 = rand(0,64);
        $nh3 = rand(0,64);
        $ch1 = $chars{$nh1};
        $ch2 = $chars{$nh2};
        $ch3 = $chars{$nh3};
        $nhnum = $nh1 + $nh2 + $nh3;
        $knum = 0;$i = 0;
        while(isset($key{$i})) $knum +=ord($key{$i++});
        $mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum%8,$knum%8 + 16);
        $txt = base64_encode($txt);
        $txt = str_replace(array('+','/','='),array('-','_','.'),$txt);
        $tmp = '';
        $j=0;$k = 0;
        $tlen = strlen($txt);
        $klen = strlen($mdKey);
        for ($i=0; $i<$tlen; $i++) {
            $k = $k == $klen ? 0 : $k;
            $j = ($nhnum+strpos($chars,$txt{$i})+ord($mdKey{$k++}))%64;
            $tmp .= $chars{$j};
        }
        $tmplen = strlen($tmp);
        $tmp = substr_replace($tmp,$ch3,$nh2 % ++$tmplen,0);
        $tmp = substr_replace($tmp,$ch2,$nh1 % ++$tmplen,0);
        $tmp = substr_replace($tmp,$ch1,$knum % ++$tmplen,0);
        return $tmp;
    }

    /*
     * 解密
     *
     */
    function reversible_decrypt($txt, $key = 'BruceVim')
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
        $ikey ="-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
        $knum = 0;$i = 0;
        $tlen = strlen($txt);
        while(isset($key{$i})) $knum +=ord($key{$i++});
        $ch1 = $txt{$knum % $tlen};
        $nh1 = strpos($chars,$ch1);
        $txt = substr_replace($txt,'',$knum % $tlen--,1);
        $ch2 = $txt{$nh1 % $tlen};
        $nh2 = strpos($chars,$ch2);
        $txt = substr_replace($txt,'',$nh1 % $tlen--,1);
        $ch3 = $txt{$nh2 % $tlen};
        $nh3 = strpos($chars,$ch3);
        $txt = substr_replace($txt,'',$nh2 % $tlen--,1);
        $nhnum = $nh1 + $nh2 + $nh3;
        $mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum % 8,$knum % 8 + 16);
        $tmp = '';
        $j=0; $k = 0;
        $tlen = strlen($txt);
        $klen = strlen($mdKey);
        for ($i=0; $i<$tlen; $i++) {
            $k = $k == $klen ? 0 : $k;
            $j = strpos($chars,$txt{$i})-$nhnum - ord($mdKey{$k++});
            while ($j<0) $j+=64;
            $tmp .= $chars{$j};
        }
        $tmp = str_replace(array('-','_','.'),array('+','/','='),$tmp);
        return trim(base64_decode($tmp));
    }

    function save_item_info( $smt_info )
    {
        $time = time();
        $inner_sql = "INSERT INTO `test`.`v1_smt_item` (
                `si_product_title`,
                `si_product_desc`,
                `si_product_msrp`,
                `si_product_msrp_min`,
                `si_product_msrp_max`,
                `si_product_cost_price`,
                `si_product_cost_price_min`,
                `si_product_cost_price_max`,
                `si_product_stock`,
                `si_product_shipping_date`,
                `si_product_attr_json`,
                `si_create_time`,
                `si_update_time` 
                )
                VALUES
                    (
                    '{$smt_info['product_title']}',
                    '{$smt_info['product_description']}',
                    '{$smt_info['product_msrp']}',
                    '{$smt_info['product_msrp_min']}',
                    '{$smt_info['product_msrp_max']}',
                    '{$smt_info['product_cost_price']}',
                    '{$smt_info['product_cost_min']}',
                    '{$smt_info['product_cost_max']}',
                    '{$smt_info['product_stock']}',
                    '{$smt_info['product_shipping_date']}',
                    '{$smt_info['product_attr_json']}}',
                    {$time},
                    {$time} 
                    );";
        $act_res = mysqli_query($this -> MysqlConn,$inner_sql);

        $si_id =  mysqli_insert_id($this -> MysqlConn);

        if ( !empty($smt_info['sku']) ) {
        foreach ($smt_info['sku']  as $key => $value) {
        
            $siv_inner_sql = "INSERT INTO `test`.`v1_smt_item_variations` (  `siv_si_id`, `siv_sku_name`, `siv_sku_stock`, `siv_sku_price`, `siv_sku_img` , `siv_create_at` )
                VALUES
                    (  {$si_id}, '{$value['sku_name']}', '{$value['stock']}', '{$value['price']}','{$value['img_url']}', '{$time}' );";

            $res = mysqli_query($this -> MysqlConn,$siv_inner_sql);
        }
        }
    }

    public function RunApp() {

        #验证
        $search_yz = $_POST['search_yz'];
        $search_sql = $_POST['search_sql'];

        $search_sql = stripslashes(urldecode($search_sql));

        if ( empty($search_yz)) {
            echo '请输入密钥';
            return ;
        }
        if ( empty($search_sql)) {
            echo '请输入要爬取的网址，分号 ; 或者换行隔开';
            return ;
        }

        if ( strlen($_POST['search_yz']) > 0 )
        {
            $search_key = $this -> reversible_decrypt( $search_yz, 'love my');
        }

        $search_sql = str_replace([';','；',"\n","\r\n"], ';', $search_sql);
        $link_arr = explode(';', $search_sql);

        #连接mysql
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
      

        
        if ( !empty($link_arr) ) {

            echo "抓取中...<br />";
            ob_flush();
            flush();

            foreach ($link_arr  as $key => $value) {
                
                $this -> targetUrl = trim($value);
                if ( !empty($this -> targetUrl) ) {

                     $this -> driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome(), 50000);
                     $this -> smt_info = [];
                     $this -> Crawler();

                     $this -> smt_info['product_attr_json'] = json_encode($this -> smt_info['product_attr']);

                     $this -> save_item_info( $this -> smt_info );

                     echo  $this -> targetUrl . '抓取完成 <br />';
                     ob_flush();
                     flush();
                       //关闭浏览器
                     $this -> driver->quit();
                }
            }
        }

       

    }



}



$App = new CrawlerApp;
$App -> RunApp();
return;

