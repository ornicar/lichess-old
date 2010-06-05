<?php
/************************************************************************************************************
Chess Widget
Copyright (C) 2007  DTHMLGoodies.com, Alf Magne Kalleland

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA

Dhtmlgoodies.com., hereby disclaims all copyright interest in this script
written by Alf Magne Kalleland.

Alf Magne Kalleland, 2007
Owner of DHTMLgoodies.com


************************************************************************************************************/
define('FILE_CACHE_FOLDER', false);

class PgnParser
{
	var $pgnString;
	var $pgnFile;
	var $pgnParsed;
	var $games = array();
	var $pgnTimestamp;

	/* Constructor */
	function PgnParser($pgnFile="")
	{
		$this->pgnParsed = false;
		if($pgnFile){
			$this->readPgnData($pgnFile);
		}
	}

	/* Return pgnName without extension and slashes - used when caching files */
	function getPgnNameWithoutExtension()
	{
		$ret = "";
		return preg_replace("/[\/\.]/s","",$this->pgnFile);
	}

	/* Specify pgn file */
	function setPgnFile($pgnFile)
	{
		if($pgnFile==$this->pgnFile)return;
		$this->readPgnData($pgnFile);
	}
	/* Return safe js string, i.e. backslash in front of apostrophes */
	function getSafeJsString($inputString)
	{
		$inputString = str_replace("'","\\'",$inputString);
		return $inputString;
	}
	/*  Return attribute as key,value from a line in the pgn file */
	function getAttribute($line)
	{
		$line = preg_replace("/^(.*?);.*$/","\\1",$line);	// Removing comments
		$line = trim($line);
		$tokens = explode(" ",$line);
		$key = $tokens[0];
		$value = str_replace($key,"",$line);

		$key = str_replace('"','',$key);
		$key = str_replace("[","",$key);
		$value = str_replace('"','',$value);
		$value = str_replace(']','',$value);

		$value = str_replace('"','',$value);
		$key = str_replace('"','',$key);



		$retArray = array();

		$retArray['key'] = trim(strtolower($key));
		$retArray['value'] = trim($value);
		return $retArray;


	}

	function getStartingMoveNumber($string)
	{
		$no = preg_replace("/^(.*?)\..*$/si","\\1",$string);
		return intval($no);;
	}

	/* This method returns a string like 4. e4 e5 { comment } and returns an associative array with the keys <move order> and <color>, here 4 and "black" */
	function getLastMoveBeforeVariation($commentString)
	{
		$retArray = array();
		$commentString = str_replace("  "," ",$commentString);
		$commentString = preg_replace('/\$[0-9]{1,3}/s','',$commentString);
		$moveOrder = preg_replace("/^([\d]+)\.[^\.]*?\(.*$/s","\\1",$commentString);
		$commentString = preg_replace('/^.*?(\d\..*?)\(.*$/s',"\\1",$commentString);
		$commentString = trim($commentString);

		$items = explode(" ",$commentString);
		if(count($items)>=3)$color='black'; else $color='white';

		$retArray['color'] = $color;
		$retArray['move'] = $moveOrder;
		return $retArray;
	}

