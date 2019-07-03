<template>
	<div>
        <div id="sidebar" class="sidebar">
            <h2>{{ translate('COM_TEMPLATES_SETTINGS') }}</h2>
			<hr>
            <button type="button" class="btn btn-lg closebtn" @click="closeNav()">
                <span class="icon-cancel"></span>
            </button>
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
                                        <grid :element="child" :childAllowed="childAllowed" @editColumn="editColumn" @deleteColumn="deleteColumn" @addElement="addElement"></grid>   

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
                            <grid :element="element" :childAllowed="childAllowed" @editColumn="editColumn" @deleteColumn="deleteColumn" @addElement="addElement"></grid>   

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
			<add-element-modal id="add-element" :allowedChildren="allowedChildren" @selection="insertElem"></add-element-modal>

		</div>
	</div>
</template>

<script>
  import draggable from 'vuedraggable';
  import {notifications} from "./../app/Notifications";
  import { mapGetters, mapMutations } from 'vuex';

  export default {
    computed: {
        elementArray() {
            return this.$store.state.elementArray;
        },
        childAllowed() {
            return this.$store.state.childAllowed;
        },
        allowedChildren() {
            return this.$store.state.allowedChildren;
        }
    },
    data() {
        return {
            // elementArray: this.grid,
        };
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
            'mapGrid'
        ]),
        deleteColumn(grid, column) {
            const index = grid.children.indexOf(column);
            if (index > -1) {
            grid.children.splice(index, 1);
            }
        },
        editPosition(grid, column) {
            this.gridSelected = grid;
            this.columnSelected = column;
            this.selectedSettings = 'edit-position';
        },
        show(name) {
            this.$modal.show(name);
        },
        hide(name) {
            this.$modal.hide(name);
        },
        addElement(parent) {
            this.fillAllowedChildren(parent.type);
            this.parent = parent;
            this.show('add-element');
        },
        insertElem(element,sizes) {
            if(element == 'Grid') {
                this.addGrid(sizes);
            }
            else if(element == 'Container') {
                this.addContainer();
            }
            else {
                if(this.parent.children) {
                    this.parent.children.push({
                    type: element,
                    options: {
                        class: ''
                    },
                    children: []
                    })
                }
                else {
                    this.parent.push({
                    type: element,
                    options: {
                        class: ''
                    },
                    children: []
                    })
            }
            }
            this.hide('add-element');
        },
        closeNav() {
            document.getElementById("sidebar").style.width = "0";
            document.getElementById("pagebuilder").style.marginLeft = "0";
        }
    }
  }
</script>
