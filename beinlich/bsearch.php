<?php
// loosely based on original one field search perl script by: 
# Paul Sciortino
# paul@hieroglyphs.net
# February 8, 2002
# (bsearch.pl in the same directory as this file)

// This expanded PHP version courtesy Shaun Osborne 2008
// shaun@cybergate9.net

// output the html page construct, style etc before search
echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>Beinlich Search Results</title>
<link href="../code/er.css" rel="styleSheet" type="text/css">
<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
<style type="text/css">
<!--
body {
	margin: 10px;
	padding: 0;
	color: #000000;
	background: #ffffff;
	font: 12px/1.2 sans-serif;
	}


	
td, th {
	font: 12px/1.2 sans-serif;
	text-align: left;
	vertical-align: top;
	padding-left: 10px;
	}

p.validation {
	color: #cccccc;
	font-size: 9px;
	padding: 5px 0 0 0;
	}
	
/* transliteration */
td.t {font-weight: bold;}

/* meaning */
td .m {	}

/* source */
td.s {font-style: italic;}
	
/* table headers */	
th {font-size: 120%;font-weight: bold;}

-->	
</style>
</head>
<body>
<h1><img align="middle" src="a.gif">Beinlich Search Results<img align="middle" src="d.gif"></h1>



<?php
// globals
$datafilename = 'beinlich.txt';
$inputlines = array();
$foundlines = array();
$sortedlines = array();
$search = '';
$gsearch = '';
$rsearch = '';
$logic = 'and';
$sort = 'word';
$case = ''; // not used atm
$searchpage="beinlich.html";

// setup
ini_set('auto_detect_line_endings',1); //this is required so file() recognises Mac CR line ends on windoze boxes...

// process the url arguments
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
      			               break;
      			  case "ref":
      			               $rsearch=urldecode($argvalues[1]);
      			               break;
      			  case "logic":
      			               $logic=urldecode($argvalues[1]);
      			               break;
      			  case "sort":
      			               $sort=urldecode($argvalues[1]);
      			               break;
      			  case "case":
      			               $case=urldecode($argvalues[1]);
      			               break;
       			  default:
      			             //ignore anything else
              } 
       } 
      
// get the whole file into $inputlines array
$inputlines = file($datafilename);
if(!$inputlines)
  {
  echo "ERROR: Sheesh! no input file [$datafilename]?";
  exit;
  }

// do the main() of the thing
if(validated()) // if we're happy with input from form
  {
  if($logic=='and') // do the appropriate searching
     {
     do_andsearch();
     }
  else
     {
     do_orsearch();
     }    
  do_searchlinehtml(); // tell user what they searched for and other niceties
  echo '&nbsp;|&nbsp; <a href="javascript:history.back()">Refine Search</a>';
  echo '&nbsp;|&nbsp;<a href="'.$searchpage.'">New Search</a></p>';
  echo '<hr/>';
  if($foundlines) // if there's results
     {
     switch($sort) // sort them
       {
       case 'trans':
           foreach($foundlines as $line)
                  {
                   $lineparts = explode('|',$line);
                   $sortedlines[$lineparts[1].$lineparts[0]]=$line;
                  }
           ksort($sortedlines);
           break;
        case 'ref':
           foreach($foundlines as $line)
                  {
                   $lineparts = explode('|',$line);
                   $sortedlines[$lineparts[2].$lineparts[0]]=$line;
                  }
           ksort($sortedlines);
           break;

       default:
           sort($foundlines);
           $sortedlines=$foundlines;
           break;                      
      }
     do_resultshtml(); //output them to user
     }
  else // nothing returned - bugger... :)
     {
     echo '<p>No matching results found.</p>';
     }   
  }
else // not happy with the form - tell the user
 {
  echo '<hr/><p>Oops! please enter at least one search term</p>';
 }     

// end of main() 

// ***** FUNCTIONS
 
// echo summary of search back at user
function do_searchlinehtml()
{
  global $search, $gsearch, $rsearch, $logic;
  $terms= array();
  echo '<p>You searched for: ';  
  if($search){array_push($terms, array($search,"Word"));}
  if($gsearch){array_push($terms, array($gsearch,"Translation"));} 
  if($rsearch){array_push($terms, array($rsearch,"Reference"));} 
  foreach($terms as $key => $term)
         {
         if($key <= 0){echo "'$term[0]' ($term[1]) ";}
         else{ echo "$logic '$term[0]' ($term[1]) ";} 
         }
}

// validation code - minimal, 'yes we have some values from the form' check
function validated()
{
 global $search, $gsearch, $rsearch;
 if(strlen($search) >= 1) return 1;
 if(strlen($gsearch) >= 1) return 1;
 if(strlen($rsearch) >= 1) return 1;
 // else
 return 0;
}

// search input array based on 'and' logic & put results into $foundlines 
function do_andsearch()
{
 global $inputlines, $foundlines, $search, $gsearch, $rsearch, $case;
 // search using a logical 'and' process and store all results into $foundlines  
 foreach($inputlines as $line)
     {
     $lineparts = explode('|',$line);
     if((preg_match("#.*$search.*#",$lineparts[0])) &&
        (preg_match("#.*$gsearch.*#i",$lineparts[1])) &&
         (preg_match("#.*$rsearch.*#i",$lineparts[2])))
         {
           array_push($foundlines, $line);
         }
     }
} 

// search using a logical 'or' process and store all results into $foundlines 
function do_orsearch()
{
 global $inputlines, $foundlines, $search, $gsearch, $rsearch, $case;
 foreach($inputlines as $line)
       {
       $lineparts = explode('|',$line);
       if($search != '' && preg_match("#.*$search.*#",$lineparts[0]))
         {
          array_push($foundlines, $line);
         }
       if($gsearch != '' && preg_match("#.*$gsearch.*#i",$lineparts[1]))
         {
          array_push($foundlines, $line);
         }
       if($rsearch != '' && preg_match("#.*$rsearch.*#i",$lineparts[2]))
         {
          array_push($foundlines, $line);
         }         
       }
} 
   
// output the results table html       
function do_resultshtml()
{
global $sortedlines;
echo '<table><tr><th>Word</th><th>Translation</th><th>Reference</th></tr>';
// process and display $foundlines
foreach($sortedlines as $line)
       {
       echo'<tr>';
       $lineparts = explode('|',utf8_encode($line));
       echo "<td class=\"t\">$lineparts[0]</td>";
       echo "<td class=\"m\">$lineparts[1]</td>";
       echo "<td class=\"s\">$lineparts[2]</td>";
       echo'</tr>';
       }  
echo '</table>';     
}   
// below - finish off the html
?>

<hr />
<p><a href="javascript:history.back()">Refine Search</a>&nbsp;|&nbsp;<a href="<?php echo $searchpage; ?>">New Search </a>&nbsp;|&nbsp; <a href="beinlich.html">Beinlich Home Page</a>&nbsp;|&nbsp;<a href="beinlich.html#copy">Credits</a></p>
<!-- p class="validation"><a href="http://validator.w3.org/check/referer">XHTML 1.0</a> | <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS 2.0</a></p -->

</body>
</html>