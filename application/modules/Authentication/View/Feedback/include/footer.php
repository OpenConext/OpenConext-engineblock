<?php
$log = EngineBlock_ApplicationSingleton::getInstance()->getLog();
$log->log('Showing feedback page with message: ' . $layout->title, EngineBlock_Log::INFO);
$log->getQueueWriter()->flush('feedback page shown');
?>

    <hr />

    <table>
    <?php
    if (!empty($_SESSION['feedbackInfo']) && is_array($_SESSION['feedbackInfo'])) {
        foreach($_SESSION['feedbackInfo'] as $name => $value) {
            if (empty($value)) {
                continue;
            }
        ?>
            <tr>
                <td><strong><?php echo htmlentities($this->t($name))?>:</strong></td>
                <td><?php echo htmlentities($value);?></td>
            </tr>
        <?php
        }
    }
    ?>
     </table>

    <hr />

    <p>
        <?php echo $this->t('error_help_desc'); ?>
    </p>

    <?php
    // @todo make the number of steps in history that the back button makes dynamic, it is not always 2
    ?>
    <div class="button-row">
        <a href="#" id="GoBack"  class="submit button-tertiary" onclick="history.back(-2); return false;">
            <?php echo $this->t('go_back'); ?>
            <span class="btn-wrap-right">&nbsp;</span>
        </a>
    </div>

   </div>
</div>

   <?php if (isset($exception)) { ?>
    <strong><?php echo $exception->getMessage();?></strong>
    <pre>
    <?php var_dump($exception); ?>
</pre>

    <?php if (isset($exception->xdebug_message)) { echo $exception->xdebug_message; } ?>
<?php } ?>