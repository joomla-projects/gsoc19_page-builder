<template>
	<div>
        <div id="sidebar" class="sidebar">
            <h2>{{ translate('COM_TEMPLATES_SETTINGS') }}</h2>
			<hr>
            <button type="button" class="btn btn-lg closebtn" @click="closeNav()">
                <span class="icon-cancel"></span>
            </button>
            <!-- TODO Add to store -->
            <!-- <component :is="selectedSettings" class="form-group" :grid='grid_selected' :column='column_selected' @reset="reset"></component> -->
        </div>

		<div class="pagebuilder" id="pagebuilder">
			<h2>{{ translate('COM_TEMPLATES_VIEW') }}</h2>
			<!-- Element -->
			<draggable v-model="elementArray" ghost-class="drop">
				<div v-for="element in elementArray" :class="['row-wrapper', element.type]">
                    <span>{{ element.type }}</span>
                    <span v-if="element.options.class != ''">.{{element.options.class}}</span>
                        <div class="btn-wrapper">
                            <button type="button" class="btn btn-lg" @click="editElement(element)">
                                <span class="icon-options"></span>
                                <span class="sr-only">{{ translate('COM_TEMPLATES_EDIT') }}</span>
                            </button>
                            <button type="button" class="btn btn-lg" @click="deleteElement(element)">
                                <span class="icon-cancel"></span>
                                <span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_GRID') }}</span>
                            </button>
                        </div>

                        <!-- Container & Module Position -->
                        <div v-if="element.type != 'Grid'">
                            <draggable v-model="element.children">
                                <div v-for="child in element.children" :class="['col-wrapper', child.type]">
                                    <span>{{ child.type }}</span>
                                    <span v-if="child.options.class != ''">.{{ child.options.class }}</span>
                                    
                                    <div v-if="child.type == 'Grid'">
                                        <grid :element="child"></grid>

                                        <button class="btn btn-add btn-outline-info" type="button" @click="addColumn(child)">
                                        <span class="icon-new"></span>
                                        {{ translate('COM_TEMPLATES_ADD_COLUMN') }}
                                        </button>
                                    </div>

                                    <button v-if="childAllowed.includes(child.type)" class="btn btn-add btn-outline-info" type="button" @click="addElement(child)">
                                        <span class="icon-new"></span>
                                        {{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
                                    </button>
                                </div>
                            </draggable>
                        </div>
                        <!-- Container & Module Position Ends -->

                        <!-- Grid -->
                        <div v-if="element.type == 'Grid'">
                            <grid :element="element"></grid> 

                            <button class="btn btn-add btn-outline-info" type="button" @click="addColumn(element)">
                                <span class="icon-new"></span>
                                {{ translate('COM_TEMPLATES_ADD_COLUMN') }}
                            </button>
                        </div>
                        <!-- Grid Ends-->

                    <button type="button" v-if="childAllowed.includes(element.type)" class="btn btn-add btn-outline-info btn-block" @click="addElement(element)">
                        <span class="icon-new"></span>
                        {{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
                    </button>
				</div>
			</draggable>
			<!-- Element Ends -->

			<button type="button" class="btn btn-add btn-outline-info btn-block" @click="addElement(elementArray)">
				<span class="icon-new"></span>
				{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
			</button>

			<!-- Modals -->
			<add-element-modal id="add-element"></add-element-modal>

		</div>
	</div>
</template>

<script>
    import draggable from 'vuedraggable';
    import { mapMutations, mapState } from 'vuex';

    export default {
        computed: {
            ...mapState([
                'elementArray',
                'childAllowed',
                'parent',
                'allowedChildren'
            ]),
        },
        watch: {
        elementArray: {
            handler: function (newVal) {
                document.getElementById('jform_params_grid').value = JSON.stringify(newVal);
            },
            deep: true,
        },
        },
        components: {
            draggable
        },
        created() {
            this.mapGrid((JSON.parse(document.getElementById('jform_params_grid').value)));
            this.ifChildAllowed();
        },
        methods: {
            ...mapMutations([
                'ifChildAllowed',
                'addGrid',
                'deleteElement',
                'addColumn',
                'editColumn',
                'editElement',
                'addContainer',
                'fillAllowedChildren',
                'mapGrid',
                'closeNav'
            ]),
            addElement(parent) {
                this.fillAllowedChildren(parent.type);
                this.$store.commit('addElement', parent);
                this.$modal.show('add-element');
            },
        }
    }
</script>
