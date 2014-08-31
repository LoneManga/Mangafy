<?php global $voteCount, $voteRating, $voteMyRating, $voteUrl; ?>
<tr>
<td class="infoTabOne">
<?php
	for( $i=1; $i<=20; $i++) {
		if($voteMyRating == $i)
			echo '<input value="'.$i.'" class="star {split:4}" id="star'.$i.' checked="checked"/>';
		else
			echo '<input value="'.$i.'" class="star {split:4}" id="star'.$i.'"/>';
	}
?> 
</td>
<td><p style="margin: 0;" id="voteStats"><span id="voteNumber"><?=$voteCount?></span> votes recorded. (<span id="voteRating"><?=$voteRating?></span> rating)</p></td>
</tr>