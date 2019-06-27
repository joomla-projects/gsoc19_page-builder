<template>
        <draggable v-model="element.children" class="row">
            <div class="col-wrapper" v-for="column in element.children" :class="[column.options.size, checkIfGrid(column), column.type]">
                <div class="btn-wrapper">
                <button type="button" class="btn btn-lg" @click="$emit('editColumn', column)">
                    <span class="icon-options"></span>
                    <span class="sr-only">{{ translate('COM_TEMPLATES_EDIT_COLUMN') }}</span>
                </button>
                <button type="button" class="btn btn-lg" @click="$emit('deleteColumn', element, column)">
                    <span class="icon-cancel"></span>
                    <span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_COLUMN') }}</span>
                </button>
                </div>
                
                <span>{{column.options.size}} (<i>{{column.type}}<span v-if="column.options.class">, .{{column.options.class}}</span></i>)</span>
                <button type="button" class="btn btn-add btn-outline-info" @click="$emit('addElement', column)">
                    <span class="icon-new"></span>
                    {{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
                </button>
            </div>
        </draggable>
</template>

<script>
import draggable from 'vuedraggable';

export default {
    name: 'grid',
    props: {
        element: {}
    },
    components: {
        draggable
    },
    methods: {
        checkIfGrid(column) {
            if(column.type == 'Module Position')
                return 'col-12';
            else
                return '';
        }
    }
}
</script>