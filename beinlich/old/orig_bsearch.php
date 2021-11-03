
<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Beinlich Search Results</title>
<style type="text/css">
<!--
body {
	margin: 10px;
	padding: 0;
	color: #000000;
	background: #ffffff;
	font: 12px/1.2 sans-serif;
	}

h1 {
	font: 20px sans-serif;
	font-weight: bold;
	padding: 3px;
	}
	
td, th, a {
	font: 12px/1.2 sans-serif;
	text-align: left;
	vertical-align: top;
	}

p.validation {
	color: #cccccc;
	font-size: 9px;
	padding: 5px 0 0 0;
	}
	
/* transliteration */
td.t {
	font-weight: bold;
	}

/* meaning */
td .m {
	}

/* source */
td.s {
	font-style: italic;
	}
-->	
</style>
</head>
<body>
<h1>Beinlich Search Results</h1>
<table>
<tr><th>Word</th><th>Translation</th><th>Reference</th></tr>

<?php
// logic based on original perl by: 
# Paul Sciortino
# paul@hieroglyphs.net
# February 8, 2002

// this version Shaun Osborne 2008


// globals
$datafilename = 'beinlich.txt';
$inputlines = array();
$foundlines = array();
$search = '';
$gsearch = '';
$searchpage="beinform.html";
$querystrings=array();
$querystringargs = explode("&",$_SERVER["QUERY_STRING"]);
foreach($querystringargs as $argument) 
       {
    	  $argvalues = explode("=",$argument);
    	  switch($argvalues[0])
              {
      			  case "term":
      			               $search=urldecode($argvalues[1]);
      			               break;
      			  case "german":
      			               $gsearch=urldecode($argvalues[1]);
       			  default:
      			             //ingnore
              } # end of switch
      } # end of foreach
      
//echo $_SERVER['POSTQUERY_STRING'];
//exit;

// setup
ini_set('auto_detect_line_endings',1); //this is required so file() recognises Mac CR line ends...
error_reporting(E_ALL); // only needed for debugging





$inputlines = file($datafilename);
if(!$inputlines)
  {
  echo "ERROR: Sheesh! no input file [$datafilename]";
  }
foreach($inputlines as $line)
       {
       $lineparts = explode('|',$line);
       #print_r($lineparts);
       if($search && preg_match("#.*$search.*#",$lineparts[0]))
         {
          #print_r($line);
          #echo '<br/>';
          array_push($foundlines, $line);
          #print_r($matches);
          #echo '<br/>';
        }
        if($gsearch && preg_match("#.*$gsearch.*#",$lineparts[1]))
         {
          #print_r($line);
          #echo '<br/>';
          array_push($foundlines, $line);
          #print_r($matches);
          #echo '<br/>';
        }
       }
    
       
foreach($foundlines as $line)
       {
       echo'<tr>';
       $lineparts = explode('|',$line);
       echo "<td class=\"t\">$lineparts[0]</td>";
       echo "<td class=\"m\">$lineparts[1]</td>";
       echo "<td class=\"s\">$lineparts[2]</td>";
       #foreach($lineparts as $part)
       #{
       #echo '<td>';
       #echo "$part";
       #echo '</td>';
       #}
       #print_r($line);
          #echo '<br/>';
          #array_push($found, $line);
          #print_r($matches);
          #echo '<br/>';
        }
       echo'</tr>';
       
               
#print_r($found);

function do_header()
{
echo '';}
?>
<hr />
<p><a href="<?php echo $searchpage; ?>">Search again</a></p>
<!--
<p class="validation"><a href="http://validator.w3.org/check/referer">XHTML 1.0</a> | <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS 2.0</a></p>
-->
</body>
</html>