	function getVariationsFromMoveString($moveString)
	{
		$matches = array();
		$matchFound = true;
		$variationCounter = array();

		while($matchFound){
			$match = preg_replace("/.*?([\d][\d]*?\.[^\.]*?\([^\)]*?\)).*/s","\\1",$moveString);
			$equalToMoveString = false;
			if($match==substr($moveString,0,strlen($match)))$equalToMoveString=true;
			$tokens = explode("(",$match);
			if(count($tokens)>2 && !$equalToMoveString){	/* The match contains sub variations --> We need to remove it since we don't support it yet */
				$string = substr($match,2);
				$innerMatch = preg_replace("/.*?\(.*?(\([^\)]*?\)).*/s","\\1",$string);
				$moveString = str_replace($innerMatch,"",$moveString);
			}else{
				$theVariation = preg_replace("/^.*?(\([^\)]*\)).*$/s","\\1",$match);
				if(strstr($match,"("))$matchFound=true; else $matchFound=false;
				if($matchFound){
					$move = $this->getLastMoveBeforeVariation($match);
					if(!isset($variationCounter[$move['move']]))$variationCounter[$move['move']] = array();
					if(!isset($variationCounter[$move['move']][$move['color']])){
						$variationCounter[$move['move']][$move['color']] = array();
						$variationCounter[$move['move']][$move['color']] = 0;
					}else{
						$variationCounter[$move['move']][$move['color']]++;
					}
					if(!isset($matches[$move['move']][$move['color']])){
						$matches[$move['move']][$move['color']] = array();
					}
					$matches[$move['move']][$move['color']][$variationCounter[$move['move']][$move['color']]] = $theVariation;
					$tmpVar = &$matches[$move['move']][$move['color']][$variationCounter[$move['move']][$move['color']]];


					$tmpVar = preg_replace('/\$1[\d][\d]+/s',"",$tmpVar);
					$tmpVar = preg_replace('/ \$10+/s',"=",$tmpVar);
					$tmpVar = preg_replace('/ \$11+/s',"=",$tmpVar);
					$tmpVar = preg_replace('/ \$12+/s',"=",$tmpVar);
					$tmpVar = preg_replace('/ \$13+/s',"~",$tmpVar);
					$tmpVar = preg_replace('/ \$14+/s',"+=",$tmpVar);
					$tmpVar = preg_replace('/ \$15+/s',"=+",$tmpVar);
					$tmpVar = preg_replace('/ \$16+/s',"+/-",$tmpVar);
					$tmpVar = preg_replace('/ \$17+/s',"-/+",$tmpVar);
					$tmpVar = preg_replace('/ \$18+/s',"+-",$tmpVar);
					$tmpVar = preg_replace('/ \$19+/s',"-+",$tmpVar);
					$tmpVar = preg_replace('/\$2[\d][\d]+/s',"",$tmpVar);
					$tmpVar = preg_replace('/ \$20+/s',"+-",$tmpVar);
					$tmpVar = preg_replace('/ \$21+/s',"-+",$tmpVar);
					$tmpVar = preg_replace('/\$2[\d]+/s',"",$tmpVar);
					$tmpVar = preg_replace('/\$3[\d]+/s',"",$tmpVar);
					$tmpVar = preg_replace('/\$4[\d]+/s',"",$tmpVar);
					$tmpVar = preg_replace('/\$5[\d]+/s',"",$tmpVar);
					$tmpVar = preg_replace('/\$6[\d]+/s',"",$tmpVar);
					$tmpVar = preg_replace('/\$7[\d]+/s',"",$tmpVar);
					$tmpVar = preg_replace('/\$8[\d]+/s',"",$tmpVar);
					$tmpVar = preg_replace('/\$9[\d]+/s',"",$tmpVar);
					$tmpVar = preg_replace('/ \$1+/s',"!",$tmpVar);
					$tmpVar = preg_replace('/ \$2+/s',"?",$tmpVar);
					$tmpVar = preg_replace('/ \$3+/s',"!!",$tmpVar);
					$tmpVar = preg_replace('/ \$4+/s',"??",$tmpVar);
					$tmpVar = preg_replace('/ \$5+/s',"!?",$tmpVar);
					$tmpVar = preg_replace('/ \$6+/s',"?!",$tmpVar);
					$tmpVar = preg_replace('/\$[0-9]{1,3}/s','',$tmpVar);

					$tmpVar = trim($tmpVar);

					$tmpVar = substr($tmpVar,1,strlen($tmpVar)-1);	// Removing parantheses from the string
					$tmpVar = str_replace("  "," ",$tmpVar);

					// Remove sub variations - We need to take better support of this or best of all support sub variations.
					$tmpVar = preg_replace("/\([^\)*]\)/s","",$tmpVar);
					$tmpVar = preg_replace("/\(.*/s","",$tmpVar);
					$tmpVar = preg_replace("/[\(\)]/s","",$tmpVar);

					$tmpVar = trim($tmpVar);

				}
				$moveString = str_replace($theVariation,"",$moveString);
			}
		}
		return $matches;
	}

	/* This method returns a string like 4. e4 e5 { comment } and returns an associative array with the keys <move order> and <color>, here 4 and "black" */
	function getLastMoveBeforeComment($commentString)
	{
		$retArray = array();
		$tmpColor="";
		if(strstr($commentString,"..."))$tmpColor='black';
		$commentString = preg_replace("/ \s+?/s"," ",$commentString);
		$commentString = str_replace("...",".",$commentString);
		$commentString = str_replace("  "," ",$commentString);
		$commentString = preg_replace('/\$[0-9]{1,3}/s','',$commentString);
		$moveOrder = preg_replace("/^([\d]+)\.[^\.]*?{.*$/s","\\1",$commentString);
		$commentString = preg_replace('/^.*?(\d\..*?){.*$/s',"\\1",$commentString);
		$commentString = trim($commentString);

		$items = explode(" ",$commentString);
		if(count($items)>=3)$color='black'; else $color='white';
		if($tmpColor)$color=$tmpColor;
		$retArray['color'] = $color;
		$retArray['move'] = $moveOrder;

		#echo $moveOrder."---<br>";
		return $retArray;
	}

