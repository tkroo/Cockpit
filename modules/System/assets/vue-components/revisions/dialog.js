export default {

    data() {

        return {
            revisions: [],
            loading: true,
            selectedRev: null
        }
    },

    props: {
        oid: {
            type: String,
            default: false
        },
        current: {
            type: Object,
            default: {}
        },
        caption: {
            type: String
        }
    },

    computed: {
        changes() {

            let changes = [];

            Object.keys(this.selectedRev.data).forEach(key => {

                if (this.current[key] == undefined) return;
                if (JSON.stringify(this.current[key]) == JSON.stringify(this.selectedRev.data[key])) return;

                changes.push({
                    key,
                    current: this.current[key],
                    revision: this.selectedRev.data[key],
                    diff: htmldiff(JSON.stringify(this.selectedRev.data[key], null, ' '), JSON.stringify(this.current[key], null, ' '))
                });
            });

            return changes;
        }
    },

    mounted() {
        this.load()
    },

    template: /*html*/`

        <div class="app-offcanvas-container">
            <div class="kiss-padding kiss-text-bold">
                {{ caption || t('Revisions') }}
            </div>
            <div class="app-offcanvas-content kiss-bgcolor-contrast kiss-flex-1 kiss-flex kiss-flex-column kiss-position-relative">

                <div class="kiss-height-50vh kiss-padding kiss-flex kiss-flex-middle" v-if="loading">
                    <app-loader size="small"></app-loader>
                </div>

                <kiss-row class="kiss-cover animated fadeIn" v-if="!loading && revisions.length">
                    <div class="kiss-flex-1 kiss-padding kiss-overflow-y-auto" style="max-height:100%;">

                        <div class="kiss-flex kiss-height-30vh kiss-flex-middle kiss-flex-center" v-if="!selectedRev">
                            <div class="kiss-color-muted kiss-size-2 kiss-width-1-2 kiss-align-center">{{ t('Select a version') }}</div>
                        </div>

                        <div class="kiss-flex kiss-height-30vh kiss-flex-middle kiss-flex-center" v-if="selectedRev && !changes.length">
                            <div class="kiss-color-muted kiss-size-2 kiss-width-1-2 kiss-align-center">{{ t('No changes') }}</div>
                        </div>

                        <div v-if="selectedRev">
                            <div class="kiss-margin" v-for="item in changes">

                                <div class="kiss-text-bold kiss-text-caption">{{ item.key }}</div>

                                <kiss-card class="kiss-margin-small-top kiss-padding kiss-flex kiss-flex-middle" theme="contrast">
                                    <pre class="kiss-text-monospace kiss-size-small kiss-overflow-y-auto kiss-margin-small-right kiss-flex-1" style="max-height:15vh" v-html="item.diff"></pre>
                                    <div><a @click="restoreField(item.key)"><icon class="kiss-size-4">settings_backup_restore</icon></a></div>
                                </kiss-card>
                            </div>

                        </div>

                    </div>
                    <div class="kiss-width-1-5 kiss-overflow-y-auto" style="max-height:100%;">

                        <ul class="app-list-items kiss-margin-top kiss-margin-bottom">
                            <li class="kiss-flex kiss-position-relative" v-for="rev in revisions">
                                <div class="kiss-flex-1">
                                    <div :class="(selectedRev == rev) ? 'kiss-color-primary kiss-text-bold':'kiss-size-small'">{{ (new Date(rev._created * 1000).toLocaleString()) }}</div>
                                    <div class="kiss-color-muted kiss-size-xsmall">By {{ rev._by && rev._by.user ? rev._by.user : 'n/a' }}</div>
                                </div>
                                <a class="kiss-cover" @click="selectedRev = rev"></a>
                            </li>
                        </ul>

                    </div>
                </kiss-row>

            </div>
            <div class="kiss-padding kiss-bgcolor-contrast">
                <kiss-row>
                    <div class="kiss-flex-1">
                        <button class="kiss-button kiss-button-primary kiss-width-1-1 kiss-margin-right" @click="restoreAll()" v-if="selectedRev && changes.length">{{ t('Restore all') }}</button>
                    </div>
                    <div class="kiss-width-1-5">
                        <button class="kiss-button kiss-width-1-1" kiss-offcanvas-close>{{ t('Close') }}</button>
                    </div>
                </kiss-row>
            </div>
        </div>
    `,

    methods: {

        load() {

            this.loading = true;

            this.$request(`/system/utils/revisions/${this.oid}?limit=20`).then(revisions => {

                App.assets.require(['system:assets/vendor/htmldiff.js']).then(() => {

                    this.revisions = revisions;
                    this.loading = false;
                });

            }).catch(rsp => {
                this.saving = false;
                App.ui.notify(rsp.error || 'Loading revisions failed!', 'error');
            });

        },

        restoreField(key) {
            this.current[key] = JSON.parse(JSON.stringify(this.selectedRev.data[key]));
        },

        restoreAll() {

            Object.keys(this.selectedRev.data).forEach(key => {

                if (this.current[key] !== undefined) {
                    this.current[key] = JSON.parse(JSON.stringify(this.selectedRev.data[key]));
                }
            });
        }

    }
}