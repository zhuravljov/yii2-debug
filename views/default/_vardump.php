<?php
/* @var DefaultController $this */
/* @var mixed $data */
/* @var int $depth */
/* @var bool $highlightCode */
?>
<?php if ($highlightCode): ?>
	<div class="well well-small">
		<?php CVarDumper::dump($data, $depth, true); ?>
	</div>
<?php else: ?>
	<pre><?php CVarDumper::dump($data, $depth, false); ?></pre>
<?php endif; ?>