	function getCommentsFromMoveString($moveString)
	{

		$moveString = trim($moveString);
		$moveString = preg_replace('/\([^\)]*?\)/s',"",$moveString); // Removing variations for now.
		$moveString = preg_replace("/ \s+?/s"," ",$moveString);
		$matches = array();
		if(substr($moveString,0,1)=="{"){	/* Opening comment */
			$comment = preg_replace("/{(.*?)}.*/s","\\1",$moveString);
			$matches['prefaceComment'] = $comment;
		}
		$matchFound = true;
		while($matchFound){
			$match = preg_replace("/.*?([\d][\d]*?\.{1,3}[^\.]*?{[^}]*?}).*/s","\\1",$moveString);
			$theComment = preg_replace("/^.*?({[^}]*}).*$/s","\\1",$match);
			if(strstr($match,"{"))$matchFound=true; else $matchFound=false;
			if($matchFound){
				$move = $this->getLastMoveBeforeComment($match);
				$matches[$move['move']][$move['color']] = preg_replace("/[{}]/s","",$theComment);
			}
			$moveString = str_replace($theComment,"",$moveString);
		}
		return $matches;
	}


	/* Parse move string and create an array of each move - comments and variations are not part of the "moves" array */
	function getMovesFromMoveString($moveString,$startNo=0)
	{
		$moveString = preg_replace('/{[^}]*?}/si',"",$moveString);	// Removing comments for now
		$moveString = preg_replace("/\((?:[^()]+|(?R))*\)/","",$moveString);
		$moveString = preg_replace('/\([^\)]*?\)/s',"",$moveString); // Removing variations for now.
		$moveString = str_replace(". ",".",$moveString);
		$moveString = preg_replace("/ \s+?/"," ",$moveString);
		$moveString = preg_replace('/\$1[\d][\d]+/s',"",$moveString);
		$moveString = preg_replace('/ \$10+/s',"=",$moveString);
		$moveString = preg_replace('/ \$11+/s',"=",$moveString);
		$moveString = preg_replace('/ \$12+/s',"=",$moveString);
		$moveString = preg_replace('/ \$13+/s',"~",$moveString);
		$moveString = preg_replace('/ \$14+/s',"+=",$moveString);
		$moveString = preg_replace('/ \$15+/s',"=+",$moveString);
		$moveString = preg_replace('/ \$16+/s',"+/-",$moveString);
		$moveString = preg_replace('/ \$17+/s',"-/+",$moveString);
		$moveString = preg_replace('/ \$18+/s',"+-",$moveString);
		$moveString = preg_replace('/ \$19+/s',"-+",$moveString);
		$moveString = preg_replace('/\$2[\d][\d]+/s',"",$moveString);
		$moveString = preg_replace('/ \$20+/s',"+-",$moveString);
		$moveString = preg_replace('/ \$21+/s',"-+",$moveString);
		$moveString = preg_replace('/\$2[\d]+/s',"",$moveString);
		$moveString = preg_replace('/\$3[\d]+/s',"",$moveString);
		$moveString = preg_replace('/\$4[\d]+/s',"",$moveString);
		$moveString = preg_replace('/\$5[\d]+/s',"",$moveString);
		$moveString = preg_replace('/\$6[\d]+/s',"",$moveString);
		$moveString = preg_replace('/\$7[\d]+/s',"",$moveString);
		$moveString = preg_replace('/\$8[\d]+/s',"",$moveString);
		$moveString = preg_replace('/\$9[\d]+/s',"",$moveString);

		$moveString = preg_replace('/ \$1+/s',"!",$moveString);
		$moveString = preg_replace('/ \$2+/s',"?",$moveString);
		$moveString = preg_replace('/ \$3+/s',"!!",$moveString);
		$moveString = preg_replace('/ \$4+/s',"??",$moveString);
		$moveString = preg_replace('/ \$5+/s',"!?",$moveString);
		$moveString = preg_replace('/ \$6+/s',"?!",$moveString);
		$moveString = preg_replace('/\$[\d]+/s',"",$moveString);
		$moveString = str_replace('*','',$moveString);
		$moveString = trim($moveString);
		$moveString = preg_replace("/\s{2,10}/s"," ",$moveString);
		$moveString = preg_replace("/\+[0-9]/s","+ ",$moveString);
		$moves = explode(" ",$moveString);

		$retArray = array();

		$currentColor = 'black';

		$moveCounter = 1+$startNo;
		for($no=0;$no<count($moves);$no++){

			$origMoves = $moves[$no];
			$moves[$no] = preg_replace('/\d+\.{3}?/s',"",$moves[$no]);
			$moves[$no] = preg_replace('/\d+\.{1}?/s',"",$moves[$no]);
			$moves[$no] = preg_replace("/[\(\)]/s","",$moves[$no]);

			if(!$moves[$no] || $moves[$no]=='1-0' || strstr($moves[$no],'1/2') || $moves[$no]=='0-1')continue;
			if(strlen($moves[$no])<2 || !preg_match("/[0-8O]/",$moves[$no])){
				continue;
			}
			if($currentColor=='white')$currentColor='black'; else $currentColor = 'white';
			if(strstr($origMoves,".."))$currentColor='black';
			$moves[$no] = str_replace(".","",$moves[$no]);
			$retArray[$moveCounter][$currentColor] = $moves[$no];
			if($currentColor=='black')$moveCounter++;
		}

		return $retArray;
	}

