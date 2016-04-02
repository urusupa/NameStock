<?php

//-----------------------------------------------------------------------------
// include
//-----------------------------------------------------------------------------

require_once "common.php";

//-----------------------------------------------------------------------------
// 変数
//-----------------------------------------------------------------------------

$pdo = null;

//-----------------------------------------------------------------------------
// 初期処理
//-----------------------------------------------------------------------------
if ( !isset($_POST['genrelist']) ){
	$cPostG = "none";
}else{
	$cPostG = $_POST['genrelist'];
}

if ( !isset($_POST['showNumlist']) ){
	$cPostN = 5;
}else{
	$cPostN = $_POST['showNumlist'];
}

//-----------------------------------------------------------------------------
// 関数群
//-----------------------------------------------------------------------------



function getRandRcd(){
	global $pdo;
	ConnectMySQL();
	if ( $GLOBALS["cPostG"] == "none" ){
		$sql = "SELECT NAMEVALUE,GENRE,BIKO FROM M_NAMESTOCK WHERE USE_KBN = 0 ORDER BY Rand() LIMIT 0," . $GLOBALS["cPostN"] . "";
		$result = $pdo->query($sql);
	}else{
		$sql = "SELECT NAMEVALUE,GENRE,BIKO FROM M_NAMESTOCK WHERE GENRE = '" . $GLOBALS["cPostG"] . "' AND USE_KBN = 0 ORDER BY Rand() LIMIT 0," . $GLOBALS["cPostN"] . "";
		$result = $pdo->query($sql);
	}
	CloseMySQL();

	return $result;
}

function showRcds($Rs){
	echo "<table class='namelists'>\n";
	echo "<th>名前</th><th>ジャンル</th><th>備考</th>\n";

	while($row = $Rs -> fetch(PDO::FETCH_ASSOC)) {
		$names = $row["NAMEVALUE"];
		$genre = $row["GENRE"];
		$biko = $row["BIKO"];
		
		if($genre == "和色名") $biko = convertColorCode($biko);
		
		echo "<tr>";
		echo "<td>" . $names . "</td><td><a href='" . getSrcURL($genre) . "' target='_blank'>". $genre . "</td><td>" . $biko . "</td>";
		echo "</tr>\n";
	}
	echo "</table>\n";
}

function getSrcURL($gnr){
	global $pdo;
	ConnectMySQL();
	$result = $pdo -> prepare("SELECT BIKO FROM M_NAMESTOCK WHERE GENRE = :genre AND SIYOZUMI_FLG = 1 AND USE_KBN = 1");
	$result -> bindValue(':genre', $gnr, PDO::PARAM_STR);
	$result -> execute();
	$result = $result->fetchAll();
	$url = $result[0]["BIKO"];
	CloseMySQL();

	return $url;
}

function convertColorCode($biko){
	$pattern = '/#([\da-fA-F]{6}|[\da-fA-F]{3})/i';
	preg_match($pattern,$biko,$ccode);
	$replacement = "<b><font color='" . $ccode[0] .  "'>" . $ccode[0] . "</font></b>";
	$biko = preg_replace($pattern,$replacement,$biko);

	return $biko;
}

function getGenreList(){

	ConnectMySQL();
	$result = $GLOBALS["pdo"] -> prepare("SELECT GENRE FROM M_NAMESTOCK GROUP BY GENRE ORDER BY 1");
	$result -> execute();
	CloseMySQL();
	
	echo "<form name ='genrelistform' class ='genrelistform' method = 'POST' action = ''>\n";
	echo "<input type='hidden' name='showNumlist' value='" . $GLOBALS["cPostN"] . "'>";
	echo "<select class='genrelist' name='genrelist' onChange=\"document.forms['genrelistform'].submit()\">\n";
	echo "<option value='none'>選択なし</option>\n";
	while($row = $result -> fetch(PDO::FETCH_ASSOC)) {
		$genre = $row["GENRE"];
		$selected = "";
		
		if( $GLOBALS["cPostG"] == $genre ) $selected = "selected" ;
		echo "<option value='" . $genre . "' " . $selected . ">" . $genre . "</option>\n";
	}
	echo "</select>\n";
	echo "</form>\n";
	
}

function moreName($flg){
	if ( $flg == 1){
		echo "<form class='' method = 'POST' action = ''>";
		echo "<input type='hidden' name='showNumlist' value='" . $GLOBALS["cPostN"] . "'>";
		echo "<input type='submit' value='ランダム！'></form><br>";
	}elseif( $flg == 2 and $GLOBALS["cPostG"] != "none" ){
		echo "<form class='' method = 'POST' action = ''>";
		echo "<input type='hidden' name='genrelist' value='" . $GLOBALS["cPostG"] . "'>";
		echo "<input type='hidden' name='showNumlist' value='" . $GLOBALS["cPostN"] . "'>";
		echo "<input type='submit' value='もっと！'></form><br>";
	}else{
	}
}

function showNum(){
	echo "表示件数 ";
	echo "<form name ='showNumform' class ='showNumform' method = 'POST' action = ''>\n";
	echo "<input type='hidden' name='genrelist' value='" . $GLOBALS["cPostG"] . "'>";
	echo "<select class='showNumlist' name='showNumlist' onChange=\"document.forms['showNumform'].submit()\">\n";

	$numlist = array(5,10,50,100);
	foreach( $numlist as $num ) {
		$selected = "";
		if( $GLOBALS["cPostN"] == $num ) $selected = "selected" ;
		echo "<option value='" . $num . "' " . $selected . ">" . $num . "件</option>\n";
	}

	echo "</select>\n";
	echo "</form>\n";
}

//-----------------------------------------------------------------------------
// メイン
//-----------------------------------------------------------------------------





//HTML出力
//-----------------------------------------------------------------------------
echo "<!DOCTYPE html>\n\n";
echo "<html>\n\n";
echo "<head>\n";
echo "<title>NameStock</title>\n";
echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>\n";
echo "<link href='namestock.css' rel='stylesheet'>\n";
echo "<script type='text/javascript' src='/misc/jquery-2.2.2.min.js'></script>\n";
echo "<script type='text/javascript' src='namestock.js'></script>\n";
echo "</head>\n";
echo "<body>\n";
echo "\n";
echo "<h1>\n";
echo "<a class='titlelink' href=''>NameStock</a>\n";
echo "</h1>\n";
echo "\n";
echo "\n";
echo "<hr>\n";
echo "ランダムに名前を表示します\n"; moreName(1);
echo "ジャンルで絞るならこちら → "; getGenreList(); moreName(2);
echo "<hr>\n";
echo "<br>\n";
showNum();
showRcds(getRandRcd());
echo "\n";
echo "\n";
echo "<br>\n";
echo "<br>\n";
echo "<a class='' href='http://nyctea.me/'>@nyctea.me</a><br><br>\n";
echo "\n";
echo "</body>\n";
echo "</html>\n";


?>