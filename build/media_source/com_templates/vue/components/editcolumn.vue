<template>
    <div>
        <fieldset>
            <legend>{{ translate('COM_TEMPLATES_EDIT_COLUMN') }}</legend>
            <div class="form-group">
                <label for="classes">{{ translate('COM_TEMPLATES_ADD_CLASS') }}</label>
				<input id="classes" name="classes" type="text" v-model="column.options.class">
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-success" @click="editColumn">{{ translate('COM_TEMPLATES_SAVE') }}</button>
                <button type="button" class="btn btn-danger" @click="$emit('reset')">{{ translate('JTOOLBAR_BACK') }}</button>
            </div>
        </fieldset>
    </div>
</template>

<script>
export default {
    name: 'edit-column',
    props: {
        column: {},
    },
    data() {
        return {
            column_class: ''
        };
    },
    methods: {
        editColumn() {
            if (this.column_class !== '') {
                this.options.class = this.column_class;
            }
            this.column_class = '';
            this.$emit('reset');
        }
    }
}
</script>