	function __parsePgnString()
	{
		#$this->pgnString = preg_replace("/;.*?[\n\r]/","\n",$this->pgnString);	// Removing comments starting with semicolon
		$this->pgnString = preg_replace('/\[\%[^\]]*\]/s','',$this->pgnString);  // Removing fritz notations like [%...]
		$this->pgnString = preg_replace('/{\s*?}/s','',$this->pgnString);
		$lines = explode("\n",$this->pgnString);
		$gameIndex = 0;
		$this->games[$gameIndex]=array();
		$inMoveBlock = false;
		$this->games[0]['moveString']='';
		for($no=0;$no<count($lines);$no++){
			if(!$this->games[$gameIndex])$this->games[$gameIndex]=array();
			if(substr($lines[$no],0,1)=='[' && !$inMoveBlock){	/* Attribute */
				if($inMoveBlock){
					$gameIndex++;
					$this->games[$gameIndex]['moveString']='';
					$inMoveBlock = false;
				}
				$keyValue = $this->getAttribute($lines[$no]);
				$this->games[$gameIndex][$keyValue['key']] = $keyValue['value'];
			}else{
				$inMoveBlock = true;
				if(!isset($this->games[$gameIndex]['moveString']))$this->games[$gameIndex]['moveString']="";
				$this->games[$gameIndex]['moveString'].=$lines[$no]." ";
			}
		}


		for($no=0;$no<count($this->games);$no++){
			$this->games[$no]['moveString'] = str_replace("  "," ",$this->games[$no]['moveString']);
			$this->games[$no]['moveString'] = str_replace('"',"\\\"",$this->games[$no]['moveString']);
			$this->games[$no]['moveString'] = trim($this->games[$no]['moveString']);
			$this->games[$no]['moveString'] = preg_replace('/[\n\r]/si'," ",$this->games[$no]['moveString']);
		}
		$this->pgnParsed=true;

	}

	function readPgnData($pgnFile)
	{
		$this->pgnFile = $pgnFile;
		$fh = fopen($pgnFile,"r");
		$this->pgnString = trim(fread($fh,filesize($pgnFile)));
		fclose($fh);
	}

	function outputFileFromCache($filePath)
	{
		$fp = fopen($filePath,'r');
		fpassthru($fp);
		fclose($fp);
	}

	function writeContentToFileCache($filePath,$content)
	{
		if(!file_exists(FILE_CACHE_FOLDER)){
			mkdir(FILE_CACHE_FOLDER);
		}
		$fh = fopen($filePath,"w");
		fwrite($fh,$content);
		fclose($fh);

	}

