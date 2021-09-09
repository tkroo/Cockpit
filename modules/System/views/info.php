
<kiss-container class="kiss-margin" size="medium">

    <ul class="kiss-breadcrumbs">
        <li><a href="<?=$this->route('/system')?>"><?=t('Settings')?></a></li>
    </ul>

    <div class="kiss-margin-large-bottom kiss-flex kiss-flex-middle">
        <div class="kiss-size-1 kiss-flex-1"><strong><?=t('System')?></strong></div>
    </div>

    <vue-view>
        <template>

            <app-tabs>

                <tab class="kiss-margin animated fadeIn" caption="<?=t('App')?>">

                    <div class="kiss-text-caption kiss-text-bold kiss-size-bold kiss-margin">
                        <?=('General')?>
                    </div>

                    <table class="kiss-table">
                        <tbody>
                            <tr>
                                <td width="50%" class="kiss-size-small">Version</td>
                                <td class="kiss-size-small kiss-color-muted"><?=$this->retrieve('app.version')?></td>
                            </tr>
                        </tbody>
                    </table>


                    <?php if ($this->helper('acl')->isSuperAdmin()): ?>
                    <div class="kiss-text-caption kiss-text-bold kiss-size-bold kiss-margin">
                        <?=('Environment Variables')?>
                    </div>
                    <table class="kiss-table">
                        <tbody>
                            <?php foreach(getenv() as $key => $value): ?>
                            <tr>
                                <td width="50%" class="kiss-size-small"><div class="kiss-size-xsmall"><?=$key?></div></td>
                                <td class="kiss-color-muted"><div class="kiss-size-xsmall"><?=$value?></div></td>
                            </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                    <?php endif ?>

                </tab>

                <tab class="kiss-margin animated fadeIn" caption="PHP">

                    <table class="kiss-table">
                        <tbody>
                            <tr><td width="50%">Version</td><td class="kiss-color-muted"><?=phpversion()?></td></tr>
                            <tr><td>PHP SAPI</td><td class="kiss-color-muted"><?=php_sapi_name()?></td></tr>
                            <tr><td>System</td><td class="kiss-color-muted"><?=php_uname()?></td></tr>
                            <tr><td>Extensions</td><td class="kiss-color-muted"><?=implode(', ', get_loaded_extensions())?></td></tr>
                            <tr><td>Max. execution time</td><td class="kiss-color-muted"><?=ini_get('max_execution_time')?> sec.</td></tr>
                            <tr><td>Memory limit</td><td class="kiss-color-muted"><?=ini_get("memory_limit")?></td></tr>
                            <tr><td>Upload file size limit</td><td class="kiss-color-muted"><?=ini_get("upload_max_filesize")?></td></tr>
                            <tr><td>Realpath Cache</td><td class="kiss-color-muted"><?=ini_get("realpath_cache_size")?> / <?=ini_get("realpath_cache_ttl")?> (ttl)</td></tr>
                            <tr>
                                <td>OPCache</td>
                                <td><span class="kiss-badge kiss-badge-outline kiss-color-<?=(ini_get("opcache.enable") ? 'success':'danger')?>"><?=(ini_get("opcache.enable") ? 'Enabled':'Disabled')?></span></td>
                            </tr>

                        </tbody>
                    </table>

                </tab>

            </app-tabs>

        </template>

        <script type="module">

            export default {

            }
        </script>
    </vue-view>
</kiss-container>