<template>
    <div>
        <fieldset>
            <legend>{{ translate('COM_TEMPLATES_ADD_COLUMN') }}</legend>
            <label for="column_size">{{ translate('COM_TEMPLATES_ADD_COLUMN') }}</label>
            <input id="column_size" name="column_size" type="text" v-model="column_size">

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
            column_size: ''
        }
    },
    methods: {
        addColumn() {
            if (this.column_size !== '') {
                let sum = 0;
                this.grid.children.forEach(element => {
                    const parts = element.options.size.split('-');
                    sum += Number(parts[parts.length - 1]);
                });
                if (sum + Number(this.column_size) <= 12) {
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
                else {
                    notifications.error('COM_TEMPLATES_MAX_COLUMN_SIZE');
                }
            }
            this.column_size = '';
            this.$emit('reset');
        }
    },
}
</script>
