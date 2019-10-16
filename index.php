<?php
$ver = '1.0'
?>


<!DOCTYPE html>
<html>
<head>
	<title>爽儿牛逼！！！！</title>
	<link rel="shortcut icon" href="favicon.ico" >
	<script type="text/javascript" src="js/jquery.js"></script>
	<style>
        .btn{
        display: inline-block;
        padding: 6px 12px;
        margin-bottom: 0;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.42857143;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        -ms-touch-action: manipulation;
        touch-action: manipulation;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        color: white;
        border: 1px solid #53baff;
        background-color: #53baff;
        border-radius: 2px;
        }
        .btn-sm {
        font-size: 12px;
        line-height: 0.8;
        border-radius: 2px;
        }

        .btn-sm:hover {
        background-color: #4ba5e6;
        }

        .btn-green:hover {
        background-color: #07d266;
        }
        .btn-green {
        background-color: #00c14e;
        border: 1px solid #00c14e;
        }
        .btn-warning {
        background-color: #f25800;
        border: 1px solid #f25800;
        }
        .btn-warning:hover {
        background-color: #db5500;
        }
    </style>
</head>
<body>
	 <div style="margin-left: 50px" >
        <div>
            <textarea name="" placeholder="请输入要爬取的网址，分号 ; 或者换行隔开" id="search_sql" style="margin-top: 10px;width: 900px;height: 300px" cols="30" rows="10"></textarea>
        </div>
        <div>
            <input  type="text" id="yz" placeholder="请输入密钥">
            <input class="btn btn-sm" style="margin-left: 20px" type="button" id="do_search" value="执行">

            <span style="margin-left: 2%">
                 <?php  echo 'Ver ' .$ver ?>
            </span>

        </div>
        <p style="color: red">* 谨慎操作 更改删除 务必加 where 和 limit </p>
    </div>

    <div style="margin-left: 50px;"  >
        <p id="input_p"></p>
        <span>结果：</span>
        <div id="search_res" style="height: 1200px;overflow-y: auto;">

        </div>
    </div>
</body>
<script>
    $('#do_search').click(function () {
        var sql = $('#search_sql').val();
        var yz = $('#yz').val();
        var slave_search = $('input[name="slave_search"]:checked').val();
        var param = {};
        param['search_sql'] = encodeURIComponent(sql);
        param['search_yz']  = yz;
        param['slave_search']  = slave_search;
        console.log( param )
        $.post( 'smt.php', param, function ( data ) {
            console.log( sql );
            $('#input_p').html( sql );
            $('#search_res').html( data )
        });

    })

</script>
</html>