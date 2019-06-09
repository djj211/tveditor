<html>
<script>
	var user = "<?php echo $role; ?>";
</script>
<div class="help help-lg1">
	<?php if($role != "read"):?>
	To Add A New Show Enter the Name, and the Season and Epsiode to Start Downloading From. Then Click 'Add'.
	<?php else: ?>
	To Lookup a show in the TVDB Enter the Name, and then Click 'View'.
	<?php endif; ?>
</div>
<?php echo $this->Form->create(null, array('id' => 'new_form')); ?>
<div class="center">
<div class="wrap>"
	<div class="inline-row">
		<div class='block-new'>
			<?php echo $this->Form->input('New Show', array('type' => 'text', 'class' => 'glowing-border', 'name' => 'new_show')) ?>
		</div>
		<?php if($role != "read"):?>
		<div class='block-new-small'> 
			<?php echo $this->Form->input('Season', array('type' => 'number', 'class' => 'glowing-border season-ep', 'placeholder' => '##')) ?>
		</div>
		<div class='block-new-small'> 
			<?php echo $this->Form->input('Episode', array('type' => 'number', 'class' => 'glowing-border season-ep', 'placeholder' => '##')) ?>
		</div>
		<div class='block-new-btn'>
			<?php echo $this->Form->button('Add', ['type' => 'button', 'id' => 'add_new', 'class' => 'modern-small embossed-link']); ?>
		</div>
		<?php else: ?>
			<?php echo $this->Form->input('Season', array('type' => 'hidden', 'class' => 'glowing-border season-ep', 'placeholder' => '##')) ?>
			<?php echo $this->Form->input('Episode', array('type' => 'hidden', 'class' => 'glowing-border season-ep', 'placeholder' => '##')) ?>
		<div class='block-new-btn'>
			<?php echo $this->Form->button('View', ['type' => 'button', 'id' => 'add_new', 'class' => 'modern-small embossed-link']); ?>
		</div>
		<?php endif; ?>
	</div>
</div>
</div>
<div class="help help-lg2">
	<?php if($role != "read"):?>
	You can Edit the Shows that downloaded by Adding/Removing or Editing the Show Properties.
	<?php else: ?>
	You May View a Show's Properties Below.
	<?php endif; ?>
</div>
<?php echo $this->Form->end(array('Save')); ?>
<?php echo $this->Form->create(null, array('id' => 'edit_form', 'url' => ['controller' => 'Edits', 'action' => 'index'])); ?>
<div class="center btm-padd">
<div class="wrap>"
	<div class="inline-row">
		<div class='block'>
			<div class="shows-csv active">
				<?php foreach($shows as $show): ?>
				<div class="show selectDisable unselected" id="<?php echo $show ?>"><span><?php echo $show ?></span></div>
				<?php endforeach ?>
			</div>
		</div>
		<div class='btn-block'>
		<?php if($role != "read"):?>
			<div>	
				<?php echo $this->Form->button('Remove', ['type' => 'button', 'class' => 'modern embossed-link', 'id' => 'btn_remove']); ?>
			</div>
			<div>	
				<?php echo $this->Form->button('Add', ['type' => 'button', 'class' => 'modern embossed-link', 'id' => 'btn_add']); ?>
			</div>
		<?php endif; ?>
		</div>
		<div class='block'>
			<div class="shows-csv removed">
				<?php foreach($removed as $show): ?>
				<div class="show selectDisable unselected" id="<?php echo $show ?>"><span><?php echo $show ?></span></div>
				<?php endforeach ?>
			</div>
		</div>
		<?php if($role != "read"):?>
		<div class='btn-block-lst'>
			<?php echo $this->Form->button('Submit', ['type' => 'button', 'class' => 'modern embossed-link', 'id' => 'submit_shows']); ?>
		</div>
		<?php endif; ?>
	</div>
<?php echo $this->Form->end(array('Save')); ?>
</div>
</div>
<div id="confirmBox"></div>
<div id="editDiv"></div>
<div id="mobileInfo"></div>
<div id="message"></div>
<div id="tvDbShows" title=""></div>
</html>