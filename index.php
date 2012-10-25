<?php
require_once('lib/Grabatar.php');
$gravatar = Grabatar::getInstance();
$gravatar->setCacheDir("cache/");
$gravatar->setDefaults(100, "g", "identicon");
$i = $gravatar->grab("test@test.com");
?>

<h1>Grabatar</h1>

PHP Wrapper for the Gravatar API. 

<ul>

<li><?php echo $i; ?></li>

<li><img src="<?php echo $i; ?>"></li>

</ul>
