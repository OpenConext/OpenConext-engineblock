<?php
EngineBlock_ApplicationSingleton::getInstance()->flushLog('Showing feedback page with message: ' . $layout->title);
?>

    <div class="l-overflow">
      <table class="comp-table">
        <thead></thead>
        <tbody>
        <?php
        if (!empty($_SESSION['feedbackInfo']) && is_array($_SESSION['feedbackInfo'])) {
            foreach($_SESSION['feedbackInfo'] as $name => $value) {
                if (empty($value)) {
                    continue;
                }
            ?>
            <tr>
                <td class="strong"><?= EngineBlock_View::htmlSpecialCharsText($this->t($name))?>:</td>
                <td style="font-size: 0.8em;"><?= EngineBlock_View::htmlSpecialCharsText($value);?></td>
            </tr>
            <?php
            }
        }
        ?>
        </tbody>
      </table>
    </div>

    <p><?= $this->t('error_help_desc'); ?></p>

    <?php
    // @todo make the number of steps in history that the back button makes dynamic, it is not always 2
    ?>
    <a href="#" id="GoBack"  class="c-button" onclick="history.back(-2); return false;">
      <?= $this->t('go_back'); ?>
    </a>
