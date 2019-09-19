<?php header('Content-type: application/xml'); 
date_default_timezone_set('Africa/Johannesburg');
define('DB_SERVER', 'localhost');
define('DB_USER', 'memeclub_memebeam');
define('DB_PASSWORD', 'cCRm8cN9BCbC3wqCEF');
define('DB_NAME', 'memeclub_app');
 
$conn = new PDO("mysql:host=".DB_SERVER.";port=8889;dbname=".DB_NAME, DB_USER, DB_PASSWORD);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>\n";
echo "<channel>\n";

echo "<title>Memeclub RSS Feed</title>\n";
echo "<description>You affirm that you are at least 18 years of age or the age of majority in the jurisdiction you are accessing this website from. If you are under 18 or the applicable age of majority, you are not permitted to submit personal information to us or use this website. You also represent that the jurisdiction from which you access this website does not prohibit the receiving or viewing of sexually explicit content.</description>\n";
echo "<link>https://memeclub.xyz/</link>\n";

 
$stmt = $conn->query('SELECT * FROM memes ORDER BY created DESC LIMIT 500');
while($row = $stmt->fetch(PDO::FETCH_OBJ)) {
    
     echo "<item>\n";
         echo "<title>https://memeclub.xyz/</title>\n";
         echo "<description><![CDATA[<img src='https://memeclub.xyz/$row->path' alt=''>]]></description>\n";
         echo "<pubDate>".date('D, d M Y H:i:s O',strtotime($row->created))."</pubDate>\n";
         echo "<link>https://memeclub.xyz/$row->path</link>\n";
         echo "<guid>https://memeclub.xyz/$row->name</guid>\n";
         echo "<atom:link href='https://memeclub.xyz/$row->path' rel='self' type='application/rss+xml'/>\n";
     echo "</item>\n";

}

echo "</channel>\n";
echo "</rss>\n";
?>