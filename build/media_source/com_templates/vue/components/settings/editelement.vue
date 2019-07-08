<template>
    <div>
        <!-- Settings for editing grids -->
        <fieldset>
            <legend>{{ translate('COM_TEMPLATES_EDIT') }}</legend>
            <div class="form-group">
                <label for="grid_class">{{ translate('COM_TEMPLATES_ADD_CLASS') }}</label>
                <input id="grid_class" name="grid_class" type="text" v-model="grid_class">
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-success" @click="editGrid">{{ translate('COM_TEMPLATES_SAVE') }}</button>
                <button type="button" class="btn btn-danger" @click="$emit('reset')">{{ translate('JTOOLBAR_BACK') }}</button>
            </div>
        </fieldset>
    </div>
</template>

<script>
export default {
    name: 'edit-grid',
    props: {
        grid: {}
    },
    data() {
        return {
            grid_class: this.grid.options.class,
        }
    },
    methods: {
        editGrid() {
            if (this.grid_class !== '') {
                this.grid.options.class = this.grid_class;
            }
            this.grid_class = '';
            this.$emit('reset');
        }
    },
}
</script>