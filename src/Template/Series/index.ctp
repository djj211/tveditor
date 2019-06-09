<h1>Series-Last Downloaded</h1>
<?php if(!empty($series)): foreach($series as $serie): ?>
<div class="serie-box">
<?php if(!empty($serie->name)): ?>
<?php echo $serie->name; ?> Season: <?php echo $serie->seasons->season; ?>
<?php endif; ?>
</div>
<?php endforeach; else: ?>
None Downloaded Yet
<?php endif; ?>
