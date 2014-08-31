<?php kommiku_header(); ?>
<div id="content" class="narrowcolumn home">
	<div class="breadcrumb">
		<h2 class="kommiku-bread" style="margin: 0 15px;"><a href="<?=HTTP_HOST?><?=$kommiku['url']['series']?>"><?=$kommiku["title"]["series"]?></a></h2> 
	</div>
	<?php kommiku_series_information();	?>
    <div class="column" style="float: none;">
		<?php kommiku_chapter_table_list(); ?>
	</div>
</div>
<?php kommiku_footer(); ?>
