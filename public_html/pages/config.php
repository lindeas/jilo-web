<?php ?>

<div>

<p>Jilo web configuration</p>

<ul>
<?php foreach ($config as $config_item=>$config_value) { ?>
    <li><?php echo htmlspecialchars($config_item) . ': ' . htmlspecialchars($config_value); ?></li>
<?php } ?>
</ul>

</div>