	/* Output game list in JSON format */
	function getGameListAsJson()
	{
		if(FILE_CACHE_FOLDER && FILE_CACHE_FOLDER!=''){
			$fileName = FILE_CACHE_FOLDER."/gameList".$this->getPgnNameWithoutExtension().".cache";
			if(file_exists($fileName) && $this->__getPgnTimestamp()<$this->__getFiletimestamp($fileName)){
				$this->outputFileFromCache($fileName);
				exit;
			}
		}
		$retVal = "{\n";
		if(!$this->pgnParsed){
			$this->__parsePgnString();
		}
		for($no=0;$no<count($this->games);$no++){
			if($no)$retVal.="\n,\n";
			$retVal.="\"$no\":{\n";
			if(!isset($this->games[$no]['black']))$this->games[$no]['black']="";
			if(!isset($this->games[$no]['result']))$this->games[$no]['result']="*";
			if(isset($this->games[$no]['white']))$retVal.="\t\"white\" : \"".$this->games[$no]['white']."\",\n";
			if(isset($this->games[$no]['black']))$retVal.="\t\"black\" : \"".$this->games[$no]["black"]."\"";
			if(isset($this->games[$no]['event']))$retVal.=",\n\t\"event\" : \"".$this->games[$no]['event']."\"";
			if(isset($this->games[$no]['result']))$retVal.=",\n\t\"result\" : \"".$this->games[$no]['result']."\"";
			if(isset($this->games[$no]['date']))$retVal.=",\n\t\"date\" : \"".$this->games[$no]['date']."\"\n";
			if(isset($this->games[$no]['annotator']))$retVal.=",\n\t\"annotator\" : \"".$this->games[$no]['annotator']."\"\n";
			if(isset($this->games[$no]['round']))$retVal.=",\n\t\"round\" : \"".$this->games[$no]['round']."\"\n";
			$retVal.="\n}";
		}
		$retVal.="\n}";
		$retVal = $this->getSafeJsString($retVal);

		if(FILE_CACHE_FOLDER && FILE_CACHE_FOLDER!=''){
			$this->writeContentToFileCache($fileName,$retVal);
		}

		return $retVal;
	}

	function outputGameListAsTableRows($objName,$viewString,$props=false)
	{
		if(!$props){
			$props = array("white","black","result");
		}
		if(FILE_CACHE_FOLDER && FILE_CACHE_FOLDER!=''){
			$params = implode("_",$props);
			$params.=$objName;
			$fileName = FILE_CACHE_FOLDER."/phpgametable".$this->getPgnNameWithoutExtension().$params.".cache";
			if(file_exists($fileName) && $this->__getPgnTimestamp()<$this->__getFiletimestamp($fileName)){
				$this->outputFileFromCache($fileName);
				return;
			}
		}
		if(!$this->pgnParsed){
			$this->__parsePgnString();
		}
		$retVal = "";
		for($no=0;$no<count($this->games);$no++){
			$retVal.= "<tr id=\"ChessGameList$no\">\n";
			$retVal.= "\t<td><a href=\"#\" onclick=\"$objName.showGame('$no');return false;\">$viewString</a></td>\n";
			for($no2=0;$no2<count($props);$no2++){
				$retVal.= "\t<td>".$this->games[$no][$props[$no2]]."</td>\n";
			}
			$retVal.="</tr>\n";
		}
		if(FILE_CACHE_FOLDER && FILE_CACHE_FOLDER!=''){
			$this->writeContentToFileCache($fileName,$retVal);
		}
		echo $retVal;
	}

	function getVariationJson($variationArray,$moveNumber)
	{

		$ret = "\n\t\t\t\t\t{";
		for($no=0;$no<count($variationArray);$no++){
			$loopStarted = false;
			if($no)$ret.="\n\t\t\t\t\t\t,";
			$ret.="\n\t\t\t\t\t\t\"".($no)."\" : {";
			foreach($variationArray[$no] as $key=>$value){
				if($loopStarted)$ret.="\n\t\t\t\t\t\t\t,";
				$ret.="\n\t\t\t\t\t\t\t\"".($key+$moveNumber-1)."\" : {";

				if(isset($value['white'])){
					$ret.="\n\t\t\t\t\t\t\t\t\"white\" : \"".$value['white']."\"";
					if(isset($value['black'])){
						$ret.="\n\t\t\t\t\t\t\t\t,\n\t\t\t\t\t\t\t\t\"black\" : \"".$value['black']."\"";
					}
				}elseif(isset($value['black'])){
					$ret.="\n\t\t\t\t\t\t\t\t\"black\" : \"".$value['black']."\"";
				}

				$ret.="\n\t\t\t\t\t\t\t}";
				$loopStarted=true;

			}
			$ret.="\n\t\t\t\t\t\t}";
		}
		$ret.="\n\t\t\t\t\t}";
		return $ret;
	}

