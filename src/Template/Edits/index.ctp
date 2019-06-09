<?php if(!empty($results)): foreach($results as $show): ?>
<h3><?php echo $show[0]; ?></h3>
<div>Last Downloaded:</div>
<div class="show">
<?php if(!empty($show[1]) && !empty($show[2])):?>
Season: <?php echo $show[1]; ?> Episode: <?php echo $show[2]; ?>
<?php else: ?>
None Downloaded Yet.
<?php endif; ?>
</div>
<div>Previously Set Start Value:</div>
<?php if(!empty($show[3]) && !empty($show[4])):?>
Season: <?php echo $show[3]; ?> Episode: <?php echo $show[4]; ?>
<?php else: ?>
Not Set
<?php endif; ?>
<?php endforeach; else: ?>
Shows Not Found!!! What Happened!
<?php endif; ?>