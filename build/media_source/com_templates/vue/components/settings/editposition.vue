<template>
    <div>
        <!-- Settings for editing positions -->
        <fieldset>
            <legend>{{ translate('COM_TEMPLATES_EDIT_POSITION') }}</legend>
            <div class="form-group">
                <label for="module_chrome">{{ translate('COM_TEMPLATES_SELECT_MODULE_CHROME') }}</label>
                <select id="module_chrome" name="module_chrome" v-model="module_chrome">
                    <option value="none">{{ translate('COM_TEMPLATES_NONE') }}</option>
                    <option value="rounded">{{ translate('COM_TEMPLATES_ROUNDED') }}</option>
                    <option value="table">{{ translate('COM_TEMPLATES_TABLE') }}</option>
                    <option value="horz">{{ translate('COM_TEMPLATES_HORZ') }}</option>
                    <option value="xhtml">{{ translate('COM_TEMPLATES_XHTML') }}</option>
                    <option value="html5">{{ translate('COM_TEMPLATES_HTML5') }}</option>
                    <option value="outline">{{ translate('COM_TEMPLATES_OUTLINE') }}</option>
                </select>
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-success" @click="editPosition">{{ translate('COM_TEMPLATES_SAVE') }}</button>
                <button type="button" class="btn btn-danger" @click="closeNav">{{ translate('JTOOLBAR_CLOSE') }}</button>
            </div>
        </fieldset>
    </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';

export default {
    name: 'edit-position',
    computed: {
        ...mapState([
            'columnSelected'
        ])
    },
    data() {
        return {
            module_chrome: 'none'
        }
    },
    methods: {
        closeNav() {
            this.module_chrome = 'none';
            this.$store.commit('closeNav');
        },
        editPosition() {
            if (this.module_chrome !== 'none') {
                this.columnSelected.children[0].options.module_chrome = this.module_chrome;
            }
        }
    },
}
</script>