<?php
use Cake\Core\Configure;
use Cake\Error\Debugger;

$user = $this->request->session()->read('Auth.User');

if (Configure::read('debug')):
    $this->layout = 'dev_error';

    $this->assign('title', $message);
    $this->assign('templateName', 'login.ctp');

    $this->start('file');
else:
	$this->set('$titleHead', 'Error');
	
	if ($user)
	{
		$this->set('myAccount', $user[username]);
		
		if ($user[role] == 'admin')
		{
			$this->layout = 'admin';
		}
		else if ($user[role] == 'manage')
		{
			$this->layout = 'default';
		}
		else if ($user[role] == 'read')
		{
			$this->layout = 'read';
		}
	}
	else 
	{
		$this->layout = 'login';
	}
?>
<?php if (!empty($error->queryString)) : ?>
    <p class="notice">
        <strong>SQL Query: </strong>
        <?= h($error->queryString) ?>
    </p>
<?php endif; ?>
<?php if (!empty($error->params)) : ?>
        <strong>SQL Query Params: </strong>
        <?= Debugger::dump($error->params) ?>
<?php endif; ?>
<?php if ($error instanceof Error) : ?>
        <strong>Error in: </strong>
        <?= sprintf('%s, line %s', str_replace(ROOT, 'ROOT', $error->getFile()), $error->getLine()) ?>
<?php endif; ?>
<?php
    echo $this->element('auto_table_warning');

    if (extension_loaded('xdebug')):
        xdebug_print_function_stack();
    endif;

    $this->end();
endif;
?>
<h2><?= __d('cake', 'Oh No! Something went wrong. Please try again. If the problem continues, please contact your server adminstrator.') ?></h2>
<p style="text-align: center;">
	<?php echo $this->Html->image('error.jpg', ['alt' => 'Error', 'id' => 'error_img']); ?>
</p>
<p class="error">
    <strong><?= __d('cake', 'Error') ?>: </strong>
    <?= h($message) ?>
</p>
