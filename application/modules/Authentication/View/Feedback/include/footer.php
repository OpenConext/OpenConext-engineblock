<p>
    Debug information:
</p>
<table>
    <?php
    if (!empty($_SESSION['debugInfo']) && is_array($_SESSION['debugInfo'])) {
        foreach($_SESSION['debugInfo'] as $name => $value) {
        ?>
            <tr>
                <td><strong><?php echo $name?>:</strong></td>
                <td><?php echo $value;?></td>
            </tr>
        <?php
        }
    }
    ?>
     </table>
   </div>
</div>

   <?php if (isset($exception)) { ?>
    <strong><?php echo $exception->getMessage();?></strong>
    <pre>
    <?php var_dump($exception); ?>
</pre>

    <?php if (isset($exception->xdebug_message)) { echo $exception->xdebug_message; } ?>
<?php } ?>