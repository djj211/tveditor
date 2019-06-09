<h1>Series</h1>
<?php if(!empty($shows)): foreach($shows as $show): ?>
<div class="serie-box">
<?php echo $show->series->name; ?> Season: <?php echo $show->season; ?> Episode: <?php echo $show->number; ?>
</div>
<?php endforeach; else: ?>
None Downloaded Yet...
<?php endif; ?>