	function getNumberOfGames()
	{
		if(FILE_CACHE_FOLDER && FILE_CACHE_FOLDER!=''){
			$fileName = FILE_CACHE_FOLDER."/pgnNumberOfGames".$this->getPgnNameWithoutExtension().".cache";
			if(file_exists($fileName)){
				$this->outputFileFromCache($fileName);
				exit;
			}
		}

		if(!$this->pgnParsed){
			$this->__parsePgnString();
		}

		$ret = count($this->games);
		if(FILE_CACHE_FOLDER && FILE_CACHE_FOLDER!=''){
			$this->writeContentToFileCache($fileName,$ret);
		}

		return $ret;
	}

	function __getPgnTimestamp()
	{
		if($this->pgnTimestamp)return $this->pgnTimestamp;
		$stat = stat($this->pgnFile);
		$this->pgnTimestamp = $stat['mtime'];
		return $this->pgnTimestamp;
	}

	function __getFiletimestamp($file)
	{
		$stat = stat($file);
		return $stat['mtime'];
	}

	/* Output game details for a specific game in Json format */
	function getGameDetailsAsJson($gameIndex,$lastUpdateTimeStamp=0)
	{
		$timestampPgn = $this->__getPgnTimestamp();
		if($lastUpdateTimeStamp && $lastUpdateTimeStamp>$timestampPgn){
			echo "false";	// Time stamp sent to this method and there are no changes, just output "false". This means that this method has been called by an liveUpdate call.
			exit;
		}
		if(FILE_CACHE_FOLDER && FILE_CACHE_FOLDER!=''){
			$fileName = FILE_CACHE_FOLDER."/gameDetails".$this->getPgnNameWithoutExtension()."_gameIndex$gameIndex.cache";
			if(file_exists($fileName) && $this->__getPgnTimestamp()<$this->__getFiletimestamp($fileName)){
				$this->outputFileFromCache($fileName);
				exit;
			}
		}

		if(!$this->pgnParsed){
			$this->__parsePgnString();
		}
		if($gameIndex>=count($this->games)-1){
			$gameIndex = count($this->games)-1;
		}
		$retVal = "{\n";
		$loopStarted = false;

		if(!isset($this->games[$gameIndex]['moves'])){
			$this->games[$gameIndex]['comments'] = $this->getCommentsFromMoveString($this->games[$gameIndex]['moveString']);
			$this->games[$gameIndex]['moves'] = $this->getMovesFromMoveString($this->games[$gameIndex]['moveString']);
			if(isset($this->games[$gameIndex]['comments']['prefaceComment'])){
				$this->games[$gameIndex]['prefaceComment'] = $this->games[$gameIndex]['comments']['prefaceComment'];
			}
			$this->games[$gameIndex]['variationString'] = $this->getVariationsFromMoveString($this->games[$gameIndex]['moveString']);

		}
		foreach($this->games[$gameIndex]['variationString'] as $key=>$moveVariation){
			foreach($moveVariation as $key2=>$colorVariation){
				for($no=0;$no<count($colorVariation);$no++){
					$this->games[$gameIndex]['variations'][$key][$key2][$no] = $this->getMovesFromMoveString($colorVariation[$no]);
				}
			}
		}

		$this->games[$gameIndex]['moveString'] = preg_replace("/{.*?}/s","",$this->games[$gameIndex]['moveString']);

		$variationCounter = array();	// Array used to count variations for each move
		$nodeWritten = false;

		if(!isset($this->games[$gameIndex]['white']))$this->games[$gameIndex]['white']="";
		if(!isset($this->games[$gameIndex]['black']))$this->games[$gameIndex]['black']="";
		if(!isset($this->games[$gameIndex]['result']))$this->games[$gameIndex]['result']="";
		foreach($this->games[$gameIndex] as $key=>$value){

			if($nodeWritten)$retVal.=",\n";
			$nodeWritten = false;
			if($key=='moves'){
				$retVal.="\t\t\"moves\" : {\n";
				for($no2=1;$no2<=count($value);$no2++){
					if($no2>1)$retVal.="\t\t\t,\n";
					$retVal.="\t\t\t$no2 : {";
					if(isset($value[$no2]['white'])){
						$retVal.="\n\t\t\t\t\"white\" : {".
						"\n\t\t\t\t\t\"move\":\"".$value[$no2]['white']."\"";
						if(isset($this->games[$gameIndex]['comments'][$no2]['white'])){
							$retVal.=",\n\t\t\t\t\t\"comment\" : \"".$this->games[$gameIndex]['comments'][$no2]['white']."\"";
						}
						if(isset($this->games[$gameIndex]['variationString'][$no2]['white'])){
							$variationString = $this->getVariationJson($this->games[$gameIndex]['variations'][$no2]['white'],$no2);
							$retVal.=",\n\t\t\t\t\t\"variation\" : ".$variationString."";
						}
						$retVal.="\n\t\t\t\t}\n";
						if(isset($value[$no2]['black']))$retVal.="\n\t\t\t\t,";
					}
					if(isset($value[$no2]['black'])){
						$retVal.="\n\t\t\t\t\"black\" : {".
						"\n\t\t\t\t\t\"move\":\"".$value[$no2]['black']."\"";
						if(isset($this->games[$gameIndex]['comments'][$no2]['black'])){
							$retVal.=",\n\t\t\t\t\t\"comment\" : \"".$this->games[$gameIndex]['comments'][$no2]['black']."\"";
						}
						if(isset($this->games[$gameIndex]['variationString'][$no2]['black'])){
							$variationString = $this->getVariationJson($this->games[$gameIndex]['variations'][$no2]['black'],$no2);
							$retVal.=",\n\t\t\t\t\t\"variation\" : ".$variationString."";
						}
						$retVal.="\n\t\t\t\t}\n";

					}
					$retVal.="\t\t\t\n\t\t\t}\n";
				}
				$retVal.="\n\t\t}";
				$nodeWritten =true;
			}elseif($key=='variationString'){
			}elseif($key=='variations'){
			}elseif($key=='comments'){
			}else{
				$retVal.= "\t\t\"$key\" : \"$value\"";
				$nodeWritten =true;
			}
			$loopStarted = true;
		}
		while(substr($retVal,strlen($retVal)-2,1)==',')$retVal = substr($retVal,0,strlen($retVal)-2);

		$retVal.="\n}";
		$retVal = $this->getSafeJsString($retVal);

		if(FILE_CACHE_FOLDER && FILE_CACHE_FOLDER!=''){
			$this->writeContentToFileCache($fileName,$retVal);
		}

		return $retVal;
	}

