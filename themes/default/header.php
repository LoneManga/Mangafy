<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<title><?php kommiku_title(); ?> | <?php bloginfo('name'); ?></title>

<link rel="stylesheet" href="<?php kommiku_css(); ?>" type="text/css" media="screen" />
<link rel="alternate" type="application/rss+xml" title="<?=$series['title']?> RSS Feed |<?php bloginfo('name'); ?>" href="<?=HTTP_HOST.get_option( "kommiku_url_feed" ).'/'.$series["slug"]; ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<?php kommiku_rating_header();?>

</head>
<body>
<center>
<div id="page">
<div id="header">
	<div id="top-header">
		<h1><a href="<?php echo get_option('home'); ?>"><?php bloginfo('name'); ?></a></h1>
	</div>
</div>
</center>