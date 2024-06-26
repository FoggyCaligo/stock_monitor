<!DOCTYPE html>
<html>
<head>
<meta charset="EUC-KR">
<title>Insert title here</title>
<style type="text/css">
#t1, div {
	border: 1px solid black;
}
</style>
 

</head>
<body>
	<form name="f" method="get">
		<h3>추가</h3>
		<table id="t1">
			<tr>
				<th>종목명</th>
				<td><input type="text" name="writer" id="writer"></td>
			</tr>
			<tr>
				<th>종목코드</th>
				<td><input type="text" name="content" id="content"></td>
			</tr>
			<tr>
				<th>작성</th>
				<td><input type="submit" name="submit" id="submit" onclick="insert();"></td>
			</tr>
		</table>


		<h3>제거</h3>
		<table id="t1">
			<tr>
				<th>종목명</th>
				<td><input type="text" name="delname" ></td>
			</tr>
			<tr>
				<th>종목코드</th>
				<td><input type="text" name="delcode" ></td>
			</tr>
			<tr>
				<th>작성</th>
				<td><input type="submit" name="submit" ></td>
			</tr>
		</table>
	</form>

</body>
</html>


<script>
function drawlines() {
    ctx.fillStyle = "black";
    ctx.strokeStyle = "black";
    x = xOnCvs[0];
    y = yOnCvs[0];

    ctx.beginPath();
    ctx.arc(x, y, 5, 0, 2 * Math.PI);
    ctx.fill();

    for (i = 1; i < nX; ++i) {
      nextx = xOnCvs[i];
      nexty = yOnCvs[i];

      ctx.moveTo(x, y);
      ctx.lineTo(nextx, nexty);
      ctx.stroke();

      ctx.beginPath();
      ctx.arc(nextx, nexty, 5, 0, 2 * Math.PI);
      ctx.fill();

      x = nextx;
      y = nexty;
    }
  }
</script>





<?php

require_once './simplehtmldom_1_9_1/simple_html_dom.php';
ob_start();
function get_price($code){
	//$answer = 0;
	$html = file_get_html ( 'https://finance.naver.com/item/main.naver?code='.$code);
	$p = $html->find('p[class=no_today]',0);
	
	$p = $p->find('span',0)->plaintext;
	$p = (string)$p;
	print($p);
	$p = str_replace(",","",$p);
	$html->clear();
	

	return (int)($p);
}







function update_price($code,$price){
	$data = "";
	print($price);
	if(isset($_COOKIE[$code])){
		$data = $_COOKIE[$code].",".(string)$price;
		setcookie($code, $data,time()+120000);
	}	
	else{
		setcookie($code, (string)$price,time()+120000);
	}
	return $data;
}




	//connect DB
	$con = mysqli_connect("localhost","root","test","data2");
	//제거 입력 관리
	if(isset($_GET['delname']) && isset($_GET['delcode'])){	
		$name = $_GET['delname'];
		$delcode = $_GET['delcode'];
		$default_price = "0";
		$sql = "delete from data2 where code='".$delcode."';";
		$ret = mysqli_query($con,$sql);
	}
	//추가 입력 관리
	if(isset($_GET['writer']) && isset($_GET['content'])){	
		$name = $_GET['writer'];
		$code = $_GET['content'];
		$default_price = "0";
		$sql = "Insert ignore Into data2 VALUES ('"   .$code.  "','"  .$name."')";
		$ret = mysqli_query($con,$sql);
	}
	

	//$con = mysqli_connect("localhost","root","test","data2");
	//데이터 가져오기
	$sql = "Select * From data2";
	$ret = mysqli_query($con,$sql);
	echo("<table>");
	
	while($row = mysqli_fetch_array($ret)){
		if($row['code']==''){
			continue;
		}
		echo("<tr>");
		// echo(echo($data));;
		// echo(");>");
			echo("<td>");
			echo $row['name'];
			echo("</td>");

			echo("<td style='color:lightgray;'>");
			echo (string)$row['code'];
			echo("</td>");
			
			echo("<td>");
			//echo "	".$row['value']."<br>";
			$price = get_price($row['code']);
			echo("  ");
			echo($price);
			echo("</td>");
			//graph	
			echo("<td>");
				//$data = [];
				echo("<canvas id='canv_".$row['code']."' width: 50vw; height: 10px;></canvas>");
				echo("<script>");
					echo('cvs = document.getElementById("canv_'.$row['code'].'");');
					echo('ctx = cvs.getContext("2d");');
					echo('data = [');
					//echo('2, 7, 3, 2, 6, -3,7,99,3,1');
					echo((string)update_price($row['code'],$price));
					echo('];');
					
					echo('pad = 50;
chartInnerWidth = cvs.width - 2 * pad;
chartInnerHeight = cvs.height - 2 * pad;

ctx.moveTo(pad, pad);
ctx.lineTo(pad, pad + chartInnerHeight);
ctx.stroke();

ctx.moveTo(pad, pad + chartInnerHeight);
ctx.lineTo(pad + chartInnerWidth, pad + chartInnerHeight);
ctx.stroke();

max = Math.max(...data);
min = Math.min(...data);
nX = data.length;
nY = max - min + 1;

// mouse position
mx = 0;
my = 0;

blockWidth = chartInnerWidth / (nX + 1);
blockHeight = chartInnerHeight / (nY + 1);

// drawing ticks
ticklenhalf = 5;
  

xOnCvs = [];
yOnCvs = [];

// where to draw
x = pad + blockWidth;
y = pad + chartInnerHeight - blockHeight * (data[0] - min + 1);

xOnCvs.push(x);
yOnCvs.push(y);

for (i = 1; i < nX; ++i) {
	xOnCvs.push(pad + (i + 1) * blockWidth);
	yOnCvs.push(pad + chartInnerHeight - blockHeight * (data[i] - min + 1));
}

function drawlines() {
	ctx.fillStyle = "black";
	ctx.strokeStyle = "red";
	x = xOnCvs[0];
	y = yOnCvs[0];

	ctx.beginPath();
	ctx.fill();

	for (i = 1; i < nX; ++i) {
		nextx = xOnCvs[i];
		nexty = yOnCvs[i];

		ctx.moveTo(x, y);
		ctx.lineTo(nextx, nexty);
		ctx.stroke();

		ctx.beginPath();
		ctx.fill();

		x = nextx;
		y = nexty;
	}
}

for (i = 0; i < nX; ++i) {
	dx = xOnCvs[i] - mx;
	dy = yOnCvs[i] - my;
	ctx.font = "30px Arial";
	if (dx * dx + dy * dy < 100) {
		ctx.fillStyle = "rgba(77, 82, 82,100)";
		ctx.fillRect(xOnCvs[i], yOnCvs[i] - 40, 40, 40);
		ctx.textAlign = "center";
		ctx.textBaseline = "middle";
		ctx.fillStyle = "rgb(213, 219, 219)";
		ctx.fillText(data[i].toString(), xOnCvs[i] + 20, yOnCvs[i] + 20 - 40);
    }
}
drawlines();
					');
				echo("</script>");
				
			echo("</td>");
			
		echo("</tr>");



		
		
	}
	echo("</table><br>");
	

	//$t = (int)date("i");
	
?>





<script>
	//60초(1분)마다 페이지 새로고침
	setTimeout(() => {
		location.reload(true);
	}, 6000);
</script>