	/* Sample function only. Output entire PGN file in JSON format. */
	function toJson()
	{
		if(FILE_CACHE_FOLDER && FILE_CACHE_FOLDER!=''){
			$fileName = FILE_CACHE_FOLDER."/pgnAsJson".$this->getPgnNameWithoutExtension().".cache";
			if(file_exists($fileName)){
				$this->outputFileFromCache($fileName);
				exit;
			}
		}

		if(!$this->pgnParsed){
			$this->__parsePgnString();
		}

		$retVal = "{\n ";
		for($no=0;$no<count($this->games);$no++){
			if($no)$retVal.="\t,\n";
			$retVal.="\t\"".$no."\":{\n";

			$loopStarted = false;
			foreach($this->games[$no] as $key=>$value){
				if($loopStarted)$retVal.=",\n";
				if(is_array($value)){
					$retVal.="\t\t\"moves\" : {\n";
					for($no2=1;$no2<count($value);$no2++){
						if($no2>1)$retVal.="\t\t\t,\n";
						$retVal.="\t\t\t\"$no2\" : {";
						if(isset($value[$no2]['white'])){
							$retVal.="\n\t\t\t\t\"white\" : \"".$value[$no2]['white']."\"";
							if(isset($value[$no2]['black']))$retVal.=",";
						}
						if(isset($value[$no2]['black']))$retVal.="\n\t\t\t\t\"black\" : \"".$value[$no2]['black']."\"";
						$retVal.="\t\t\t\n\t\t\t}\n";
					}
					$retVal.="\n\t\t}";
				}else{
					$retVal.= "\t\t\"$key\":\"$value\"";
				}
				$loopStarted = true;
			}
			$retVal.="\t}\n";
		}
		$retVal = str_replace("'","\\'",$retVal);
		$retVal.=" \n}";
		$retVal = $this->getSafeJsString($retVal);

		if(FILE_CACHE_FOLDER && FILE_CACHE_FOLDER!=''){
			$this->writeContentToFileCache($fileName,$retVal);
		}

		return $retVal;
	}
}
