<template>
    <div>
        <fieldset>
            <legend>{{ translate('COM_TEMPLATES_ADD_COLUMN') }}</legend>
            <label for="column_size">{{ translate('COM_TEMPLATES_ADD_COLUMN') }}</label>
            <select id="column_size" name="column_size" v-model="column_size">
                <option value="1">col-1</option>
                <option value="2">col-2</option>
                <option value="3">col-3</option>
                <option value="4">col-4</option>
                <option value="5">col-5</option>
                <option value="6">col-6</option>
                <option value="7">col-7</option>
                <option value="8">col-8</option>
                <option value="9">col-9</option>
                <option value="10">col-10</option>
                <option value="11">col-11</option>
                <option value="12">col-12</option>
            </select>
            <hr>

            <div class="btn-group">
                <button type="button" class="btn btn-success" @click="addColumn">{{ translate('COM_TEMPLATES_SAVE') }}</button>
                <button type="button" class="btn btn-danger" @click="$emit('reset')">{{ translate('JTOOLBAR_BACK') }}</button>
            </div>
        </fieldset>
    </div>
</template>

<script>
import {notifications} from "./../app/Notifications";

export default {
    name: 'add-column',
    props: {
        grid: {}
    },
    data() {
        return {
            column_size: '1',
        }
    },
    methods: {
        addColumn() {
            if (this.column_size !== '') {
                this.grid.children.push({
                    type: 'column',
                    options: {
                        size: 'col-sm-' + this.column_size
                    },
                    children: [{
                        type: 'position',
                        options: {},
                        children: []
                    }]
                });
            }
            this.column_size = '';
            this.$emit('reset');
        }
    },
}
</script>
