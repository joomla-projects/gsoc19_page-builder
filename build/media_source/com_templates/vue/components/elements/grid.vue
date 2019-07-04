<template>
        <draggable v-model="element.children" class="row">
            <div class="col-wrapper" v-for="column in element.children" :class="[column.options.size, (column.type == 'Module_Position') ? 'col-12' : '', column.type]">
                <div class="btn-wrapper">
                <button type="button" class="btn btn-lg" @click="editElement(column)">
                    <span class="icon-options"></span>
                    <span class="sr-only">{{ translate('COM_TEMPLATES_EDIT_COLUMN') }}</span>
                </button>
                <button type="button" class="btn btn-lg" @click="deleteColumn({element,column})">
                    <span class="icon-cancel"></span>
                    <span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_COLUMN') }}</span>
                </button>
                </div>
                
                <span>{{column.options.size}} <i>{{column.type}}<span v-if="column.options.class">, .{{column.options.class}}</span></i></span>
                <button v-if="childAllowed.includes(column.type)" type="button" class="btn btn-add btn-outline-info" @click="addElement(column)">
                    <span class="icon-new"></span>
                    {{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
                </button>
            </div>
        </draggable>
</template>

<script>
    import draggable from 'vuedraggable';
    import { mapState, mapMutations } from 'vuex';

    export default {
        name: 'grid',
        props: {
            element: {},
        },
        components: {
            draggable
        },
        computed: {
            ...mapState([
                'childAllowed'
            ])
        },
        methods: {
            ...mapMutations([
                'deleteColumn',
                'fillAllowedChildren',
                'editElement'
            ]),
            addElements(column) {
                this.fillAllowedChildren(column.type);
                this.$store.commit('addElements', column);
                this.$modal.show('add-element');
            }
        }
    }
</script>