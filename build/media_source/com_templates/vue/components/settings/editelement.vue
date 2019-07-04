<template>
    <div>
        <!-- Settings for editing elements -->
        <fieldset>
            <legend>{{ translate('COM_TEMPLATES_EDIT') }}{{ elementSelected.type }}</legend>
            <div class="form-group">
                <label for="element_class">{{ translate('COM_TEMPLATES_ADD_CLASS') }}</label>
                <input id="element_class" name="element_class" type="text" v-model="element_class">
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-success" @click="modifyElement">{{ translate('COM_TEMPLATES_ADD') }}</button>
                <button type="button" class="btn btn-danger" @click="closeNav">{{ translate('JTOOLBAR_CLOSE') }}</button>
            </div>
        </fieldset>
    </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';

export default {
    name: 'edit-element',
    computed: {
        ...mapState([
            'elementSelected'
        ])
    },
    data() {
        return {
            element_class: this.elementSelected ? this.elementSelected.options.class : '',
        }
    },
    methods: {
        closeNav() {
            this.element_class = '';
            this.$store.commit('closeNav');
        },
        modifyElement() {
            if (this.element_class !== '') {
                this.$store.commit('modifyElement', this.element_class);
            }
        }
    },
}
</script>