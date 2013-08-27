   </div>
</div>

   <?php if (isset($exception)) { ?>
    <strong><?php echo $exception->getMessage();?></strong>
    <pre>
    <?php var_dump($exception); ?>
</pre>

    <?php if (isset($exception->xdebug_message)) { echo $exception->xdebug_message; } ?>
<?php } ?>