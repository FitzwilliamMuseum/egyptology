<?php
// This PHP version courtesy Shaun Osborne 2008
// shaun@cybergate9.net

// output the html page construct, style etc before search
echo '<?xml version="1.0" encoding="utf-8" ?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>Email Search Results</title>
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
<h1>Email Search Results</h1>



<?php
// globals
$datafilename = 'emaildata_ansi.txt';
$inputlines = array();
$foundlines = array();
$sortedlines = array();
$search = '';
$isearch = '';
$sort = 'name';
$logic = 'and';
$searchpage="email.html";

// setup
ini_set('auto_detect_line_endings',1); //this is required so file() recognises Mac CR line ends on windoze boxes...

// process the url arguments
$querystringargs = explode("&",$_SERVER["QUERY_STRING"]);
foreach($querystringargs as $argument) 
       {
    	  $argvalues = explode("=",$argument);
    	  switch($argvalues[0])
              {
      			  case "name":
      			               $search=urldecode($argvalues[1]);
      			               break;
      			  case "inst":
      			               $isearch=urldecode($argvalues[1]);
      			  case "sort":
      			               $sort=urldecode($argvalues[1]);
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
  do_andsearch(); 
  //print_r($foundlines);
  do_searchlinehtml(); // tell user what they searched for and other niceties
  echo '&nbsp;|&nbsp; <a href="javascript:history.back()">Refine Search</a>';
  echo '&nbsp;|&nbsp;<a href="'.$searchpage.'">New Search</a></p>';
  echo '<hr/>';
  if($foundlines) // if there's results
     {
     switch($sort) // sort them
       {
       case 'inst':
           foreach($foundlines as $line)
                  {
                   $lineparts = explode('|',$line);
                   $sortedlines[$lineparts[1].$lineparts[0]]=$line;
                  }
           ksort($sortedlines);
           break;
       case 'name':
           foreach($foundlines as $line)
                  {
                   $lineparts = explode('|',$line);
                   $sortedlines[$lineparts[0].$lineparts[1]]=$line;
                  }
           ksort($sortedlines);
           break;
       default:
            echo 'default sort';
           //should never get here
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
  echo '<hr/><p>Sorry! please enter at least four characters in the search fields</p>';
 }     

// end of main() 

// ***** FUNCTIONS
 
// echo summary of search back at user
function do_searchlinehtml()
{
  global $search, $isearch,  $logic;
  $terms= array();
  echo '<p>You searched for: ';  
  if($search){array_push($terms, array($search,"Name"));}
  if($isearch){array_push($terms, array($isearch,"Institution"));}  
  foreach($terms as $key => $term)
         {
         if($key <= 0){echo utf8_encode("'$term[0]' ($term[1]) ");}
         else{ echo utf8_encode("$logic '$term[0]' ($term[1]) ");} 
         }
}

// validation code - minimal, 'yes we have some values from the form' check
function validated()
{
 global $search, $isearch;
 if((strlen($search)+strlen($isearch)) >= 4) return 1;
// if(strlen($search) >= 4) return 1;
// if(strlen($isearch) >= 4) return 1;

 // else
 return 0;
}

// search input array based on 'and' logic & put results into $foundlines 
function do_andsearch()
{
 global $inputlines, $foundlines, $search, $isearch;
 // search using a logical 'and' process and store all results into $foundlines  
 foreach($inputlines as $line)
     {
     $lineparts = explode('|',$line);
     if(
        (preg_match("#.*$search.*#i",removeaccents($lineparts[0])) || preg_match("#.*$search.*#i",$lineparts[0]))
        &&
        (preg_match("#.*$isearch.*#i",removeaccents($lineparts[1])) || preg_match("#.*$isearch.*#i",$lineparts[1])) 
       )
         {
           array_push($foundlines, $line);
         }
     }
} 

 
   
// output the results table html       
function do_resultshtml()
{
global $sortedlines;
echo '<table><tr><th>Name</th><th>Institution</th><th>Email</th></tr>';
// process and display $foundlines
foreach($sortedlines as $line)
       {
       echo'<tr>';
       $lineparts = explode('|',$line);
       $emails = explode(',',$lineparts[2]);
       echo "<td class=\"t\">".utf8_encode($lineparts[0])."</td>";
       echo "<td class=\"m\">".utf8_encode($lineparts[1])."</td>";
       //echo "<td class=\"m\">".utf8_encode(removeaccents($lineparts[1]))."</td>";
       echo "<td class=\"s\">";
       foreach($emails as $key => $email)
           {
           if($key >= 1)
             {
              echo "<br/>";
              do_obf_emaillink($email,'');
             }
            else
             {
             do_obf_emaillink($email,'');
             } 
          
           //echo "</td>";
           //echo $email;

           }
       echo "</td>" ;
                 
              
       echo'</tr>';
       }  
echo '</table>';     
}   

/**
* Generate a javascript encoded email 
* 
* @param string emailaddress a valid email address
* 
* @param string any classing (css) you may wish to apply to the a href
*/
function do_obf_emaillink($emailaddress,$class)
{
/* get the name - pre @ */
$result = preg_match("/^.*\@/",$emailaddress,$matches);
if(!$result)
{echo "<b>Last known address failed</b>";return;}
$name = $matches[0];
$name=preg_replace("/\@/","",$name);
/* get the domain - pre @ */
$result = preg_match("/\@.*$/",$emailaddress,$matches);
if(!$result)
{echo "<b>Last known address failed</b>";return;}
$domain = $matches[0];
$domain=preg_replace("/\@/","",$domain);

/* encode name domain and mailto: as html entity numbers */
$encname = preg_replace('/[\x00-\xFF]/e', '"&#".ord("$0").";"', $name);
$encdomain = preg_replace('/[\x00-\xFF]/e', '"&#".ord("$0").";"', $domain);
//$mailto = preg_replace('/[\x00-\xFF]/e', '"&#".ord("$0").";"', "mailto:");

echo "
<SCRIPT LANGUAGE=\"javascript\">
// <!-- 

	var fourth = '$encname';
	var fifth = '$encdomain';
	document.write('<p');
	document.write('class=\"".$class."\"');
	document.write('>');
	document.write(fourth);
	document.write('&#64;');
	document.write(fifth);
	document.write('</p>');
// -->
</script>";
//echo "<noscript><a href=\"".$mailto.$encname."&#64;".$encdomain."\"";
//if($class) echo "class=\"".$class."\"";
//echo ">".$linktext."</a></noscript>";

}
function removeaccents($string)
{
return strtr($string,
utf8_decode("ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ"),
utf8_decode("SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy")
);
}

// below - finish off the html
?>

<hr />
<p><a href="javascript:history.back()">Refine Search</a>&nbsp;|&nbsp;<a href="<?php echo $searchpage; ?>">New Search </a>&nbsp;|&nbsp; <a href="email.html">Email Home Page</a></p>
<!-- p class="validation"><a href="http://validator.w3.org/check/referer">XHTML 1.0</a> | <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS 2.0</a></p -->

</body>
</html>