<!-- Language selection -->
<ul class="nav">
    <li class="<?php if ($lang==='en'): ?>active<?php endif; ?>">
        <a href="<?php echo EngineBlock_View::setLanguage('en'); ?>">EN</a>
    </li>
    <li class="<?php if ($lang==='nl'): ?>active<?php endif; ?>">
        <a href="<?php echo EngineBlock_View::setLanguage('nl'); ?>">NL</a>
    </li>
    <?php if (EngineBlock_View::moduleName() == 'profile'): ?>
        <li data-external-link="true">
            <a href="https://wiki.surfnet.nl/display/conextsupport/Profile+page" target="_blank">Help</a>
        </li>
    <?php endif; ?>
</ul>