<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = 'TvEditor';
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $cakeDescription ?>:
        <?= $titleHead ?>
    </title>
    <?= $this->Html->meta('icon') ?>
	
    <?= $this->Html->css('base.css') ?>
    <?= $this->Html->css('fire.css') ?>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <nav class="top-bar expanded" data-topbar role="navigation">
        <ul class="title-area large-3 medium-4 columns">
            <li class="name">
 				<h1><a href="/tveditor">TV Download Editor</a></h1>
            </li>
        </ul>
        <section class="top-bar-section">
            <ul class="right">
                <li><a href="/tveditor">Home</a></li>
                <li><a href="/tveditor/users/myaccount"><?= $myAccount ?></a></li>
            	<li><a href="/tveditor/logout">Logout</a></li>
            </ul>
        </section>
    </nav>
    <?= $this->Flash->render() ?>
    <section class="container clearfix">
        <font color="red">Unauthorized: You are not authorized to access this content.</font>
    </section>
    <footer>
    	<div id="footer">Copyright © DJJ 2016 v2.0.0</div>
    </footer>
</body>
</html>
